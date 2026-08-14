<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\OrderItem;

class Order extends Model
{
    //
    protected $table = 'orders';
    
    protected $fillable = [
        'user_id', 'status', 'total_price',
        'shipping_name', 'shipping_phone', 'shipping_company',
        'shipping_address_line1', 'shipping_address_line2',
        'shipping_city', 'shipping_state', 'shipping_pincode', 'shipping_country', 'shipping_gst_no',
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
