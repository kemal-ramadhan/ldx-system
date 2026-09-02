<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientRack extends Model
{
    protected $fillable = [
        'client_id',
        'rack_id',
        'rented_units',
        'monthly_fee',
        'rental_start_date',
        'rental_end_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'rack_id');
    }

    public function devices()
    {
        return $this->hasMany(RackDivice::class, 'rack_id', 'rack_id')
            ->where('client_id', $this->client_id);
    }
}
