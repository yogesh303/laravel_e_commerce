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
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart');

    }
    public function cart()
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
            return redirect('/login')->with('error', 'Please login');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return redirect('/cart')->with('error', 'Cart not found');
        }

        $items = CartItem::where('cart_id', $cart->id)->get();

        if ($items->isEmpty()) {
            return redirect('/cart')->with('error', 'Cart is empty');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];

        foreach ($items as $item) {

            $product = Products::find($item->product_id);

            if (!$product) {
                continue;
            }

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'inr',

                    'product_data' => [
                        'name' => $product->name,
                    ],

                    'unit_amount' => (int) ($product->price * 100),
                ],

                'quantity' => $item->quantity,
            ];
        }

        if (empty($lineItems)) {
            return redirect('/cart')
                ->with('error', 'No valid products found in cart');
        }

        $session = Session::create([

            'payment_method_types' => ['card'],

            'line_items' => $lineItems,

            'mode' => 'payment',

            'customer_email' => $user->email,

            'success_url' => url('/payment-success') . '?session_id={CHECKOUT_SESSION_ID}',

            'cancel_url' => url('/cart'),
        ]);

        return redirect($session->url);
    }
    public function order($session = null)
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
                    'custom_image' => $item->custom_image,
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

            Mail::to($to)->send(
                new TestMail($order)
            );

            return redirect('orders')->with('success', 'Order placed successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                dd($e->getMessage()); // 👈 ADD THIS
            }
    }
    public function payment_success(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        if (!$request->session_id) {
            return redirect('/cart')
                ->with('error', 'Invalid payment session.');
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {

            $session = Session::retrieve($request->session_id);

            if ($session->payment_status !== 'paid') {

                return redirect('/cart')
                    ->with('error', 'Payment was not completed.');
            }

            /*
            |--------------------------------------------------------------------------
            | Payment successful
            |--------------------------------------------------------------------------
            */

            return $this->order($session);

        } catch (\Exception $e) {

            return redirect('/cart')
                ->with('error', 'Unable to verify payment.');
        }
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
    public function order_view($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $order = Order::with([
            'user',
            'items.product'
        ])->findOrFail($id);

        // Normal user can only see their own order
        if ($user->role !== 'admin' && $order->user_id != $user->id) {
            abort(403);
        }

        return view('order_view', compact('order'));
    }
   public function customize($id)
    {
        $product = Products::findOrFail($id);

        return view('customize', compact('product'));
    }


    public function saveCustomization(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $product = Products::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Validate Customized Image
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'custom_image' => 'required|string',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get / Create Cart
        |--------------------------------------------------------------------------
        */

        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Base64 Image
        |--------------------------------------------------------------------------
        */

        $imageData = $request->custom_image;

        /*
        | Example:
        | data:image/png;base64,iVBORw0KGgo...
        */

        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {

            return redirect()->back()
                ->with('error', 'Invalid customized image.');

        }


        /*
        |--------------------------------------------------------------------------
        | Remove Base64 Header
        |--------------------------------------------------------------------------
        */

        $imageData = substr(
            $imageData,
            strpos($imageData, ',') + 1
        );


        /*
        |--------------------------------------------------------------------------
        | Decode Image
        |--------------------------------------------------------------------------
        */

        $imageData = base64_decode($imageData);

        if ($imageData === false) {

            return redirect()->back()
                ->with('error', 'Unable to process customized image.');

        }


        /*
        |--------------------------------------------------------------------------
        | Get Image Extension
        |--------------------------------------------------------------------------
        */

        $extension = strtolower($matches[1]);

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }


        /*
        |--------------------------------------------------------------------------
        | Create Upload Folder
        |--------------------------------------------------------------------------
        */

        $folder = public_path('uploads/customizations');

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }


        /*
        |--------------------------------------------------------------------------
        | Generate Filename
        |--------------------------------------------------------------------------
        */

        $filename = time() . '_' . uniqid() . '.' . $extension;


        /*
        |--------------------------------------------------------------------------
        | Save Customized Image
        |--------------------------------------------------------------------------
        */

        file_put_contents(
            $folder . '/' . $filename,
            $imageData
        );


        /*
        |--------------------------------------------------------------------------
        | Add Product To Cart
        |--------------------------------------------------------------------------
        */

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'custom_image' => $filename,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect To Cart
        |--------------------------------------------------------------------------
        */

        return redirect('/cart')
            ->with('success', 'Customized product added to cart!');
    }
}

