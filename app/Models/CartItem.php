<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'selected_options',
        'custom_image',
        'custom_images',
        'product_quantity_id',
        'tier_qty',
        'tier_price',
    ];

    protected $casts = [
        'custom_images'    => 'array',
        'selected_options' => 'array',
    ];
    public function tier()
    {
        return $this->belongsTo(ProductQuantity::class, 'product_quantity_id');
    }

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}