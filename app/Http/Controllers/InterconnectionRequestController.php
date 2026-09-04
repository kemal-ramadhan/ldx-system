<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DevicePort;
use App\Models\InterconnectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\CrossConnect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InterconnectionRequestController extends Controller
{
    /**
     * LIST
     */
    public function index()
    {
        $interconnections = InterconnectionRequest::with([
            'requesterClient',
            'sourcePort.device.client',
            'sourcePort.device.rack',
            'destinationPort.device.client',
            'destinationPort.device.rack',
        ])
            ->latest()
            ->paginate(15);

        return Inertia::render('interconnections/Index', [
            'title' => 'Interconnections',
            'interconnections' => $interconnections,
        ]);
    }

    /**
     * CREATE
     */
    public function create()
    {
        $clients = Client::with([
            'clientRacks.rack.rackDivices.ports',
        ])
            ->select([
                'id',
                'company_code',
                'company_name',
                'company_email',
            ])
            ->get();

        return Inertia::render('interconnections/Create', [
            'title' => 'Create Interconnection',
            'clients' => $clients,
        ]);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'requester_client_id' => [
                'required',
                'exists:clients,id',
            ],

            'source_port_id' => [
                'required',
                'exists:device_ports,id',
            ],

            'destination_port_id' => [
                'required',
                'exists:device_ports,id',
                'different:source_port_id',
            ],

            'interconnection_type' => [
                'required',
                'in:internal_building,cross_connect,other',
            ],

            'cable_type' => [
                'required',
                'in:fiber_optic,copper,coaxial,other',
            ],

            'connector_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'cable_length' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cable_length_unit' => [
                'required',
                'string',
                'max:20',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ]);

        DB::beginTransaction();

        try {
            /*
             * Pastikan source dan destination port valid
             */
            $sourcePort = DevicePort::with([
                'device.client',
                'device.rack',
            ])->findOrFail($validated['source_port_id']);

            $destinationPort = DevicePort::with([
                'device.client',
                'device.rack',
            ])->findOrFail($validated['destination_port_id']);

            /*
             * Port tidak boleh sama
             */
            if ($sourcePort->id === $destinationPort->id) {
                return back()->withErrors([
                    'message' => 'Source port and destination port cannot be the same.',
                ]);
            }

            /*
             * Port harus available
             */
            if ($sourcePort->status !== 'available') {
                return back()->withErrors([
                    'message' => 'Source port is not available.',
                ]);
            }

            if ($destinationPort->status !== 'available') {
                return back()->withErrors([
                    'message' => 'Destination port is not available.',
                ]);
            }

            /*
             * Requester harus merupakan client
             * yang memiliki source atau destination
             *
             * Untuk sekarang kita validasi bahwa requester
             * adalah salah satu pemilik endpoint.
             */
            $requesterId = (int) $validated['requester_client_id'];

            $sourceClientId = (int) $sourcePort->device->client_id;
            $destinationClientId = (int) $destinationPort->device->client_id;

            if (
                $requesterId !== $sourceClientId &&
                $requesterId !== $destinationClientId
            ) {
                return back()->withErrors([
                    'message' => 'Requester client must own the source or destination device.',
                ]);
            }

            /*
             * Generate request number
             */
            $requestNumber = $this->generateRequestNumber();

            $interconnection = InterconnectionRequest::create([
                'request_number' => $requestNumber,

                'requester_client_id' => $validated['requester_client_id'],

                'source_port_id' => $validated['source_port_id'],

                'destination_port_id' => $validated['destination_port_id'],

                'interconnection_type' => $validated['interconnection_type'],

                'cable_type' => $validated['cable_type'],

                'connector_type' => $validated['connector_type'] ?? null,

                'cable_length' => $validated['cable_length'] ?? null,

                'cable_length_unit' => $validated['cable_length_unit'],

                'status' => 'waiting_destination_approval',

                'progress' => 0,

                'requested_at' => now(),

                'description' => $validated['description'] ?? null,

                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.interconnections.show', $interconnection->id)
                ->with('success', 'Interconnection request created successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'message' => $th->getMessage(),
            ]);
        }
    }

    /**
     * SHOW
     */
    public function show(InterconnectionRequest $interconnection)
    {
        $interconnection->load([
            'requesterClient',

            'sourcePort.device.client',
            'sourcePort.device.rack.room.locationDataCenter',

            'destinationPort.device.client',
            'destinationPort.device.rack.room.locationDataCenter',

            'destinationApprover',
            'dcApprover',

            'crossConnect',
        ]);

        return Inertia::render('interconnections/Show', [
            'title' => 'Interconnection Request',
            'interconnection' => $interconnection,
        ]);
    }

    /**
     * Generate request number
     *
     * Example:
     * IC-202609-00001
     */
    private function generateRequestNumber(): string
    {
        $prefix = 'IC-' . now()->format('Ym') . '-';

        $lastRequest = InterconnectionRequest::query()
            ->where('request_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if (!$lastRequest) {
            $sequence = 1;
        } else {
            $lastNumber = (int) str_replace(
                $prefix,
                '',
                $lastRequest->request_number
            );

            $sequence = $lastNumber + 1;
        }

        return $prefix . str_pad(
            $sequence,
            5,
            '0',
            STR_PAD_LEFT
        );
    }

    public function approveDestination(string $id)
    {
        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'waiting_destination_approval') {
            throw ValidationException::withMessages([
                'status' => 'This request is not waiting for destination approval.',
            ]);
        }

        $interconnection->update([
            'status' => 'waiting_dc_approval',
            'progress' => 20,
            'destination_approved_by' => Auth::id(),
            'destination_approved_at' => now(),
        ]);

        return back()->with(
            'success',
            'Destination approval completed successfully.'
        );
    }

    public function rejectDestination(
        Request $request,
        string $id
    ) {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'waiting_destination_approval') {
            throw ValidationException::withMessages([
                'status' => 'This request is not waiting for destination approval.',
            ]);
        }

        $interconnection->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with(
            'success',
            'Interconnection request rejected.'
        );
    }

    public function approveDc(string $id)
    {
        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'waiting_dc_approval') {
            throw ValidationException::withMessages([
                'status' => 'This request is not waiting for DC approval.',
            ]);
        }

        $interconnection->update([
            'status' => 'approved',
            'progress' => 40,
            'dc_approved_by' => Auth::id(),
            'dc_approved_at' => now(),
        ]);

        return back()->with(
            'success',
            'DC approval completed successfully.'
        );
    }

    public function rejectDc(
        Request $request,
        string $id
    ) {
        $validated = $request->validate([
            'rejection_reason' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'waiting_dc_approval') {
            throw ValidationException::withMessages([
                'status' => 'This request is not waiting for DC approval.',
            ]);
        }

        $interconnection->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with(
            'success',
            'Interconnection request rejected.'
        );
    }

    public function startInstallation(string $id)
    {
        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'This request is not approved for installation.',
            ]);
        }

        $interconnection->update([
            'status' => 'in_progress',
            'progress' => 60,
            'started_at' => now(),
        ]);

        return back()->with(
            'success',
            'Installation has been started.'
        );
    }

    public function startTesting(string $id)
    {
        $interconnection = InterconnectionRequest::findOrFail($id);

        if ($interconnection->status !== 'in_progress') {
            throw ValidationException::withMessages([
                'status' => 'Installation must be in progress before testing.',
            ]);
        }

        $interconnection->update([
            'status' => 'testing',
            'progress' => 80,
        ]);

        return back()->with(
            'success',
            'Interconnection testing has started.'
        );
    }

    public function complete(string $id)
    {
        $crossConnect = DB::transaction(function () use ($id) {

            /**
             * Lock interconnection request
             */
            $interconnection = InterconnectionRequest::query()
                ->lockForUpdate()
                ->findOrFail($id);

            /**
             * Request must be in testing state
             */
            if ($interconnection->status !== 'testing') {
                throw ValidationException::withMessages([
                    'status' =>
                    'Interconnection must be in testing status before completion.',
                ]);
            }

            /**
             * Lock source port
             */
            $sourcePort = DevicePort::query()
                ->lockForUpdate()
                ->findOrFail(
                    $interconnection->source_port_id
                );

            /**
             * Lock destination port
             */
            $destinationPort = DevicePort::query()
                ->lockForUpdate()
                ->findOrFail(
                    $interconnection->destination_port_id
                );

            /**
             * Both ports must still be available
             */
            if ($sourcePort->status !== 'available') {
                throw ValidationException::withMessages([
                    'source_port_id' =>
                    'Source port is no longer available.',
                ]);
            }

            if ($destinationPort->status !== 'available') {
                throw ValidationException::withMessages([
                    'destination_port_id' =>
                    'Destination port is no longer available.',
                ]);
            }

            /**
             * Prevent duplicate active Cross Connect
             */
            $busy = CrossConnect::query()
                ->where('status', 'active')
                ->where(function ($query) use (
                    $sourcePort,
                    $destinationPort
                ) {
                    $query
                        ->whereIn('source_port_id', [
                            $sourcePort->id,
                            $destinationPort->id,
                        ])
                        ->orWhereIn('destination_port_id', [
                            $sourcePort->id,
                            $destinationPort->id,
                        ]);
                })
                ->exists();

            if ($busy) {
                throw ValidationException::withMessages([
                    'status' =>
                    'One of the ports already has an active cross connect.',
                ]);
            }

            /**
             * Generate Cross Connect number
             *
             * Example:
             * IC-20260904-00001
             * becomes
             * CC-20260904-00001
             */
            $crossConnectNumber =
                'CC-' .
                substr(
                    $interconnection->request_number,
                    3
                );

            /**
             * Create Cross Connect
             */
            $crossConnect = CrossConnect::create([
                'cross_connect_number' => $crossConnectNumber,

                'interconnection_request_id' =>
                $interconnection->id,

                'source_port_id' =>
                $sourcePort->id,

                'destination_port_id' =>
                $destinationPort->id,

                'cable_type' =>
                $interconnection->cable_type,

                'connector_type' =>
                $interconnection->connector_type,

                'cable_length' =>
                $interconnection->cable_length,

                'cable_length_unit' =>
                $interconnection->cable_length_unit,

                'status' => 'active',

                'installed_by' =>
                Auth::id(),

                'installed_at' =>
                now(),

                'description' =>
                $interconnection->description,
            ]);

            /**
             * Mark both ports as connected
             */
            $sourcePort->update([
                'status' => 'connected',
            ]);

            $destinationPort->update([
                'status' => 'connected',
            ]);

            /**
             * Complete request
             */
            $interconnection->update([
                'status' => 'completed',
                'progress' => 100,
                'completed_at' => now(),
            ]);

            return $crossConnect;
        });

        return back()->with(
            'success',
            'Interconnection completed successfully. Cross Connect is now active.'
        );
    }

    public function cancel(
        Request $request,
        string $id
    ) {
        $validated = $request->validate([
            'reason' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $interconnection = InterconnectionRequest::findOrFail($id);

        $allowedStatuses = [
            'waiting_destination_approval',
            'waiting_dc_approval',
            'approved',
            'in_progress',
            'testing',
        ];

        if (!in_array(
            $interconnection->status,
            $allowedStatuses,
            true
        )) {
            throw ValidationException::withMessages([
                'status' =>
                'This request cannot be cancelled.',
            ]);
        }

        $interconnection->update([
            'status' => 'cancelled',
            'notes' => $validated['reason'] ?? null,
        ]);

        return back()->with(
            'success',
            'Interconnection request cancelled.'
        );
    }
}
