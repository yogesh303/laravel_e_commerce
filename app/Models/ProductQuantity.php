<?php
// app/Models/ProductQuantity.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQuantity extends Model
{
    protected $table = 'product_quantity_prices';

    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'step',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }
}