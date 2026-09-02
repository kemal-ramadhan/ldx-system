<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'location_data_center_id',
        'code',
        'name',
        'description',
    ];

    public function locationDataCenter()
    {
        return $this->belongsTo(LocationDataCenter::class, 'location_data_center_id');
    }

    public function racks()
    {
        return $this->hasMany(Rack::class, 'room_id');
    }
}
