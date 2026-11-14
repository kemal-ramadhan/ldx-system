<?php

namespace App\Services;

use App\Models\CrossConnect;
use App\Models\DeviceMovement;
use App\Models\DevicePort;
use App\Models\InterconnectionRequest;
use App\Models\Rack;
use App\Models\RackDivice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Keluar-masuk device. Every action runs in one transaction, updates the
 * device placement + rack units, and writes a DeviceMovement with attachments.
 *
 * $data keys (all optional unless stated): occurred_at, carrier_name,
 * carrier_company, carrier_id_number, carrier_phone, visitor_id, reason, notes,
 * photos[] (UploadedFile), bast[] (UploadedFile).
 */
class DeviceMovementService
{
    /*
    |--------------------------------------------------------------------------
    | MASUK (called by RackController::storeDevice after the device is created)
    |--------------------------------------------------------------------------
    */
    public static function recordCheckIn(RackDivice $device, array $data = []): DeviceMovement
    {
        return self::record($device, 'check_in', $data, [
            'to_rack_id' => $device->rack_id,
            'to_start_unit' => $device->start_unit,
            'to_end_unit' => $device->end_unit,
            'occurred_at' => $data['occurred_at'] ?? $device->installed_at ?? now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | KELUAR SEMENTARA
    |--------------------------------------------------------------------------
    */
    public static function tempOut(RackDivice $device, array $data, bool $reserveUnits = true): DeviceMovement
    {
        return DB::transaction(function () use ($device, $data, $reserveUnits) {
            $device = self::lock($device, [RackDivice::PLACEMENT_IN_RACK], 'keluar sementara');
            $from = self::position($device);

            if (! $reserveUnits) {
                RackUnitAllocator::release($device);
            }

            $device->forceFill([
                'placement_status' => RackDivice::PLACEMENT_OUT_TEMPORARY,
            ] + ($reserveUnits ? [] : ['start_unit' => null, 'end_unit' => null]))->save();

            $movement = self::record($device, 'temp_out', $data, $from + ['units_reserved' => $reserveUnits]);

            self::refreshRacks($from['from_rack_id']);

            return $movement;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | KEMBALI MASUK
    |--------------------------------------------------------------------------
    */
    public static function returnIn(RackDivice $device, array $data, ?int $rackId = null, ?int $startUnit = null): DeviceMovement
    {
        return DB::transaction(function () use ($device, $data, $rackId, $startUnit) {
            $device = self::lock($device, [RackDivice::PLACEMENT_OUT_TEMPORARY], 'dikembalikan');
            $lastOut = $device->movements()->where('type', 'temp_out')->first();
            $reserved = $device->rackUnits()->exists();

            if (! $reserved) {
                // units were released: allocate again (default: the rack it left from)
                $targetRack = $rackId ?: ($lastOut?->from_rack_id ?? $device->rack_id);
                self::ensureClientRentsRack($device, $targetRack);

                $units = RackUnitAllocator::findContiguous($targetRack, (int) $device->client_id, (int) $device->total_unit, $startUnit);
                RackUnitAllocator::assign($device, $units);
            }

            $device->forceFill(['placement_status' => RackDivice::PLACEMENT_IN_RACK])->save();
            $device->refresh();

            $movement = self::record($device, 'return_in', $data, [
                'to_rack_id' => $device->rack_id,
                'to_start_unit' => $device->start_unit,
                'to_end_unit' => $device->end_unit,
                'units_reserved' => $reserved,
            ]);

            self::refreshRacks($device->rack_id);

            return $movement;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PINDAH RACK / UNIT
    |--------------------------------------------------------------------------
    */
    public static function relocate(RackDivice $device, array $data, int $rackId, ?int $startUnit = null): DeviceMovement
    {
        return DB::transaction(function () use ($device, $data, $rackId, $startUnit) {
            $device = self::lock($device, [RackDivice::PLACEMENT_IN_RACK], 'dipindah');
            self::ensureClientRentsRack($device, $rackId);
            $from = self::position($device);

            // free current units first so a shift inside the same rack can overlap
            RackUnitAllocator::release($device);
            $units = RackUnitAllocator::findContiguous($rackId, (int) $device->client_id, (int) $device->total_unit, $startUnit);
            RackUnitAllocator::assign($device, $units);
            $device->refresh();

            if ($from['from_rack_id'] === $device->rack_id && $from['from_start_unit'] === $device->start_unit) {
                throw ValidationException::withMessages([
                    'units' => 'Posisi tujuan sama dengan posisi sekarang.',
                ]);
            }

            $movement = self::record($device, 'relocate', $data, $from + [
                'to_rack_id' => $device->rack_id,
                'to_start_unit' => $device->start_unit,
                'to_end_unit' => $device->end_unit,
            ]);

            self::refreshRacks($from['from_rack_id'], $device->rack_id);

            return $movement;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | KELUAR PERMANEN
    |--------------------------------------------------------------------------
    | Device is NOT deleted: it becomes "removed" and keeps its history.
    | Ports are disabled (not deleted) so cross-connect history stays intact.
    */
    public static function checkOut(RackDivice $device, array $data): DeviceMovement
    {
        return DB::transaction(function () use ($device, $data) {
            $device = self::lock($device, [RackDivice::PLACEMENT_IN_RACK, RackDivice::PLACEMENT_OUT_TEMPORARY], 'dikeluarkan');
            $from = self::position($device);

            // rack it was in (temp-out with released units has no position anymore)
            if (! $from['from_rack_id']) {
                $from['from_rack_id'] = $device->rack_id;
            }

            self::disconnectPorts($device, 'Device dikeluarkan dari data center (' . ($data['reason'] ?? '-') . ')');
            RackUnitAllocator::release($device);

            $device->forceFill([
                'placement_status' => RackDivice::PLACEMENT_REMOVED,
                'status' => 'inactive',
                'removed_at' => $data['occurred_at'] ?? now(),
                'start_unit' => null,
                'end_unit' => null,
            ])->save();

            $movement = self::record($device, 'check_out', $data, $from);

            self::refreshRacks($from['from_rack_id']);

            return $movement;
        });
    }

    /**
     * Active cross connects using the device's ports (for warnings in the UI).
     */
    public static function activeCrossConnects(RackDivice $device)
    {
        $portIds = $device->ports()->pluck('id');

        return CrossConnect::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereIn('source_port_id', $portIds)->orWhereIn('destination_port_id', $portIds))
            ->get(['id', 'cross_connect_number']);
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNALS
    |--------------------------------------------------------------------------
    */

    private static function lock(RackDivice $device, array $allowed, string $action): RackDivice
    {
        $locked = RackDivice::withRemoved()->lockForUpdate()->findOrFail($device->id);

        if (! in_array($locked->placement_status, $allowed, true)) {
            $labels = [
                RackDivice::PLACEMENT_IN_RACK => 'sedang di rack',
                RackDivice::PLACEMENT_OUT_TEMPORARY => 'sedang keluar sementara',
                RackDivice::PLACEMENT_REMOVED => 'sudah keluar permanen',
            ];

            throw ValidationException::withMessages([
                'device' => "Device {$labels[$locked->placement_status]}, tidak bisa {$action}.",
            ]);
        }

        if (! $locked->client_id) {
            throw ValidationException::withMessages(['device' => 'Device belum terhubung ke client.']);
        }

        return $locked;
    }

    private static function position(RackDivice $device): array
    {
        $hasUnits = $device->start_unit !== null;

        return [
            'from_rack_id' => $hasUnits ? $device->rack_id : null,
            'from_start_unit' => $device->start_unit,
            'from_end_unit' => $device->end_unit,
        ];
    }

    private static function ensureClientRentsRack(RackDivice $device, int $rackId): void
    {
        $rents = Rack::query()
            ->whereKey($rackId)
            ->whereHas('rackUnits', fn ($q) => $q->where('client_id', $device->client_id))
            ->exists();

        if (! $rents) {
            throw ValidationException::withMessages([
                'rack_id' => 'Client pemilik device tidak menyewa unit di rack tujuan.',
            ]);
        }
    }

    /**
     * Terminate active cross connects, cancel open interconnection requests and
     * disable the ports (the other end of a cross connect becomes available).
     */
    private static function disconnectPorts(RackDivice $device, string $reason): void
    {
        $ports = $device->ports()->get();
        $portIds = $ports->pluck('id');

        if ($portIds->isEmpty()) {
            return;
        }

        $crossConnects = CrossConnect::query()
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereIn('source_port_id', $portIds)->orWhereIn('destination_port_id', $portIds))
            ->lockForUpdate()
            ->get();

        foreach ($crossConnects as $crossConnect) {
            $otherPortId = $portIds->contains($crossConnect->source_port_id)
                ? $crossConnect->destination_port_id
                : $crossConnect->source_port_id;

            $crossConnect->update([
                'status' => 'terminated',
                'terminated_at' => now(),
                'notes' => trim(($crossConnect->notes ? $crossConnect->notes . "\n\n" : '') . $reason),
                'terminate_requested_at' => null,
                'terminate_requested_by' => null,
                'terminate_request_reason' => null,
            ]);

            if ($otherPortId && ! $portIds->contains($otherPortId)) {
                DevicePort::whereKey($otherPortId)->where('status', 'connected')->update(['status' => 'available']);
            }
        }

        InterconnectionRequest::query()
            ->where(fn ($q) => $q->whereIn('source_port_id', $portIds)->orWhereIn('destination_port_id', $portIds))
            ->whereNotIn('status', ['completed', 'cancelled', 'rejected'])
            ->update(['status' => 'cancelled']);

        DevicePort::whereIn('id', $portIds)->update(['status' => 'disabled']);
    }

    private static function record(RackDivice $device, string $type, array $data, array $extra = []): DeviceMovement
    {
        $movement = DeviceMovement::create([
            'movement_number' => DeviceMovement::nextNumber(),
            'rack_divice_id' => $device->id,
            'type' => $type,
            'occurred_at' => $extra['occurred_at'] ?? $data['occurred_at'] ?? now(),
            'client_id' => $device->client_id,
            'device_name' => $device->divice_name,
            'device_code' => $device->code,
            'serial_number' => $device->serial_number,
            'model' => $device->model,
            'carrier_name' => $data['carrier_name'] ?? null,
            'carrier_company' => $data['carrier_company'] ?? null,
            'carrier_id_number' => $data['carrier_id_number'] ?? null,
            'carrier_phone' => $data['carrier_phone'] ?? null,
            'visitor_id' => $data['visitor_id'] ?? null,
            'reason' => $data['reason'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ] + array_diff_key($extra, ['occurred_at' => true]));

        self::storeAttachments($movement, $data['photos'] ?? [], 'photo');
        self::storeAttachments($movement, $data['bast'] ?? [], 'bast');

        return $movement;
    }

    private static function storeAttachments(DeviceMovement $movement, array $files, string $type): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $original = $file->getClientOriginalName();
            $name = time() . '_' . Str::random(6) . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
            $path = $file->storeAs("device-movements/{$movement->id}", $name, 'public');

            $movement->attachments()->create([
                'type' => $type,
                'file_path' => $path,
                'file_name' => $original,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);
        }
    }

    private static function refreshRacks(?int ...$rackIds): void
    {
        foreach (array_unique(array_filter($rackIds)) as $rackId) {
            Rack::find($rackId)?->refreshFullStatus();
        }
    }
}
