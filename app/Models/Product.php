<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_category_id',
        'code',
        'name',
        'description',
        'unit',
        'base_price',
        'billing_type',
        'status'
    ];

    public function categories()
    {
        return $this->belongsTo(ProductCategorie::class, 'product_category_id');
    }
}
