<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image', 'is_customizable', 'variant_value'];

    protected $casts = [
        'is_customizable' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}