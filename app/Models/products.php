<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class products extends Model
{
    //
    use SoftDeletes;
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
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class, 'product_id');
    }
}
