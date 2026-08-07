<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\products;

class OrderItem extends Model
{
    //
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'custom_image'
    ];

    public function product(){
        return $this->belongsTo(Products::class);
    }
}
