<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CartController extends Controller
{
    //
    public function add_cart(Request $request){
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

         // Check if cart already exists for user
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);
        }

        // Check if product already in cart
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingItem) {
            // Increase quantity
            $existingItem->quantity += 1;
            $existingItem->save();
        } else {
            // Add new item
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => 1,
                'price' => $request->price ?? 0, // better fetch from DB
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart');

    }
    public function cart_items(){
        $user = Auth::user();

        $cart = Cart::where('user_id', $user->id)->first();

        $cart_items = CartItem::where('cart_id',$cart->id)->get();

        return view('cart',['carts'=>$cart_items]);

    }
    public function add_quantity(Request $request){
        $user = Auth::user();

         if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $existingItem = CartItem::where('product_id',$request->product_id)->first();
        if($request->action === 'add'){
        $existingItem->quantity += 1;
        } else {
            if ($existingItem->quantity > 1) {
                $existingItem->quantity -= 1;
            } else {
                $existingItem->delete();
                return redirect()->back();
            }
        }
        $existingItem->save();
        return redirect()->back();
    }

}
