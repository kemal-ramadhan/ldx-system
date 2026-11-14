<?php

namespace App\Http\Controllers;

use App\Models\DevicePort;
use App\Models\InterconnectionRequest;
use App\Models\RackDivice;
use App\Support\ClientPortAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * Interconnection requests in the client portal.
 *
 * Same concept as the admin side (internal / external MMR S3-K1, cable data),
 * limited to the logged-in client's data:
 *  - a client can only request between ITS OWN ports, or to an external MMR
 *    (connections to other clients are created by the LDX team / admin);
 *  - a client sees requests it submitted and requests targeting its ports;
 *  - other clients' data is limited to company name + rack/device/port names.
 */
class InterconnectionClientController extends Controller
{
    public const EXTERNAL_LOCATIONS = ['S3', 'K1'];

    private function clientId(): int
    {
        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        return (int) $clientPic->client_id;
    }

    private function myRole(InterconnectionRequest $request, int $clientId): string
    {
        return (int) $request->requester_client_id === $clientId ? 'requester' : 'destination';
    }

    /**
     * =========================================================
     * INDEX
     * =========================================================
     */
    public function index(Request $request)
    {
        $clientId = $this->clientId();

        $query = InterconnectionRequest::query()
            ->with([
                'requesterClient:id,company_code,company_name',
                ...ClientPortAccess::portRelations(),
            ])
            ->latest();

        ClientPortAccess::touchingClient($query, $clientId, includeRequester: true);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $interconnections = $query->paginate(10)->withQueryString();

        $interconnections->getCollection()->transform(function ($item) use ($clientId) {
            $item->setAttribute('my_role', $this->myRole($item, $clientId));

            return $item;
        });

        return Inertia::render('Client/Interconnections/Index', [
            'interconnections' => $interconnections,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    /**
     * =========================================================
     * CREATE
     * Only the client's own active devices (source & internal destination).
     * =========================================================
     */
    public function create()
    {
        $clientId = $this->clientId();

        $devices = RackDivice::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->with([
                'rack:id,code,name',
                'ports' => fn ($q) => $q
                    ->select(['id', 'rack_divice_id', 'port_name', 'port_number', 'port_type', 'status'])
                    ->where('status', 'available')
                    ->orderBy('port_number'),
            ])
            ->orderBy('divice_name')
            ->get(['id', 'client_id', 'rack_id', 'code', 'divice_name']);

        return Inertia::render('Client/Interconnections/Create', [
            'devices' => $devices,
            'externalLocations' => self::EXTERNAL_LOCATIONS,
        ]);
    }

    /**
     * =========================================================
     * STORE
     * Rules mirror the admin InterconnectionRequestController::store,
     * requester = logged-in client.
     * =========================================================
     */
    public function store(Request $request)
    {
        $clientId = $this->clientId();

        $validated = $request->validate([
            'source_port_id' => ['required', 'integer', 'exists:device_ports,id'],
            'destination_type' => ['required', 'in:internal,external'],
            'destination_port_id' => [
                'exclude_if:destination_type,external',
                'required_if:destination_type,internal',
                'integer',
                'exists:device_ports,id',
                'different:source_port_id',
            ],
            'external_location' => [
                'exclude_if:destination_type,internal',
                'required_if:destination_type,external',
                'in:' . implode(',', self::EXTERNAL_LOCATIONS),
            ],
            'external_client_name' => ['exclude_if:destination_type,internal', 'required_if:destination_type,external', 'string', 'max:255'],
            'external_rack_name' => ['exclude_if:destination_type,internal', 'required_if:destination_type,external', 'string', 'max:255'],
            'external_device_name' => ['exclude_if:destination_type,internal', 'required_if:destination_type,external', 'string', 'max:255'],
            'external_port_name' => ['exclude_if:destination_type,internal', 'required_if:destination_type,external', 'string', 'max:255'],
            'interconnection_type' => ['required', 'in:internal_building,cross_connect,other'],
            'cable_type' => ['required', 'in:fiber_optic,copper,coaxial,other'],
            'connector_type' => ['nullable', 'string', 'max:100'],
            'cable_length' => ['nullable', 'numeric', 'min:0'],
            'cable_length_unit' => ['required', 'in:meter,cm,km'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ], [
            'destination_port_id.required_if' => 'Port tujuan wajib dipilih.',
            'destination_port_id.different' => 'Port tujuan harus berbeda dengan port sumber.',
            'external_location.required_if' => 'Lokasi MMR (S3 / K1) wajib dipilih.',
            'external_location.in' => 'Lokasi MMR harus S3 atau K1.',
            'interconnection_type.in' => 'Tipe interconnection tidak valid.',
            'cable_type.in' => 'Tipe kabel tidak valid.',
        ]);

        $isExternal = $validated['destination_type'] === 'external';

        /*
        |--------------------------------------------------------------------------
        | OWNERSHIP: both ends must be the client's own ports
        |--------------------------------------------------------------------------
        */
        $sourcePort = DevicePort::with('device')->findOrFail($validated['source_port_id']);

        if ((int) $sourcePort->device?->client_id !== $clientId) {
            throw ValidationException::withMessages([
                'source_port_id' => 'Port sumber bukan milik perusahaan Anda.',
            ]);
        }

        if ($sourcePort->status !== 'available') {
            throw ValidationException::withMessages([
                'source_port_id' => 'Port sumber sedang tidak tersedia.',
            ]);
        }

        if (!$isExternal) {
            $destinationPort = DevicePort::with('device')->findOrFail($validated['destination_port_id']);

            if ((int) $destinationPort->device?->client_id !== $clientId) {
                throw ValidationException::withMessages([
                    'destination_port_id' => 'Port tujuan harus milik perusahaan Anda. Interconnection ke client lain diajukan melalui tim LDX.',
                ]);
            }

            if ($destinationPort->device->status !== 'active') {
                throw ValidationException::withMessages([
                    'destination_port_id' => 'Device tujuan tidak aktif.',
                ]);
            }

            if ($destinationPort->status !== 'available') {
                throw ValidationException::withMessages([
                    'destination_port_id' => 'Port tujuan sedang tidak tersedia.',
                ]);
            }
        }

        $interconnection = DB::transaction(function () use ($validated, $clientId, $sourcePort, $isExternal) {
            // IC-YYYYMM-00001
            $lastRequest = InterconnectionRequest::query()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $sequence = $lastRequest ? ((int) substr($lastRequest->request_number, -5)) + 1 : 1;

            return InterconnectionRequest::create([
                'request_number' => 'IC-' . now()->format('Ym') . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT),
                'requester_client_id' => $clientId,

                'source_port_id' => $sourcePort->id,
                'destination_type' => $validated['destination_type'],
                'destination_port_id' => $isExternal ? null : $validated['destination_port_id'],

                'external_location' => $isExternal ? $validated['external_location'] : null,
                'external_client_name' => $validated['external_client_name'] ?? null,
                'external_rack_name' => $validated['external_rack_name'] ?? null,
                'external_device_name' => $validated['external_device_name'] ?? null,
                'external_port_name' => $validated['external_port_name'] ?? null,

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
        });

        return redirect()
            ->route('client.interconnections.show', $interconnection->id)
            ->with('success', 'Interconnection request berhasil dibuat.');
    }

    /**
     * =========================================================
     * SHOW
     * Requester or owner of one of the ports; 404 otherwise.
     * =========================================================
     */
    public function show(string $id)
    {
        $clientId = $this->clientId();

        $query = InterconnectionRequest::query()
            ->with([
                'requesterClient:id,company_code,company_name',
                ...ClientPortAccess::portRelations(withLocation: true),
                'destinationApprover:id,name',
                'dcApprover:id,name',
                'crossConnect:id,interconnection_request_id,cross_connect_number,status,installed_at,terminated_at',
            ])
            ->whereKey($id);

        $interconnection = ClientPortAccess::touchingClient($query, $clientId, includeRequester: true)
            ->firstOrFail();

        return Inertia::render('Client/Interconnections/Show', [
            'interconnection' => $interconnection,
            'myRole' => $this->myRole($interconnection, $clientId),
        ]);
    }
}
