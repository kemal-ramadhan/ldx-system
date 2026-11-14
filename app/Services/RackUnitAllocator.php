<?php

namespace App\Services;

use App\Models\RackDivice;
use App\Models\RackUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Contiguous rack-unit allocation for devices.
 *
 * Unit states:
 *  - rented to a client (client_id set) and free       -> status 'used',  rack_divice_id null
 *  - occupied by a device                               -> status 'used',  rack_divice_id = device
 *  - not rented                                         -> status 'empty', client_id null
 */
class RackUnitAllocator
{
    /**
     * Free units of $clientId in $rackId, first-fit contiguous block of $size.
     * $startUnit forces the block to start at that unit number.
     */
    public static function findContiguous(int $rackId, int $clientId, int $size, ?int $startUnit = null): Collection
    {
        $free = RackUnit::query()
            ->where('rack_id', $rackId)
            ->where('client_id', $clientId)
            ->whereNull('rack_divice_id')
            ->orderBy('unit_number')
            ->lockForUpdate()
            ->get();

        $byNumber = $free->keyBy('unit_number');
        $starts = $startUnit !== null ? [$startUnit] : $free->pluck('unit_number')->all();

        foreach ($starts as $start) {
            $block = collect();

            for ($n = $start; $n < $start + $size; $n++) {
                if (! $byNumber->has($n)) {
                    break;
                }
                $block->push($byNumber->get($n));
            }

            if ($block->count() === $size) {
                return $block;
            }
        }

        throw ValidationException::withMessages([
            'units' => $startUnit !== null
                ? "Unit U{$startUnit}–U" . ($startUnit + $size - 1) . " tidak tersedia/berurutan untuk client ini."
                : "Tidak ada {$size} unit berurutan yang kosong milik client ini di rack tujuan.",
        ]);
    }

    /**
     * Put the device on the given units and store its U position.
     */
    public static function assign(RackDivice $device, Collection $units): void
    {
        RackUnit::query()
            ->whereIn('id', $units->pluck('id'))
            ->update([
                'client_id' => $device->client_id,
                'rack_divice_id' => $device->id,
                'status' => 'used',
            ]);

        $device->forceFill([
            'rack_id' => $units->first()->rack_id,
            'start_unit' => $units->min('unit_number'),
            'end_unit' => $units->max('unit_number'),
        ])->save();
    }

    /**
     * Free the device's units. Units stay rented to the client ('used'),
     * so they cannot be rented to another client by mistake.
     */
    public static function release(RackDivice $device): void
    {
        RackUnit::query()
            ->where('rack_divice_id', $device->id)
            ->update([
                'rack_divice_id' => null,
                'status' => DB::raw("CASE WHEN client_id IS NULL THEN 'empty' ELSE 'used' END"),
            ]);
    }
}
