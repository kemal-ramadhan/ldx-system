<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRack;
use App\Models\Rack;
use App\Models\RackDivice;
use App\Models\RackUnit;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search;
        $room = $request->room;

        $racks = Rack::with('room.locationDataCenter')
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

            $remainingUnits = RackUnit::query()->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->count('id');

            if ($remainingUnits <= 0) {

                $rack->update([
                    'status' => 'full'
                ]);
            }

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
        ]);

        DB::beginTransaction();

        try {

            /**
             * =========================
             * GET EMPTY UNITS
             * =========================
             */

            $emptyUnits = RackUnit::query()
                ->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->orderBy('unit_number', 'asc')
                ->get();

            /**
             * =========================
             * FIND CONTIGUOUS UNITS
             * =========================
             */

            $required = (int) $validated['total_unit'];

            $matchedUnits = [];

            for ($i = 0; $i < count($emptyUnits); $i++) {

                $currentGroup = [$emptyUnits[$i]];

                for ($j = $i + 1; $j < count($emptyUnits); $j++) {

                    $prev = $emptyUnits[$j - 1];
                    $current = $emptyUnits[$j];

                    /**
                     * CHECK SEQUENTIAL
                     */

                    if ($current->unit_number == $prev->unit_number + 1) {

                        $currentGroup[] = $current;

                        if (count($currentGroup) == $required) {

                            $matchedUnits = $currentGroup;
                            break 2;
                        }

                    } else {

                        break;
                    }
                }

                /**
                 * FOR 1U DEVICE
                 */

                if ($required == 1) {

                    $matchedUnits = [$emptyUnits[$i]];
                    break;
                }
            }

            /**
             * =========================
             * NO AVAILABLE SLOT
             * =========================
             */

            if (count($matchedUnits) < $required) {

                return back()->withErrors([
                    'message' => 'No contiguous rack units available.'
                ]);
            }

            /**
             * =========================
             * START & END UNIT
             * =========================
             */

            $startUnit = $matchedUnits[0]->unit_number;

            $endUnit = $matchedUnits[count($matchedUnits) - 1]->unit_number;

            /**
             * =========================
             * CREATE DEVICE
             * =========================
             */

            $device = RackDivice::create([
                'rack_id' => $rack->id,
                'client_id' => $validated['client_id'],

                'code' => 'DVC-' . strtoupper(Str::random(6)),

                'divice_name' => $validated['divice_name'],
                'divice_type' => $validated['divice_type'],

                'model' => $validated['model'],
                'serial_number' => $validated['serial_number'],

                'description' => $validated['description'],

                'power_usage' => $validated['power_usage'],
                'weight_usage' => $validated['weight_usage'],

                'ip_address' => $validated['ip_address'],

                'start_unit' => $startUnit,
                'end_unit' => $endUnit,

                'total_unit' => $validated['total_unit'],

                'status' => $validated['status'],
            ]);

            /**
             * =========================
             * ASSIGN UNITS
             * =========================
             */

            foreach ($matchedUnits as $unit) {

                $unit->update([
                    'client_id' => $validated['client_id'],
                    'rack_divice_id' => $device->id,
                    'status' => 'used',
                ]);
            }

            DB::commit();

            return back()->with(
                'success',
                'Device added successfully.'
            );

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
                'rackDivices.rackUnits',
            ])
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

        return Inertia::render('racks/RackShow', [
            'title' => 'Rack Details',
            'rack' => $rack,
            'clients' => $clients
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $rack = Rack::with('room.locationDataCenter')->findOrFail($id);
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
            $remainingUnits = RackUnit::query()
                ->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->count('id');

            $rack->update([
                'status' => $remainingUnits <= 0
                    ? 'full'
                    : 'active'
            ]);

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
    )
    {
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
        ]);

        $device->update($validated);

        return back()->with(
            'success',
            'Device updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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
            $emptyUnits = RackUnit::query()
                ->where('rack_id', $rack->id)
                ->where('status', 'empty')
                ->count('id');

            if ($emptyUnits > 0 && $rack->status === 'full') {

                $rack->update([
                    'status' => 'active',
                ]);
            }

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
             * RELEASE RACK UNITS
             * =========================
             */

            RackUnit::query()
                ->where('rack_id', $device->rack_id)
                ->where('rack_divice_id', $device->id)
                ->update([
                    'rack_divice_id' => null,
                    'status' => 'empty',
                ]);

            /**
             * =========================
             * DELETE DEVICE
             * =========================
             */

            $device->delete();

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
}
