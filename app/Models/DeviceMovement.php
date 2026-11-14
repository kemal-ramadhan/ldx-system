<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One physical movement of a device (keluar-masuk barang).
 * Device data is snapshotted so history stays correct after later edits.
 */
class DeviceMovement extends Model
{
    public const TYPES = [
        'check_in' => 'Masuk',
        'check_out' => 'Keluar Permanen',
        'temp_out' => 'Keluar Sementara',
        'return_in' => 'Kembali Masuk',
        'relocate' => 'Pindah',
    ];

    protected $fillable = [
        'movement_number',
        'rack_divice_id',
        'type',
        'occurred_at',
        'client_id',
        'device_name',
        'device_code',
        'serial_number',
        'model',
        'from_rack_id',
        'from_start_unit',
        'from_end_unit',
        'to_rack_id',
        'to_start_unit',
        'to_end_unit',
        'units_reserved',
        'carrier_name',
        'carrier_company',
        'carrier_id_number',
        'carrier_phone',
        'visitor_id',
        'reason',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime:Y-m-d H:i',
        'units_reserved' => 'boolean',
    ];

    protected $appends = ['type_label'];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function device(): BelongsTo
    {
        // keep history for devices that left (removed) or were deleted
        return $this->belongsTo(RackDivice::class, 'rack_divice_id')
            ->withoutGlobalScope('excludeRemoved')
            ->withTrashed();
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function fromRack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'from_rack_id')->withTrashed();
    }

    public function toRack(): BelongsTo
    {
        return $this->belongsTo(Rack::class, 'to_rack_id')->withTrashed();
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(DeviceMovementAttachment::class, 'device_movement_id');
    }

    /**
     * Next DM-YYYYMM-00001 number (call inside a transaction).
     */
    public static function nextNumber(): string
    {
        $prefix = 'DM-' . now()->format('Ym') . '-';

        $last = self::query()
            ->where('movement_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('movement_number')
            ->value('movement_number');

        $sequence = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
    }
}
