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
use App\Mail\CustomizationMail;

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

    /*
    |--------------------------------------------------------------------------
    | Collect selected options (Size, Color, etc.) from the product page
    | Expected input: options[Size] = M, options[Color] = White
    |--------------------------------------------------------------------------
    */

    $selectedOptions = $request->filled('options') ? $request->options : null;

    /*
    |--------------------------------------------------------------------------
    | Only merge quantity into an existing row if it's the SAME product
    | with the SAME selected options and no custom image. A plain
        | "Add to Cart" click should never merge into a customized row.
        |--------------------------------------------------------------------------
        */

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->whereNull('custom_image')
            ->where('selected_options', json_encode($selectedOptions))
            ->first();

        if ($existingItem) {
            $existingItem->quantity += 1;
            $existingItem->save();
        } else {
            CartItem::create([
                'cart_id'          => $cart->id,
                'product_id'       => $request->product_id,
                'quantity'         => 1,
                'selected_options' => $selectedOptions,
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

    /*
    |--------------------------------------------------------------------------
    | Target the exact cart row (fixes wrong item being updated when
    | a product has multiple rows — e.g. front/back customizations)
    |--------------------------------------------------------------------------
    */

    $existingItem = CartItem::where('id', $request->cart_item_id)
        ->whereHas('cart', function ($q) {
            $q->where('user_id', Auth::id());
        })->first();

    if (!$existingItem) {
        return redirect()->back()->with('error', 'Cart item not found');
    }

    if ($request->action === 'add') {
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
                    'order_id'         => $order->id,
                    'product_id'       => $product->id,
                    'quantity'         => $item->quantity,
                    'price'            => $product->price,
                    'custom_image'     => $item->custom_image,
                    'custom_images'    => $item->custom_images,
                    'selected_options' => $item->selected_options,
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
    $product = Products::with(['images', 'options'])->findOrFail($id);

    $customizableImages = $product->images->where('is_customizable', true)->values();

    if ($customizableImages->isEmpty() && $product->image) {
        $customizableImages = collect([
            (object) [
                'id'    => 0,
                'image' => $product->image,
            ],
        ]);
    }

    if ($customizableImages->isEmpty()) {
        return redirect('/product/' . $id)
            ->with('error', 'This product has no customizable images.');
    }

    return view('customize', [
        'product'            => $product,
        'customizableImages' => $customizableImages,
    ]);
}

public function saveCustomization(Request $request, $id)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $product = Products::with('options')->findOrFail($id);

    $request->validate([
        'custom_images'   => 'required|array|min:1',
        'custom_images.*' => 'required|string',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Require every dynamic option to be selected, same as the product page
    |--------------------------------------------------------------------------
    */

    if ($product->options->count()) {
        $request->validate([
            'options' => 'required|array',
        ]);

        foreach ($product->options as $option) {
            $request->validate([
                'options.' . $option->name => 'required|string',
            ]);
        }
    }

    $cart = Cart::firstOrCreate(['user_id' => $user->id]);

    $folder = public_path('uploads/customizations');

    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
    }

    $savedFiles = [];

    foreach ($request->custom_images as $imageId => $imageData) {

        if (!preg_match('/^data:image\/(\w+);base64,/', $imageData, $matches)) {
            continue;
        }

        $data = substr($imageData, strpos($imageData, ',') + 1);
        $data = base64_decode($data);

        if ($data === false) {
            continue;
        }

        $extension = strtolower($matches[1]);

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        $filename = time() . '_' . uniqid() . '.' . $extension;

        file_put_contents($folder . '/' . $filename, $data);

        $savedFiles[] = $filename;
    }

    if (empty($savedFiles)) {
        return redirect()->back()
            ->with('error', 'Please customize at least one image before saving.');
    }

    $selectedOptions = $request->filled('options') ? $request->options : null;

    /*
    |--------------------------------------------------------------------------
    | ONE cart row holding every customized image (front, back, etc.)
    |--------------------------------------------------------------------------
    */

    CartItem::create([
        'cart_id'          => $cart->id,
        'product_id'       => $product->id,
        'quantity'         => 1,
        'custom_image'     => $savedFiles[0],
        'custom_images'    => $savedFiles,
        'selected_options' => $selectedOptions,
    ]);

    try {
        Mail::to($user->email)->send(new CustomizationMail($product, $savedFiles));
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Customization mail failed: ' . $e->getMessage());
    }

    return redirect('/cart')
        ->with('success', count($savedFiles) . ' customized image(s) added to cart!');
}
}

