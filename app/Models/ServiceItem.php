<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItem extends Model
{
    protected $table = 'service_items';

    protected $fillable = [
        'service_id',
        'product_id',
        'description',
        'quantity',
        'price',
        'subtotal',
        'ppn_enabled',
        'ppn_percentage',
        'ppn_amount',
        'pph23_enabled',
        'pph23_percentage',
        'pph23_amount',
    ];

    protected $casts = [
        'ppn_enabled' => 'boolean',
        'pph23_enabled' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
