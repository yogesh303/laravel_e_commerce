<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\products;
use Illuminate\Http\Request;


class CartController extends Controller
{
    //
    public function add_cart(Request $request){
        $request = Cart::create([
            'user_id' => $request->user_id,
        ]);

        $request = CartItem::create([
            'cart_id' => $request->product_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

    }
}
