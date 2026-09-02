<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategorie extends Model
{
    protected $fillable = [
        'categori',
        'slug',
        'description',
    ];

    public function product()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }
}
