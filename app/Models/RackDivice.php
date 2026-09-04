<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RackDivice extends Model
{
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
    ];

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
