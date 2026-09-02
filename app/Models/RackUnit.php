<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RackUnit extends Model
{
    protected $fillable = [
        'rack_id',
        'client_id',
        'rack_divice_id',
        'code',
        'unit_number',
        'status',
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function rackDevice()
    {
        return $this->belongsTo(RackDivice::class, 'rack_divice_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
