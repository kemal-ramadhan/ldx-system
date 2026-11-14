<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RackDivice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rack_id',
        'client_id',
        'code',
        'divice_name',
        'divice_type',
        'model',
        'serial_number',
        'description',
        'power_usage',
        'weight_usage',
        'ip_address',
        'total_unit',
        'status',
        'installed_at',
        'placement_status',
        'start_unit',
        'end_unit',
        'removed_at',
    ];

    protected $casts = [
        // serialized in app timezone (Asia/Jakarta) so the UI shows the real local time
        'installed_at' => 'datetime:Y-m-d H:i',
        'removed_at' => 'datetime:Y-m-d H:i',
    ];

    public const PLACEMENT_IN_RACK = 'in_rack';
    public const PLACEMENT_OUT_TEMPORARY = 'out_temporary';
    public const PLACEMENT_REMOVED = 'removed';

    /**
     * Devices that permanently left the data center are hidden everywhere
     * (lists, rack totals, client pages) — like soft deletes. History pages
     * opt in with withoutGlobalScope('excludeRemoved') / withRemoved().
     */
    protected static function booted(): void
    {
        static::addGlobalScope('excludeRemoved', function (Builder $query) {
            $query->where(
                $query->getModel()->qualifyColumn('placement_status'),
                '!=',
                self::PLACEMENT_REMOVED
            );
        });
    }

    public function scopeWithRemoved(Builder $query): Builder
    {
        return $query->withoutGlobalScope('excludeRemoved');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(DeviceMovement::class, 'rack_divice_id')->orderByDesc('occurred_at')->orderByDesc('id');
    }

    /**
     * =========================
     * RACK
     * =========================
     */
    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    /**
     * =========================
     * CLIENT
     * =========================
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * =========================
     * RACK UNITS
     * =========================
     */
    public function rackUnits()
    {
        return $this->hasMany(RackUnit::class, 'rack_divice_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(
            DevicePort::class,
            'rack_divice_id'
        );
    }
}
