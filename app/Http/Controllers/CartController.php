<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CartController extends Controller
{
    //
    public function add_cart(Request $request){
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingItem) {
         
            $existingItem->quantity += 1;
            $existingItem->save();
        } else {
        
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => 1,
                'price' => $request->price ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart');

    }
    public function cart_items()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return view('cart', ['carts' => []]); // ✅ no redirect
        }

        $cart_items = CartItem::where('cart_id', $cart->id)->get();

        return view('cart', ['carts' => $cart_items]); // ✅ even if empty
    }
    public function add_quantity(Request $request){
        $user = Auth::user();

         if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $existingItem = CartItem::where('product_id', $request->product_id)
            ->whereHas('cart', function ($q) {
                $q->where('user_id', Auth::id());
            })->first();
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
    public function checkout()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return redirect()->back()->with('error', 'Cart not found');
        }

        $items = CartItem::where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Cart is empty');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];

        foreach ($items as $item) {
            $product = Products::find($item->product_id);

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price * 100, // paise
                ],
                'quantity' => $item->quantity,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => url('/payment-success'),
            'cancel_url' => url('/cart'),
        ]);

        return redirect($session->url);
    }
    public function order(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return redirect()->back()->with('error', 'No cart found');
        }

        $items = CartItem::where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Cart is empty');
        }

        DB::beginTransaction();

        try {

            foreach ($items as $item) {

                $product = Products::find($item->product_id);

                if (!$product) {
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Product not found');
                }

                if ($product->stock < $item->quantity) {
                    DB::rollBack();
                    return redirect()->back()->with(
                        'error',
                        $product->name . ' is out of stock (Available: ' . $product->stock . ')'
                    );
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'placed',
                'total_price' => 0
            ]);

            $grandTotal = 0;

            foreach ($items as $item) {

                $product = Products::find($item->product_id);

                $total = $product->price * $item->quantity;
                $grandTotal += $total;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'price' => $product->price,
                ]);

                $product->stock -= $item->quantity;
                $product->save();
            }

            $order->total_price = $grandTotal;
            $order->save();

            CartItem::where('cart_id', $cart->id)->delete();
            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            $to = "yogeshkanzariya5@mail.com";
            $msg = "hello this is body";
            $subject = "Important Task";
            Mail::to($to)->queue(new TestMail($msg, $subject));

            return redirect('orders')->with('success', 'Order placed successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                dd($e->getMessage()); // 👈 ADD THIS
            }
    }
    public function payment_success()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // 👉 CALL YOUR EXISTING ORDER LOGIC HERE
        return $this->order(new Request());
    }
    public function order_list()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login');
        }

        if ($user->role === 'admin') {
            $orders = Order::with('items.product')->get();
        } 
        else {
            $orders = Order::with('items.product')
                ->where('user_id', $user->id)
                ->get();
        }

        return view('orders', ['orders' => $orders]);
    }

}
