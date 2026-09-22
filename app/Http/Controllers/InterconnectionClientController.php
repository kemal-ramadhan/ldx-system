<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DevicePort;
use App\Models\InterconnectionRequest;
use App\Models\RackDivice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InterconnectionClientController extends Controller
{
    /**
     * =========================================================
     * INDEX
     * List interconnection milik client yang sedang login
     * =========================================================
     */
    public function index(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | GET AUTHENTICATED CLIENT
    |--------------------------------------------------------------------------
    */

        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        $clientId = (int) $clientPic->client_id;


        /*
    |--------------------------------------------------------------------------
    | QUERY INTERCONNECTION
    |--------------------------------------------------------------------------
    */

        $query = InterconnectionRequest::query()
            ->with([
                'requesterClient',

                /*
            |--------------------------------------------------------------------------
            | SOURCE
            |--------------------------------------------------------------------------
            */

                'sourcePort.device.client',
                'sourcePort.device.rack',

                /*
            |--------------------------------------------------------------------------
            | DESTINATION
            |--------------------------------------------------------------------------
            */

                'destinationPort.device.client',
                'destinationPort.device.rack',
            ])
            ->where('requester_client_id', $clientId)
            ->latest();


        /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where(
                    'request_number',
                    'like',
                    "%{$search}%"
                )

                    ->orWhere(
                        'description',
                        'like',
                        "%{$search}%"
                    );
            });
        }


        /*
    |--------------------------------------------------------------------------
    | FILTER STATUS
    |--------------------------------------------------------------------------
    */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );
        }


        /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */

        $interconnections = $query
            ->paginate(10)
            ->withQueryString();


        /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

        return Inertia::render(
            'Client/Interconnections/Index',
            [
                'interconnections' => $interconnections,

                'filters' => [
                    'search' => $request->search,
                    'status' => $request->status,
                ],
            ]
        );
    }


    /**
     * =========================================================
     * CREATE
     * Form membuat interconnection request
     * =========================================================
     */
    public function create()
    {
        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        $clientId = (int) $clientPic->client_id;

        /*
        |--------------------------------------------------------------------------
        | Source Device
        |--------------------------------------------------------------------------
        | Device yang dimiliki oleh client yang sedang login.
        |--------------------------------------------------------------------------
        */
        $devices = RackDivice::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->with([
                'rack',
                'ports' => function ($query) {
                    $query
                        ->where('status', 'available')
                        ->orderBy('port_number');
                },
            ])
            ->orderBy('divice_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Destination Client
        |--------------------------------------------------------------------------
        | Hanya client lain yang aktif.
        |--------------------------------------------------------------------------
        */
        $clients = Client::query()
            ->where('id', '!=', $clientId)
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get([
                'id',
                'company_code',
                'company_name',
            ]);

        return Inertia::render('Client/Interconnections/Create', [
            'devices' => $devices,
            'clients' => $clients,
        ]);
    }

    /**
     * =========================================================
     * STORE
     * Client membuat interconnection request
     * =========================================================
     */
    public function store(Request $request)
    {
        /*
    |--------------------------------------------------------------------------
    | CLIENT AUTHENTICATION
    |--------------------------------------------------------------------------
    | User -> ClientPic -> Client
    |--------------------------------------------------------------------------
    */
        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        $clientId = (int) $clientPic->client_id;


        /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'source_port_id' => [
                'required',
                'integer',
                'exists:device_ports,id',
            ],

            'destination_type' => [
                'required',
                'in:internal,external',
            ],

            'destination_port_id' => [
                'exclude_if:destination_type,external',
                'required_if:destination_type,internal',
                'integer',
                'exists:device_ports,id',
                'different:source_port_id',
            ],

            'external_client_name' => [
                'exclude_if:destination_type,internal',
                'required_if:destination_type,external',
                'string',
                'max:255',
            ],

            'external_rack_name' => [
                'exclude_if:destination_type,internal',
                'required_if:destination_type,external',
                'string',
                'max:255',
            ],

            'external_device_name' => [
                'exclude_if:destination_type,internal',
                'required_if:destination_type,external',
                'string',
                'max:255',
            ],

            'external_port_name' => [
                'exclude_if:destination_type,internal',
                'required_if:destination_type,external',
                'string',
                'max:255',
            ],

            'interconnection_type' => [
                'required',
                'string',
                'max:50',
            ],

            'cable_type' => [
                'required',
                'string',
                'max:50',
            ],

            'connector_type' => [
                'required',
                'string',
                'max:50',
            ],

            'cable_length' => [
                'required',
                'numeric',
                'min:0.1',
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


        /*
    |--------------------------------------------------------------------------
    | SOURCE PORT
    |--------------------------------------------------------------------------
    */

        $sourcePort = DevicePort::query()
            ->with('device')
            ->whereKey($validated['source_port_id'])
            ->firstOrFail();


        /*
    |--------------------------------------------------------------------------
    | SOURCE DEVICE HARUS MILIK CLIENT LOGIN
    |--------------------------------------------------------------------------
    */

        abort_unless(
            $sourcePort->device &&
                (int) $sourcePort->device->client_id === $clientId,
            403,
            'Source port bukan milik client Anda.'
        );


        /*
    |--------------------------------------------------------------------------
    | SOURCE PORT HARUS AVAILABLE
    |--------------------------------------------------------------------------
    */

        abort_unless(
            $sourcePort->status === 'available',
            422,
            'Source port tidak tersedia.'
        );


        /*
    |--------------------------------------------------------------------------
    | DESTINATION PORT
    |--------------------------------------------------------------------------
    */

        $isExternal = $validated['destination_type'] === 'external';
        $destinationPort = null;

        if (!$isExternal) {
            $destinationPort = DevicePort::query()
                ->with('device.client')
                ->whereKey($validated['destination_port_id'])
                ->firstOrFail();


            /*
        |--------------------------------------------------------------------------
        | DESTINATION DEVICE HARUS ADA
        |--------------------------------------------------------------------------
        */

            abort_unless(
                $destinationPort->device,
                422,
                'Destination device tidak ditemukan.'
            );


            /*
        |--------------------------------------------------------------------------
        | DESTINATION DEVICE HARUS AKTIF
        |--------------------------------------------------------------------------
        */

            abort_unless(
                $destinationPort->device->status === 'active',
                422,
                'Destination device tidak aktif.'
            );


            /*
        |--------------------------------------------------------------------------
        | DESTINATION CLIENT HARUS BERBEDA
        |--------------------------------------------------------------------------
        */

            abort_unless(
                (int) $destinationPort->device->client_id !== $clientId,
                422,
                'Destination harus berasal dari client lain.'
            );


            /*
        |--------------------------------------------------------------------------
        | DESTINATION PORT HARUS AVAILABLE
        |--------------------------------------------------------------------------
        */

            abort_unless(
                $destinationPort->status === 'available',
                422,
                'Destination port tidak tersedia.'
            );
        }


        /*
    |--------------------------------------------------------------------------
    | GENERATE REQUEST NUMBER + CREATE
    |--------------------------------------------------------------------------
    */

        $interconnection = DB::transaction(function () use (
            $validated,
            $clientId,
            $sourcePort,
            $destinationPort
        ) {

            /*
        |--------------------------------------------------------------------------
        | REQUEST NUMBER
        |--------------------------------------------------------------------------
        |
        | Format:
        | IC-YYYYMM-00001
        |
        |--------------------------------------------------------------------------
        */

            $year = now()->year;
            $month = now()->month;

            $lastRequest = InterconnectionRequest::query()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->latest('id')
                ->lockForUpdate()
                ->first();

            $sequence = $lastRequest
                ? ((int) substr($lastRequest->request_number, -5)) + 1
                : 1;

            $requestNumber =
                'IC-' .
                now()->format('Ym') .
                '-' .
                str_pad(
                    $sequence,
                    5,
                    '0',
                    STR_PAD_LEFT
                );


            /*
        |--------------------------------------------------------------------------
        | CREATE INTERCONNECTION REQUEST
        |--------------------------------------------------------------------------
        */

            return InterconnectionRequest::create([
                'request_number' => $requestNumber,

                /*
            |--------------------------------------------------------------------------
            | CLIENT DARI AUTHENTICATION
            |--------------------------------------------------------------------------
            */

                'requester_client_id' => $clientId,

                /*
            |--------------------------------------------------------------------------
            | PORT
            |--------------------------------------------------------------------------
            */

                'source_port_id' => $sourcePort->id,

                'destination_type' => $validated['destination_type'],

                'destination_port_id' => $validated['destination_port_id'] ?? null,

                'external_client_name' => $validated['external_client_name'] ?? null,

                'external_rack_name' => $validated['external_rack_name'] ?? null,

                'external_device_name' => $validated['external_device_name'] ?? null,

                'external_port_name' => $validated['external_port_name'] ?? null,

                /*
            |--------------------------------------------------------------------------
            | INTERCONNECTION
            |--------------------------------------------------------------------------
            */

                'interconnection_type' =>
                $validated['interconnection_type'],

                'cable_type' =>
                $validated['cable_type'],

                'connector_type' =>
                $validated['connector_type'],

                'cable_length' =>
                $validated['cable_length'],

                'cable_length_unit' =>
                $validated['cable_length_unit'],

                /*
            |--------------------------------------------------------------------------
            | WORKFLOW
            |--------------------------------------------------------------------------
            */

                'status' =>
                'waiting_destination_approval',

                'progress' => 0,

                'requested_at' => now(),

                /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */

                'description' =>
                $validated['description'] ?? null,

                'notes' =>
                $validated['notes'] ?? null,
            ]);
        });


        /*
    |--------------------------------------------------------------------------
    | REDIRECT TO DETAIL
    |--------------------------------------------------------------------------
    */

        return redirect()
            ->route(
                'client.interconnections.show',
                $interconnection->id
            )
            ->with(
                'success',
                'Interconnection request berhasil dibuat.'
            );
    }


    /**
     * =========================================================
     * SHOW
     * Detail interconnection milik client
     * =========================================================
     */
    public function show(string $id)
    {
        /*
    |--------------------------------------------------------------------------
    | GET AUTHENTICATED CLIENT
    |--------------------------------------------------------------------------
    */

        $clientPic = Auth::user()->clientPic;

        abort_unless(
            $clientPic && $clientPic->client_id,
            403,
            'User belum terhubung dengan client.'
        );

        $clientId = (int) $clientPic->client_id;


        /*
    |--------------------------------------------------------------------------
    | GET REQUEST
    |--------------------------------------------------------------------------
    */

        $interconnection = InterconnectionRequest::query()
            ->with([
                'requesterClient',

                'sourcePort.device.client',
                'sourcePort.device.rack',

                'destinationPort.device.client',
                'destinationPort.device.rack',

                'destinationApprover',
                'dcApprover',

                'crossConnect',
            ])
            ->whereKey($id)
            ->where('requester_client_id', $clientId)
            ->firstOrFail();


        /*
    |--------------------------------------------------------------------------
    | RESPONSE
    |--------------------------------------------------------------------------
    */

        return Inertia::render(
            'Client/Interconnections/Show',
            [
                'interconnection' => $interconnection,
            ]
        );
    }


    /**
     * =========================================================
     * DESTINATION DEVICES
     * =========================================================
     */
    public function destinationDevices($clientId)
    {
        $devices = RackDivice::query()
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->with('rack')
            ->orderBy('divice_name')
            ->get([
                'id',
                'client_id',
                'rack_id',
                'code',
                'divice_name',
                'model',
                'status',
            ]);

        return response()->json($devices);
    }


    /**
     * =========================================================
     * DEVICE PORTS
     * =========================================================
     */
    public function devicePorts($deviceId)
    {
        $ports = DevicePort::query()
            ->where('rack_divice_id', $deviceId)
            ->where('status', 'available')
            ->orderBy('port_number')
            ->get([
                'id',
                'rack_divice_id',
                'port_name',
                'port_number',
                'port_type',
                'connector_type',
                'status',
            ]);

        return response()->json($ports);
    }
}
