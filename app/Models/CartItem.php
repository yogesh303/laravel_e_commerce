<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\products;

class CartItem extends Model
{
    //
    protected $table = 'cart_items';

    protected $fillable = ['cart_id', 'product_id', 'quantity'];

    public function product()
    {
        return $this->belongsTo(products::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
