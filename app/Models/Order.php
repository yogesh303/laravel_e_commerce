<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    //
    protected $table = 'orders';
    
     protected $fillable = [
        'user_id',
        'total_price',
        'status'
    ];

    public function items(){
        return $this->hasMany(OrderItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public static function totalRevenue()
    {
        return self::sum('total_price');
    }
}
