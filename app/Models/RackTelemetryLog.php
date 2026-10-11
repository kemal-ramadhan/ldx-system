<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackTelemetryLog extends Model
{
    protected $fillable = [
        'rack_id',
        'api_rack_name',
        'slave',
        'status',
        'temp',
        'hum',
        'status_temp',
        'status_hum',
        'pln_ada',
        'pln_voltage',
        'pln_current',
        'pln_power',
        'pln_status',
        'ups_ada',
        'ups_voltage',
        'ups_current',
        'ups_power',
        'ups_status',
        'recorded_at',
    ];

    protected $casts = [
        'pln_ada' => 'boolean',
        'ups_ada' => 'boolean',
        'temp' => 'float',
        'hum' => 'float',
        'pln_voltage' => 'float',
        'pln_current' => 'float',
        'pln_power' => 'float',
        'ups_voltage' => 'float',
        'ups_current' => 'float',
        'ups_power' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }
}
