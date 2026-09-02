<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationDataCenter extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'location_data_center_id');
    }
}
