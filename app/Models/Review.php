<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'order_id',
        'rating',
        'review',
        'is_verified_purchase',
    ];

    protected $casts = [
        'rating'                => 'integer',
        'is_verified_purchase'  => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(products::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(\App\Models\Order::class, 'order_id');
    }

    /**
     * Returns the id of an order this user placed that contains this product,
     * or null if they never bought it. Used both to gate review creation and
     * to stamp order_id on the review.
     *
     * Adjust the `orders.status` filter below to match whatever column/value
     * your app uses to mean "completed" (e.g. 'delivered', 'paid', 1, etc.).
     * If `orders` has no status column at all, drop that ->where() line.
     */
    public static function purchasedOrderId(int $userId, int $productId): ?int
    {
        $order = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.user_id', $userId)
            ->where('order_items.product_id', $productId)
            // ->where('orders.status', 'completed') // <-- uncomment/adjust if you gate on order status
            ->orderBy('orders.id', 'asc')
            ->value('orders.id');

        return $order ?: null;
    }

    public static function userHasPurchased(int $userId, int $productId): bool
    {
        return self::purchasedOrderId($userId, $productId) !== null;
    }

    public static function userAlreadyReviewed(int $userId, int $productId): bool
    {
        return self::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
