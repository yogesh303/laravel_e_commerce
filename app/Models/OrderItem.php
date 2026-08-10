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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}