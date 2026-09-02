<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';

    protected $fillable = [
        'client_id',
        'rack_id',
        'code',
        'name',
        'description',
        'billing_cycle',
        'start_date',
        'end_date',
        'next_due_date',
        'ppn_enabled',
        'ppn_percentage',
        'ppn_amount',
        'pph23_enabled',
        'pph23_percentage',
        'pph23_amount',
        'monthly_total',
        'total',
        'status',
    ];

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class, 'service_id');
    }
}
