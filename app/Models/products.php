<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class products extends Model
{
    //
    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'description',
        'stock',
        'image'
    ];
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
