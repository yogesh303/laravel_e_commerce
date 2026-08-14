<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'custom_image',
        'custom_images',
        'logo_images',
        'selected_options',
        'product_quantity_id',   // ← was missing
        'tier_qty',               // ← was missing
        'tier_price',  
        'size_breakdown',
    ];

    protected $casts = [
        'custom_images'    => 'array',
        'logo_images' => 'array',
        'selected_options' => 'array',
        'size_breakdown'   => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function tier()
    {
        return $this->belongsTo(ProductQuantity::class, 'product_quantity_id');
    }
}