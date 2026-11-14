<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRack;
use App\Models\Rack;
use App\Models\RackDivice;
use App\Models\RackImage;
use App\Models\RackUnit;
use App\Models\DeviceMovement;
use App\Models\Visitor;
use App\Services\DeviceMovementService;
use App\Services\RackUnitAllocator;
use App\Support\DeviceMovementRules;
use App\Models\Room;
use App\Models\DevicePort;
use App\Models\RackTelemetryLog;
use App\Http\Controllers\RackMonitoringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $room = $request->room;

        $racks = Rack::with(['room.locationDataCenter', 'images:id,rack_id,file_path,file_name,caption'])
            ->withCount(['rackDivices', 'clientRacks'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })

            ->when($room, function ($query) use ($room) {
                $query->where('room_id', $room);
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('racks/Racks', [
            'title' => 'Rack Management',
            'rooms' => Room::select('id', 'name')->get(),

            'racks' => $racks,

            'filters' => [
                'search' => $search,
                'room' => $room
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('racks/RackCreate', [
            'title' => 'Create Rack',
            'rooms' => Room::select(
                'id',
                'name',
                'location_data_center_id'
            )->with('locationDataCenter')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'code' => ['required', 'string', 'max:255', 'unique:racks,code'],
            'name' => ['required', 'string', 'max:255'],
            'total_units' => ['required', 'integer', 'min:1', 'max:100'],
            'power_capacity' => ['required', 'integer', 'min:0'],
            'weight_capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,maintenance,full'],
            'api_suhu' => ['nullable', 'string', 'max:100'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        DB::beginTransaction();

        try {

            /**
             * Create rack
             */
            $rack = Rack::create([
                'room_id' => $validated['room_id'],
                'code' => $validated['code'],
                'name' => $validated['name'],
                'total_units' => $validated['total_units'],
                'power_capacity' => $validated['power_capacity'],
                'weight_capacity' => $validated['weight_capacity'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'api_suhu' => $validated['api_suhu'] ?? null,
            ]);

            /**
             * Generate rack units
             */
            $units = [];

            for ($i = 1; $i <= $rack->total_units; $i++) {

                $units[] = [
                    'rack_id' => $rack->id,
                    'client_id' => null,
                    'rack_divice_id' => null,
                    'code' => 'UNT-' . Str::upper(Str::random(6)),
                    'unit_number' => $i,
                    'status' => 'empty',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            RackUnit::insert($units);

            if ($request->hasFile('images')) {
                $this->storeRackImages($rack, $request->file('images'));
            }

            DB::commit();

            return redirect('admin/racks')
                ->with('success', 'Rack created successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'message' => $th->getMessage(),
                ]);
        }
    }

    public function storeDevicePort(
        Request $request,
        RackDivice $device
    ) {
        $validated = $request->validate([
            'port_name' => [
                'required',
                'string',
                'max:100',
            ],

            'port_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'port_type' => [
                'required',
                'in:ethernet,fiber,management,power,console,other',
            ],

            'connector_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                'in:available,connected,disabled,maintenance',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $exists = DevicePort::query()
            ->where('rack_divice_id', $device->id)
            ->where('port_name', $validated['port_name'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'message' => 'This port already exists on the device.',
            ]);
        }

        DevicePort::create([
            'rack_divice_id' => $device->id,
            'port_name' => $validated['port_name'],
            'port_number' => $validated['port_number'] ?? null,
            'port_type' => $validated['port_type'],
            'connector_type' => $validated['connector_type'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with(
            'success',
            'Device port added successfully.'
        );
    }

    public function generateDevicePorts(
        Request $request,
        RackDivice $device
    ) {
        $validated = $request->validate([
            'total_ports' => [
                'required',
                'integer',
                'min:1',
                'max:1024',
            ],

            'port_type' => [
                'required',
                'in:ethernet,fiber,management,power,console,other',
            ],

            'connector_type' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        DB::beginTransaction();

        try {

            $existingPorts = DevicePort::query()
                ->where('rack_divice_id', $device->id)
                ->pluck('port_name')
                ->toArray();

            $ports = [];
            $createdCount = 0;
            $i = 1;

            while ($createdCount < $validated['total_ports']) {

                $portName = 'Port ' . $i;

                if (!in_array($portName, $existingPorts)) {
                    $ports[] = [
                        'rack_divice_id' => $device->id,
                        'port_name' => $portName,
                        'port_number' => (string) $i,
                        'port_type' => $validated['port_type'],
                        'connector_type' => $validated['connector_type'] ?? null,
                        'status' => 'available',
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    $existingPorts[] = $portName;
                    $createdCount++;
                }

                $i++;
            }

            if (!empty($ports)) {
                DevicePort::insert($ports);
            }

            DB::commit();

            return back()->with(
                'success',
                count($ports) . ' device ports generated successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function assignOwner(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'rented_units' => ['required', 'integer', 'min:1'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'rental_start_date' => ['required', 'date'],
            'rental_end_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,suspended,terminated'],
        ]);

        DB::beginTransaction();

        try {

            /**
             * =========================
             * CHECK AVAILABLE UNITS
             * =========================
             */

            $availableUnits = RackUnit::query()->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->count('id');

            if ($availableUnits < $validated['rented_units']) {

                throw ValidationException::withMessages([
                    'rented_units' => 'Not enough available units in this rack.'
                ]);
            }

            /**
             * =========================
             * CREATE CLIENT RACK
             * =========================
             */

            $clientRack = ClientRack::create([
                'client_id' => $validated['client_id'],
                'rack_id' => $rack->id,

                'rented_units' => $validated['rented_units'],

                'monthly_fee' => $validated['monthly_fee'],

                'rental_start_date' => $validated['rental_start_date'],
                'rental_end_date' => $validated['rental_end_date'],

                'status' => $validated['status'],
            ]);

            /**
             * =========================
             * ASSIGN EMPTY UNITS
             * =========================
             */

            $units = RackUnit::query()->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->orderBy('unit_number', 'asc')
                ->limit($validated['rented_units'])
                ->get();

            foreach ($units as $unit) {

                $unit->update([
                    'client_id' => $validated['client_id'],
                    'status' => 'used',
                ]);
            }

            /**
             * =========================
             * UPDATE RACK STATUS
             * =========================
             */

            $rack->refreshFullStatus();

            DB::commit();

            return back()->with('success', 'Rack owner assigned successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            throw $th;
        }
    }

    public function storeDevice(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],

            'divice_name' => ['required', 'string'],
            'divice_type' => ['nullable', 'string'],

            'model' => ['nullable', 'string'],
            'serial_number' => ['nullable', 'string'],

            'description' => ['nullable', 'string'],

            'power_usage' => ['nullable', 'numeric'],
            'weight_usage' => ['nullable', 'numeric'],

            'ip_address' => ['nullable', 'string'],

            'total_unit' => ['required', 'integer', 'min:1'],

            'status' => ['required', 'in:active,inactive,maintenance'],

            'installed_at' => ['nullable', 'date'],

            // keluar-masuk: who brought the device in + documents (optional)
            ...DeviceMovementRules::carrier(),
            ...DeviceMovementRules::attachments(),
        ]);

        DB::beginTransaction();

        try {

            /**
             * =========================
             * GET EMPTY UNITS
             * =========================
             */

            /*
             * =========================
             * CONTIGUOUS FREE UNITS OF THE CLIENT
             * =========================
             */
            $units = RackUnitAllocator::findContiguous(
                $rack->id,
                (int) $validated['client_id'],
                (int) $validated['total_unit']
            );

            $device = RackDivice::create([
                'rack_id' => $rack->id,
                'client_id' => $validated['client_id'],
                'code' => 'DVC-' . strtoupper(Str::random(6)),
                'divice_name' => $validated['divice_name'],
                'divice_type' => $validated['divice_type'] ?? null,
                'model' => $validated['model'] ?? null,
                'serial_number' => $validated['serial_number'] ?? null,
                'description' => $validated['description'] ?? null,
                'power_usage' => $validated['power_usage'] ?? null,
                'weight_usage' => $validated['weight_usage'] ?? null,
                'ip_address' => $validated['ip_address'] ?? null,
                'total_unit' => $validated['total_unit'],
                'status' => $validated['status'],
                'placement_status' => RackDivice::PLACEMENT_IN_RACK,
                // empty = installed now
                'installed_at' => $validated['installed_at'] ?? now(),
            ]);

            RackUnitAllocator::assign($device, $units);

            /*
             * =========================
             * KELUAR-MASUK: record "Masuk"
             * =========================
             */
            DeviceMovementService::recordCheckIn($device->fresh(), [
                ...$request->only(['carrier_name', 'carrier_company', 'carrier_id_number', 'carrier_phone', 'visitor_id']),
                'photos' => $request->file('photos', []),
                'bast' => $request->file('bast', []),
            ]);

            $rack->refreshFullStatus();

            DB::commit();

            return back()->with(
                'success',
                'Device added successfully.'
            );
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()->withErrors([
                'message' => collect($e->errors())->flatten()->first(),
            ]);
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'message' => $th->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $rack = Rack::with([
            'room.locationDataCenter',
            'clientRacks.client',
            'rackDivices.client',
            'rackDivices.ports',
            'rackDivices.rackUnits',
            'images.uploader:id,name',
        ])
            ->withCount('images')
            ->withSum('clientRacks', 'rented_units')
            ->withSum('rackDivices', 'power_usage')
            ->withSum('rackDivices', 'weight_usage')
            ->withCount('clientRacks')
            ->withCount('rackDivices')
            ->findOrFail($id);

        $clients = Client::select(
            'id',
            'company_code',
            'company_name',
            'company_email'
        )->get();

        $invoices = \App\Models\Invoice::with(['service', 'client'])
            ->whereHas('service', function ($q) use ($id) {
                $q->where('rack_id', $id);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $interconnections = \App\Models\InterconnectionRequest::with([
            'sourcePort.device', 
            'destinationPort.device',
            'requesterClient'
        ])
        ->whereHas('sourcePort.device', function ($q) use ($id) {
            $q->where('rack_id', $id);
        })
        ->orWhereHas('destinationPort.device', function ($q) use ($id) {
            $q->where('rack_id', $id);
        })
        ->latest()
        ->get();

        $monitoringData = null;
        $telemetryLogs = [];
        if (!empty($rack->api_suhu)) {
            $monitoringData = Cache::remember('rack_monitoring_' . $rack->api_suhu, 60, function () use ($rack) {
                return RackMonitoringController::fetchMonitoringData($rack->api_suhu);
            });

            // Ambil histori log tersimpan (maksimal 7 hari ke belakang, urut terlama ke terbaru untuk grafik)
            $telemetryLogs = RackTelemetryLog::where(function ($q) use ($rack) {
                $q->where('rack_id', $rack->id)
                  ->orWhere('api_rack_name', $rack->api_suhu);
            })
            ->where('recorded_at', '>=', now()->subDays(7))
            ->orderBy('recorded_at', 'asc')
            ->limit(100)
            ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | KELUAR-MASUK DEVICE
        |--------------------------------------------------------------------------
        */
        // devices that permanently left this rack (hidden from rackDivices by the global scope)
        $removedDevices = RackDivice::withRemoved()
            ->where('rack_id', $rack->id)
            ->where('placement_status', RackDivice::PLACEMENT_REMOVED)
            ->with('client:id,company_name')
            ->orderByDesc('removed_at')
            ->get(['id', 'rack_id', 'client_id', 'code', 'divice_name', 'model', 'serial_number', 'total_unit', 'installed_at', 'removed_at', 'placement_status']);

        // last movements touching this rack
        $movements = DeviceMovement::query()
            ->where(fn ($q) => $q->where('from_rack_id', $rack->id)->orWhere('to_rack_id', $rack->id))
            ->with(['client:id,company_name', 'fromRack:id,code,name', 'toRack:id,code,name', 'recorder:id,name'])
            ->withCount('attachments')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        // racks each client of this rack rents (targets for "Pindah" / "Kembalikan")
        $clientRackOptions = RackUnit::query()
            ->whereIn('client_id', $rack->rackDivices->pluck('client_id')->filter()->unique())
            ->whereHas('rack')
            ->with('rack:id,code,name')
            ->get(['id', 'rack_id', 'client_id', 'rack_divice_id'])
            ->groupBy(fn ($unit) => $unit->client_id . '-' . $unit->rack_id)
            ->map(fn ($units) => [
                'client_id' => $units->first()->client_id,
                'rack_id' => $units->first()->rack_id,
                'rack_label' => $units->first()->rack?->code ?? $units->first()->rack?->name,
                'free_units' => $units->whereNull('rack_divice_id')->count(),
            ])
            ->values();

        // carriers registered in the guest book today (optional link)
        $todayVisitors = Visitor::query()
            ->whereDate('visit_date', today())
            ->orderByDesc('checkin_time')
            ->get(['id', 'name', 'company_name', 'nik', 'phone', 'type_of_visit']);

        return Inertia::render('racks/RackShow', [
            'title' => 'Rack Details',
            'rack' => $rack,
            'clients' => $clients,
            'invoices' => $invoices,
            'interconnections' => $interconnections,
            'monitoringData' => $monitoringData,
            'telemetryLogs' => $telemetryLogs,
            'removedDevices' => $removedDevices,
            'movements' => $movements,
            'clientRackOptions' => $clientRackOptions,
            'todayVisitors' => $todayVisitors,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rack = Rack::with(['room.locationDataCenter', 'images'])->findOrFail($id);
        return Inertia::render('racks/RackEdit', [
            'title' => 'Edit Rack',
            'rack' => $rack,
            'rooms' => Room::select(
                'id',
                'name',
                'location_data_center_id'
            )->with('locationDataCenter')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rack $rack)
    {
        $validated = $request->validate([
            'room_id' => ['required', 'exists:rooms,id'],
            'code' => ['required', 'string', 'max:255', 'unique:racks,code,' . $rack->id],
            'name' => ['required', 'string', 'max:255'],
            'total_units' => ['required', 'integer', 'min:1', 'max:100'],
            'power_capacity' => ['required', 'integer', 'min:0'],
            'weight_capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive,maintenance,full'],
            'api_suhu' => ['nullable', 'string', 'max:100'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_image_ids' => ['nullable', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:rack_images,id'],
        ]);

        DB::beginTransaction();

        try {

            $oldTotalUnits = $rack->total_units;
            $newTotalUnits = (int) $validated['total_units'];

            /**
             * =========================
             * CHECK IF TOTAL UNITS REDUCED
             * =========================
             */
            if ($newTotalUnits < $oldTotalUnits) {

                /**
                 * Check occupied units
                 */
                $usedUnits = RackUnit::query()
                    ->where('rack_id', $rack->id)
                    ->where('unit_number', '>=', $newTotalUnits + 1)
                    ->where('unit_number', '<=', $oldTotalUnits)
                    ->where(function ($query) {
                        $query->whereNotNull('client_id')
                            ->orWhereNotNull('rack_divice_id')
                            ->orWhere('status', '!=', 'empty');
                    })
                    ->exists();

                if ($usedUnits) {

                    return back()
                        ->withInput()
                        ->withErrors([
                            'total_units' => 'Cannot reduce total units because some units are still occupied.',
                        ]);
                }

                /**
                 * Delete unused units
                 */
                RackUnit::query()
                    ->where('rack_id', $rack->id)
                    ->where('unit_number', '>=', $newTotalUnits + 1)
                    ->where('unit_number', '<=', $oldTotalUnits)
                    ->delete();
            }

            /**
             * =========================
             * UPDATE RACK
             * =========================
             */
            $rack->update([
                'room_id' => $validated['room_id'],
                'code' => $validated['code'],
                'name' => $validated['name'],
                'total_units' => $newTotalUnits,
                'power_capacity' => $validated['power_capacity'],
                'weight_capacity' => $validated['weight_capacity'],
                'description' => $validated['description'] ?? null,
                'status' => $validated['status'],
                'api_suhu' => $validated['api_suhu'] ?? null,
            ]);

            /**
             * =========================
             * IF TOTAL UNITS INCREASED
             * =========================
             */
            if ($newTotalUnits > $oldTotalUnits) {

                $units = [];

                for ($i = $oldTotalUnits + 1; $i <= $newTotalUnits; $i++) {

                    $units[] = [
                        'rack_id' => $rack->id,
                        'client_id' => null,
                        'rack_divice_id' => null,

                        // Unique internal code
                        'code' => 'UNT-' . Str::upper(Str::random(6)),

                        'unit_number' => $i,
                        'status' => 'empty',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                RackUnit::insert($units);
            }

            /**
             * =========================
             * RACK IMAGES
             * =========================
             */
            if (!empty($validated['remove_image_ids'])) {
                $removedImages = $rack->images()->whereIn('id', $validated['remove_image_ids'])->get();
                foreach ($removedImages as $image) {
                    Storage::disk('public')->delete($image->file_path);
                    $image->forceDelete();
                }
            }

            if ($request->hasFile('images')) {
                $this->storeRackImages($rack, $request->file('images'));
            }

            /**
             * Units / weight capacity may have changed
             */
            $rack->refresh()->refreshFullStatus();

            DB::commit();

            return redirect('admin/racks')
                ->with('success', 'Rack updated successfully.');
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors([
                    'message' => $th->getMessage(),
                ]);
        }
    }

    public function updateOwner(Request $request, string $id)
    {
        $validated = $request->validate([
            'rented_units' => ['required', 'integer', 'min:1'],
            'monthly_fee' => ['required', 'numeric', 'min:0'],
            'rental_start_date' => ['required', 'date'],
            'rental_end_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive,suspended,terminated'],
        ]);

        DB::beginTransaction();

        try {

            /**
             * =========================
             * FIND OWNER
             * =========================
             */
            $owner = ClientRack::findOrFail($id);

            /**
             * =========================
             * FIND RACK
             * =========================
             */
            $rack = Rack::findOrFail($owner->rack_id);

            /**
             * =========================
             * CURRENT RENTED UNITS
             * =========================
             */
            $currentUnits = $owner->rented_units;

            /**
             * =========================
             * NEW RENTED UNITS
             * =========================
             */
            $newUnits = $validated['rented_units'];

            /**
             * =========================
             * DIFFERENCE
             * =========================
             */
            $difference = $newUnits - $currentUnits;

            /**
             * =========================
             * IF INCREASE UNIT
             * =========================
             */
            if ($difference > 0) {

                $availableUnits = RackUnit::query()
                    ->where('rack_id', $rack->id)
                    ->where('status', 'empty')
                    ->count('id');

                if ($availableUnits < $difference) {

                    throw ValidationException::withMessages([
                        'rented_units' => 'Not enough available units in this rack.'
                    ]);
                }

                /**
                 * ASSIGN NEW UNITS
                 */
                $units = RackUnit::query()
                    ->where('rack_id', $rack->id)
                    ->where('status', 'empty')
                    ->orderBy('unit_number', 'asc')
                    ->limit($difference)
                    ->get();

                foreach ($units as $unit) {

                    $unit->update([
                        'client_id' => $owner->client_id,
                        'status' => 'used',
                    ]);
                }
            }

            /**
             * =========================
             * IF REDUCE UNIT
             * =========================
             */
            if ($difference < 0) {

                $removeCount = abs($difference);

                /**
                 * REMOVE LAST USED UNITS
                 */
                $units = RackUnit::query()
                    ->where('rack_id', $rack->id)
                    ->where('client_id', $owner->client_id)
                    ->where('status', 'used')
                    ->orderBy('unit_number', 'desc')
                    ->limit($removeCount)
                    ->get();

                foreach ($units as $unit) {

                    $unit->update([
                        'client_id' => null,
                        'rack_divice_id' => null,
                        'status' => 'empty',
                    ]);
                }
            }

            /**
             * =========================
             * UPDATE CLIENT RACK
             * =========================
             */
            $owner->update([
                'rented_units' => $validated['rented_units'],
                'monthly_fee' => $validated['monthly_fee'],
                'rental_start_date' => $validated['rental_start_date'],
                'rental_end_date' => $validated['rental_end_date'],
                'status' => $validated['status'],
            ]);

            /**
             * =========================
             * UPDATE RACK STATUS
             * =========================
             */
            $rack->refreshFullStatus();

            DB::commit();

            return back()->with(
                'success',
                'Rack owner updated successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            throw $th;
        }
    }

    public function updateDevice(
        Request $request,
        RackDivice $device
    ) {
        $validated = $request->validate([
            'divice_name' => ['required', 'string'],
            'divice_type' => ['nullable', 'string'],

            'model' => ['nullable', 'string'],
            'serial_number' => ['nullable', 'string'],

            'description' => ['nullable', 'string'],

            'power_usage' => ['nullable', 'numeric'],
            'weight_usage' => ['nullable', 'numeric'],

            'ip_address' => ['nullable', 'string'],

            'status' => ['required', 'in:active,inactive,maintenance'],

            'installed_at' => ['required', 'date'],
        ]);

        $device->update($validated);

        $device->rack?->refreshFullStatus();

        return back()->with(
            'success',
            'Device updated successfully.'
        );
    }

    public function updateDevicePort(
        Request $request,
        DevicePort $port
    ) {
        $validated = $request->validate([
            'port_name' => [
                'required',
                'string',
                'max:100',
            ],

            'port_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'port_type' => [
                'required',
                'in:ethernet,fiber,management,power,console,other',
            ],

            'connector_type' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                'in:available,connected,disabled,maintenance',
            ],

            'description' => [
                'nullable',
                'string',
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Prevent duplicate port
    |--------------------------------------------------------------------------
    */

        $exists = DevicePort::query()
            ->where('rack_divice_id', $port->rack_divice_id)
            ->where('port_name', $validated['port_name'])
            ->where('id', '!=', $port->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'message' => 'This port name already exists on the device.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | Connected port cannot be disabled
    |--------------------------------------------------------------------------
    */

        if (
            $validated['status'] !== 'connected' &&
            (
                $port->activeSourceCrossConnect()->exists() ||
                $port->activeDestinationCrossConnect()->exists()
            )
        ) {
            return back()->withErrors([
                'message' => 'This port is currently used by an active cross connect.',
            ]);
        }

        $port->update([
            'port_name' => $validated['port_name'],
            'port_number' => $validated['port_number'] ?? null,
            'port_type' => $validated['port_type'],
            'connector_type' => $validated['connector_type'] ?? null,
            'status' => $validated['status'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with(
            'success',
            'Device port updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $this->deleteRackWithCascading($id);
            DB::commit();

            return back()->with('success', 'Rack deleted successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == '23000') {
                return back()->withErrors(['message' => 'Failed to delete rack: It is currently being used by active devices, interconnections, or cross connects.']);
            }
            return back()->withErrors(['message' => 'Failed to delete rack: ' . $e->getMessage()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Failed to delete rack: ' . $th->getMessage()]);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:racks,id'],
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['ids'] as $id) {
                $this->deleteRackWithCascading($id);
            }
            DB::commit();

            return back()->with('success', count($validated['ids']) . ' racks deleted successfully.');
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->getCode() == '23000') {
                return back()->withErrors(['message' => 'Failed to delete racks: One or more racks are currently being used by active devices, interconnections, or cross connects.']);
            }
            return back()->withErrors(['message' => 'Failed to delete racks: ' . $e->getMessage()]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Failed to delete racks: ' . $th->getMessage()]);
        }
    }

    private function deleteRackWithCascading($id)
    {
        $rack = Rack::findOrFail($id);

        $devices = \App\Models\RackDivice::where('rack_id', $rack->id)->get();
        foreach ($devices as $device) {
            $ports = $device->ports;
            if ($ports) {
                foreach ($ports as $port) {
                    \App\Models\CrossConnect::where(function ($query) use ($port) {
                        $query->where('source_port_id', $port->id)
                              ->orWhere('destination_port_id', $port->id);
                    })->where('status', 'active')
                      ->update([
                          'status' => 'terminated',
                          'terminated_at' => now(),
                      ]);

                    \App\Models\InterconnectionRequest::where(function ($query) use ($port) {
                        $query->where('source_port_id', $port->id)
                              ->orWhere('destination_port_id', $port->id);
                    })->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                      ->update([
                          'status' => 'cancelled',
                      ]);

                    $port->delete();
                }
            }
            $device->delete();
        }

        RackUnit::where('rack_id', $rack->id)->delete();
        \App\Models\ClientRack::where('rack_id', $rack->id)->delete();
        RackImage::where('rack_id', $rack->id)->delete();
        $rack->delete();
    }

    /**
     * Store uploaded images for a rack.
     */
    private function storeRackImages(Rack $rack, array $files)
    {
        foreach ($files as $file) {
            $filename = time() . '_' . Str::random(6) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('rack-images/' . $rack->id, $filename, 'public');

            RackImage::create([
                'rack_id' => $rack->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    public function storeImages(Request $request, Rack $rack)
    {
        $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        try {
            $this->storeRackImages($rack, $request->file('images'));

            return back()->with('success', 'Rack images uploaded successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors(['message' => 'Failed to upload images: ' . $th->getMessage()]);
        }
    }

    public function updateImage(Request $request, RackImage $image)
    {
        $validated = $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $data = ['caption' => $validated['caption'] ?? null];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . Str::random(6) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());

                Storage::disk('public')->delete($image->file_path);

                $data['file_path'] = $file->storeAs('rack-images/' . $image->rack_id, $filename, 'public');
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getMimeType();
                $data['file_size'] = $file->getSize();
                $data['uploaded_by'] = Auth::id();
            }

            $image->update($data);

            return back()->with('success', 'Rack image updated successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors(['message' => 'Failed to update image: ' . $th->getMessage()]);
        }
    }

    public function destroyImage(RackImage $image)
    {
        try {
            Storage::disk('public')->delete($image->file_path);
            $image->forceDelete();

            return back()->with('success', 'Rack image deleted successfully.');
        } catch (\Throwable $th) {
            return back()->withErrors(['message' => 'Failed to delete image: ' . $th->getMessage()]);
        }
    }

    public function removeOwner(string $id)
    {
        DB::beginTransaction();

        try {

            /**
             * =========================
             * FIND OWNER
             * =========================
             */
            $owner = ClientRack::findOrFail($id);

            /**
             * =========================
             * FIND RACK
             * =========================
             */
            $rack = Rack::findOrFail($owner->rack_id);

            /**
             * =========================
             * RESET RACK UNITS
             * =========================
             */
            RackUnit::query()
                ->where('rack_id', $owner->rack_id)
                ->where('client_id', $owner->client_id)
                ->update([
                    'client_id' => null,
                    'rack_divice_id' => null,
                    'status' => 'empty',
                ]);

            /**
             * =========================
             * DELETE CLIENT RACK
             * =========================
             */
            $owner->delete();

            /**
             * =========================
             * UPDATE RACK STATUS
             * =========================
             */
            $rack->refreshFullStatus();

            DB::commit();

            return back()->with(
                'success',
                'Rack owner removed successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            throw $th;
        }
    }

    public function destroyDevice(string $id)
    {
        DB::beginTransaction();

        try {

            /**
             * =========================
             * FIND DEVICE
             * =========================
             */

            $device = RackDivice::findOrFail($id);

            /**
             * =========================
             * ONLY FOR WRONG INPUT
             * =========================
             * A device that physically moved (anything beyond its "Masuk"
             * record) must use "Keluarkan" so the history is kept.
             */
            if ($device->movements()->where('type', '!=', 'check_in')->exists() || $device->movements()->count() > 1) {
                DB::rollBack();

                return back()->withErrors([
                    'message' => 'Device ini sudah punya riwayat keluar-masuk. Gunakan "Keluarkan" agar riwayatnya tetap tersimpan.',
                ]);
            }

            /**
             * =========================
             * RELEASE RACK UNITS
             * =========================
             * Units stay rented to the client ('used'), not 'empty',
             * so they can't be rented to another client by mistake.
             */
            RackUnitAllocator::release($device);

            // wrong input: drop its "Masuk" record too
            foreach ($device->movements()->with('attachments')->get() as $movement) {
                foreach ($movement->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                }

                $movement->delete();
            }

            /**
             * =========================
             * HANDLE PORTS & CONNECTIONS
             * =========================
             */
            $ports = $device->ports;
            if ($ports) {
                foreach ($ports as $port) {
                    \App\Models\CrossConnect::where(function ($query) use ($port) {
                        $query->where('source_port_id', $port->id)
                              ->orWhere('destination_port_id', $port->id);
                    })->where('status', 'active')
                      ->update([
                          'status' => 'terminated',
                          'terminated_at' => now(),
                      ]);

                    \App\Models\InterconnectionRequest::where(function ($query) use ($port) {
                        $query->where('source_port_id', $port->id)
                              ->orWhere('destination_port_id', $port->id);
                    })->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
                      ->update([
                          'status' => 'cancelled',
                      ]);

                    $port->delete();
                }
            }

            /**
             * =========================
             * DELETE DEVICE
             * =========================
             */

            $device->delete();

            $device->rack?->refreshFullStatus();

            DB::commit();

            return back()->with(
                'success',
                'Device deleted successfully.'
            );
        } catch (\Throwable $th) {

            DB::rollBack();

            return back()->withErrors([
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function destroyDevicePort(DevicePort $port)
    {
        if (
            $port->activeSourceCrossConnect()->exists() ||
            $port->activeDestinationCrossConnect()->exists()
        ) {
            return back()->withErrors([
                'message' => 'This port cannot be deleted because it is used by an active cross connect.',
            ]);
        }

        $port->delete();

        return back()->with(
            'success',
            'Device port deleted successfully.'
        );
    }
}
