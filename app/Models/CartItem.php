<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'custom_image',
        'custom_images',
        'selected_options',
    ];

    protected $casts = [
        'custom_images'    => 'array',
        'selected_options' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}