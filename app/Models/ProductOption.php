<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    protected $fillable = ['product_id', 'name', 'values', 'value_prices'];

    protected $casts = [
        'value_prices' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    // Convert "S,M,L,XL" -> ['S','M','L','XL']
    public function getValuesArrayAttribute()
    {
        return array_map('trim', explode(',', $this->values));
    }
}