<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeviceMovement;
use App\Models\Rack;
use App\Models\RackDivice;
use App\Services\DeviceMovementService;
use App\Support\DeviceMovementRules;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Keluar-masuk device (admin only — routes are under role:super-admin).
 */
class DeviceMovementController extends Controller
{
    /**
     * =========================================================
     * LOG (all movements)
     * =========================================================
     */
    public function index(Request $request)
    {
        $query = DeviceMovement::query()
            ->with(['client:id,company_name', 'fromRack:id,code,name', 'toRack:id,code,name', 'recorder:id,name'])
            ->withCount('attachments')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(fn ($q) => $q
                ->where('movement_number', 'like', "%{$search}%")
                ->orWhere('device_name', 'like', "%{$search}%")
                ->orWhere('device_code', 'like', "%{$search}%")
                ->orWhere('serial_number', 'like', "%{$search}%")
                ->orWhere('carrier_name', 'like', "%{$search}%")
                ->orWhere('carrier_company', 'like', "%{$search}%"));
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('rack_id')) {
            $query->where(fn ($q) => $q->where('from_rack_id', $request->rack_id)->orWhere('to_rack_id', $request->rack_id));
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        // current placement summary
        $placement = RackDivice::withRemoved()
            ->selectRaw('placement_status, count(*) as total')
            ->groupBy('placement_status')
            ->pluck('total', 'placement_status');

        return Inertia::render('device-movements/Index', [
            'title' => 'Keluar-Masuk Device',
            'movements' => $query->paginate(20)->withQueryString(),
            'summary' => [
                'in_rack' => (int) ($placement['in_rack'] ?? 0),
                'out_temporary' => (int) ($placement['out_temporary'] ?? 0),
                'removed' => (int) ($placement['removed'] ?? 0),
                'today' => DeviceMovement::whereDate('occurred_at', today())->count(),
            ],
            'types' => DeviceMovement::TYPES,
            'racks' => Rack::orderBy('code')->get(['id', 'code', 'name']),
            'clients' => Client::orderBy('company_name')->get(['id', 'company_name']),
            'filters' => $request->only(['search', 'type', 'rack_id', 'client_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * =========================================================
     * DETAIL OF ONE MOVEMENT
     * =========================================================
     */
    public function show(DeviceMovement $movement)
    {
        $movement->load([
            'device:id,code,divice_name,placement_status,rack_id,deleted_at',
            'client:id,company_name',
            'fromRack:id,code,name',
            'toRack:id,code,name',
            'recorder:id,name',
            'visitor:id,name,company_name,nik,phone',
            'attachments.uploader:id,name',
        ]);

        return Inertia::render('device-movements/Show', [
            'title' => $movement->movement_number,
            'movement' => $movement,
        ]);
    }

    /**
     * =========================================================
     * HISTORY OF ONE DEVICE (incl. removed)
     * =========================================================
     */
    public function history(string $device)
    {
        $record = RackDivice::withRemoved()
            ->withTrashed()
            ->with(['client:id,company_name', 'rack:id,code,name'])
            ->findOrFail($device);

        $movements = $record->movements()
            ->with(['fromRack:id,code,name', 'toRack:id,code,name', 'recorder:id,name', 'attachments'])
            ->get();

        return Inertia::render('devices/History', [
            'title' => 'Riwayat ' . $record->divice_name,
            'device' => $record,
            'movements' => $movements,
        ]);
    }

    /**
     * =========================================================
     * ACTIONS
     * =========================================================
     */
    public function tempOut(Request $request, string $device)
    {
        $data = $request->validate(
            DeviceMovementRules::common(true) + ['reserve_units' => ['required', 'boolean']],
            DeviceMovementRules::messages()
        );

        return $this->run(fn () => DeviceMovementService::tempOut(
            $this->device($device),
            $this->payload($request, $data),
            $request->boolean('reserve_units')
        ), 'Device dicatat keluar sementara.');
    }

    public function returnIn(Request $request, string $device)
    {
        $data = $request->validate(
            DeviceMovementRules::common(false) + [
                'rack_id' => ['nullable', 'integer', 'exists:racks,id'],
                'start_unit' => ['nullable', 'integer', 'min:1'],
            ],
            DeviceMovementRules::messages()
        );

        return $this->run(fn () => DeviceMovementService::returnIn(
            $this->device($device),
            $this->payload($request, $data),
            $data['rack_id'] ?? null,
            $data['start_unit'] ?? null
        ), 'Device dicatat kembali masuk.');
    }

    public function relocate(Request $request, string $device)
    {
        $data = $request->validate(
            DeviceMovementRules::common(true) + [
                'rack_id' => ['required', 'integer', 'exists:racks,id'],
                'start_unit' => ['nullable', 'integer', 'min:1'],
            ],
            DeviceMovementRules::messages() + ['rack_id.required' => 'Rack tujuan wajib dipilih.']
        );

        return $this->run(fn () => DeviceMovementService::relocate(
            $this->device($device),
            $this->payload($request, $data),
            (int) $data['rack_id'],
            $data['start_unit'] ?? null
        ), 'Device berhasil dipindah.');
    }

    public function checkOut(Request $request, string $device)
    {
        $data = $request->validate(DeviceMovementRules::common(true), DeviceMovementRules::messages());

        return $this->run(fn () => DeviceMovementService::checkOut(
            $this->device($device),
            $this->payload($request, $data)
        ), 'Device dicatat keluar permanen. Riwayatnya tetap tersimpan.');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    private function device(string $id): RackDivice
    {
        return RackDivice::withRemoved()->findOrFail($id);
    }

    private function payload(Request $request, array $data): array
    {
        return [
            ...collect($data)->only([
                'occurred_at', 'reason', 'notes',
                'carrier_name', 'carrier_company', 'carrier_id_number', 'carrier_phone', 'visitor_id',
            ])->all(),
            'photos' => $request->file('photos', []),
            'bast' => $request->file('bast', []),
        ];
    }

    /**
     * Validation errors (e.g. no contiguous units) bubble up to the form.
     */
    private function run(callable $action, string $message)
    {
        $action();

        return back()->with('success', $message);
    }
}
