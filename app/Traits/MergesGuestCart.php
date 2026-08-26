<?php

namespace App\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;

trait MergesGuestCart
{
    public function mergeGuestCartIntoUser(Request $request, $user): void
    {
        $sessionId = $request->session()->get('guest_cart_id');

        if (!$sessionId) {
            return;
        }

        $guestCart = Cart::where('session_id', $sessionId)->whereNull('user_id')->first();

        if (!$guestCart) {
            $request->session()->forget('guest_cart_id');
            return;
        }

        $userCart = Cart::where('user_id', $user->id)->first();

        if (!$userCart) {
            $guestCart->user_id = $user->id;
            $guestCart->session_id = null;
            $guestCart->save();
        } else {
            CartItem::where('cart_id', $guestCart->id)->update(['cart_id' => $userCart->id]);
            $guestCart->delete();
        }

        $request->session()->forget('guest_cart_id');
    }
}