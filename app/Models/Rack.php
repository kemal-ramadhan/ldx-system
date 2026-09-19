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
}
