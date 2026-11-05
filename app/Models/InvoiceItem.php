<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';
    protected $fillable = [
        'invoice_id',
        'product_id',
        'name',
        'quantity',
        'price',
        'total',
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function product()
    {
        return $this->belongsTo(
            Product::class,
            'product_id'
        );
    }
}
