<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rack extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id',
        'code',
        'name',
        'total_units',
        'power_capacity',
        'weight_capacity',
        'description',
        'status',
        'api_suhu',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function clientRacks()
    {
        return $this->hasMany(ClientRack::class, 'rack_id');
    }

    public function rackDivices()
    {
        return $this->hasMany(RackDivice::class, 'rack_id');
    }

    public function telemetryLogs()
    {
        return $this->hasMany(RackTelemetryLog::class, 'rack_id');
    }

    public function images()
    {
        return $this->hasMany(RackImage::class, 'rack_id')->latest();
    }

    public function rackUnits()
    {
        return $this->hasMany(RackUnit::class, 'rack_id');
    }

    /**
     * Rack is full when every unit is filled by a device, or the
     * devices weight reaches 100% of the weight capacity. Rented
     * units without a device don't count. Racks set to
     * inactive / maintenance manually are left untouched.
     */
    public function refreshFullStatus(): void
    {
        if (in_array($this->status, ['inactive', 'maintenance'])) {
            return;
        }

        $unitsWithoutDevice = $this->rackUnits()
            ->whereNull('rack_divice_id')
            ->count();

        $unitsFull = $this->total_units > 0 && $unitsWithoutDevice <= 0;

        $weightUsage = (float) $this->rackDivices()->sum('weight_usage');

        $weightFull = $this->weight_capacity > 0
            && $weightUsage >= $this->weight_capacity;

        $status = ($unitsFull || $weightFull) ? 'full' : 'active';

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
