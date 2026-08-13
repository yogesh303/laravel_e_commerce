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
use Razorpay\Api\Api;

class CartController extends Controller
{
    //
    public function add_cart(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $user->id]);
        }

        $selectedOptions = $request->filled('options') ? $request->options : null;

        // Resolve selected quantity tier (if the product has any)
        $tier = null;
        if ($request->filled('quantity_id')) {
            $tier = \App\Models\ProductQuantity::where('id', $request->quantity_id)
                ->where('product_id', $request->product_id)
                ->first();
        }

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->whereNull('custom_image')
            ->where('selected_options', json_encode($selectedOptions))
            ->where('product_quantity_id', $tier->id ?? null)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += 1;
            $existingItem->save();
        } else {
            CartItem::create([
                'cart_id'             => $cart->id,
                'product_id'          => $request->product_id,
                'quantity'            => 1,
                'selected_options'    => $selectedOptions,
                'product_quantity_id' => $tier->id ?? null,
                'tier_qty'            => $tier->quantity ?? null,
                'tier_price'          => $tier->price ?? null,
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
    public function add_quantity(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $existingItem = CartItem::where('id', $request->cart_item_id)
            ->whereHas('cart', function ($q) {
                $q->where('user_id', Auth::id());
            })->first();

        if (!$existingItem) {
            return redirect()->back()->with('error', 'Cart item not found');
        }

        if ($request->action === 'add') {
            $existingItem->quantity += 1;
            $existingItem->save();
        } else {
            if ($existingItem->quantity > 1) {
                $existingItem->quantity -= 1;
                $existingItem->save();
            } else {
                $this->deleteCustomImages($existingItem);
                $existingItem->delete();
            }
        }

        return redirect()->back();
    }

    /**
     * Delete any uploaded customization image files tied to a cart item
     * before the row itself is removed.
     */
    private function deleteCustomImages(CartItem $item)
    {
        $folder = public_path('uploads/customizations');

        // Multi-image rows (front, back, etc.)
        if (!empty($item->custom_images) && is_array($item->custom_images)) {
            foreach ($item->custom_images as $img) {
                $path = $folder . '/' . $img;
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        // Legacy single-image rows
        if (!empty($item->custom_image)) {
            $path = $folder . '/' . $item->custom_image;
            if (file_exists($path)) {
                unlink($path);
            }
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

    public function customize($id)
    {
        $product = Products::with(['images', 'options', 'quantities'])->findOrFail($id);

        $customizableImages = $product->images->where('is_customizable', true)->values();

        if ($customizableImages->isEmpty() && $product->image) {
            $customizableImages = collect([
                (object) [
                    'id'             => 0,
                    'image'          => $product->image,
                    'trigger_values' => [], // untagged = always shown
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

        $product = Products::with(['options', 'quantities'])->findOrFail($id);

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

        /*
        |--------------------------------------------------------------------------
        | Require a quantity tier to be selected if this product has any
        |--------------------------------------------------------------------------
        */

        $tier = null;

        if ($product->quantities->count()) {
            $request->validate([
                'quantity_id' => 'required|exists:product_quantity_prices,id',
            ]);

            $tier = $product->quantities->firstWhere('id', (int) $request->quantity_id);

            if (!$tier) {
                return redirect()->back()
                    ->with('error', 'Selected quantity is not valid for this product.');
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
            'cart_id'             => $cart->id,
            'product_id'          => $product->id,
            'quantity'            => 1,
            'custom_image'        => $savedFiles[0],
            'custom_images'       => $savedFiles,
            'selected_options'    => $selectedOptions,
            'product_quantity_id' => $tier->id ?? null,
            'tier_qty'            => $tier->quantity ?? null,
            'tier_price'          => $tier->price ?? null,
        ]);

        try {
            Mail::to($user->email)->send(new CustomizationMail($product, $savedFiles));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Customization mail failed: ' . $e->getMessage());
        }

        return redirect('/cart')
            ->with('success', count($savedFiles) . ' customized image(s) added to cart!');
    }
public function shipping_form()
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $cart = Cart::where('user_id', $user->id)->first();

    if (!$cart || CartItem::where('cart_id', $cart->id)->count() === 0) {
        return redirect('/cart')->with('error', 'Cart is empty');
    }

    // Prefill with previously entered shipping info, if any, in this session
    $saved = session('shipping_address');

    return view('shipping', ['saved' => $saved]);
}

public function save_shipping(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $rules = [
        'shipping_name' => 'required|string|max:255',
        'shipping_phone' => 'required|string|max:20',
        'shipping_address_line1' => 'required|string|max:255',
        'shipping_address_line2' => 'nullable|string|max:255',
        'shipping_city' => 'required|string|max:100',
        'shipping_state' => 'required|string|max:100',
        'shipping_pincode' => 'required|string|max:10',
        'shipping_country' => 'required|string|max:100',
    ];

    // GST number only required (and only accepted) for business accounts
    if ($user->account_type === 'business') {
        $rules['shipping_company'] = 'required|string|max:20';
        $rules['shipping_gst_no'] = 'required|string|max:15';
    }

    $validated = $request->validate($rules);

    // Never persist a GST number for non-business accounts, even if injected
    if ($user->account_type !== 'business') {
        unset($validated['shipping_gst_no']);
    }

    session(['shipping_address' => $validated]);

    return redirect()->route('payment.choice');
}

public function checkout()
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login')->with('error', 'Please login');
    }

    // Shipping address must be filled first
    if (!session()->has('shipping_address')) {
        return redirect()->route('shipping.form');
    }

    $cart = Cart::where('user_id', $user->id)->first();

    if (!$cart) {
        return redirect('/cart')->with('error', 'Cart not found');
    }

    $items = CartItem::with('product')->where('cart_id', $cart->id)->get();

    if ($items->isEmpty()) {
        return redirect('/cart')->with('error', 'Cart is empty');
    }

    Stripe::setApiKey(env('STRIPE_SECRET'));

    $lineItems = [];

    foreach ($items as $item) {

        $product = $item->product;

        if (!$product) {
            continue;
        }

        // Use the tier price if this item was added with a quantity tier,
        // otherwise fall back to the plain product price.
        $unitPrice = $item->tier_price ?? $product->price;

        $productName = $product->name;
        if ($item->tier_qty) {
            $productName .= ' (' . $item->tier_qty . ' pcs / batch)';
        }

        $lineItems[] = [
            'price_data' => [
                'currency' => 'inr',
                'product_data' => [
                    'name' => $productName,
                ],
                'unit_amount' => (int) round($unitPrice * 100),
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

    $items = CartItem::with('product')->where('cart_id', $cart->id)->get();

    if ($items->isEmpty()) {
        return redirect()->back()->with('error', 'Cart is empty');
    }

    $shipping = session('shipping_address');

    if (!$shipping) {
        return redirect()->route('shipping.form')->with('error', 'Please add shipping details');
    }

    DB::beginTransaction();

    try {

        foreach ($items as $item) {
            if (!$item->product) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Product not found');
            }
        }

        // (stock availability check removed — stock is no longer tracked)

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'placed',
            'total_price' => 0,
            'shipping_name' => $shipping['shipping_name'],
            'shipping_phone' => $shipping['shipping_phone'],
            'shipping_address_line1' => $shipping['shipping_address_line1'],
            'shipping_address_line2' => $shipping['shipping_address_line2'] ?? null,
            'shipping_city' => $shipping['shipping_city'],
            'shipping_state' => $shipping['shipping_state'],
            'shipping_pincode' => $shipping['shipping_pincode'],
            'shipping_country' => $shipping['shipping_country'],
            'shipping_gst_no' => $shipping['shipping_gst_no'] ?? null,
            'shipping_company' => $shipping['shipping_company'] ?? null,
        ]);

        $grandTotal = 0;

        foreach ($items as $item) {

            $product = $item->product;

            // Use the tier price (per batch) if this item has one,
            // otherwise fall back to the plain product price.
            $unitPrice = $item->tier_price ?? $product->price;
            $total = $unitPrice * $item->quantity;
            $grandTotal += $total;

            OrderItem::create([
                'order_id'             => $order->id,
                'product_id'           => $product->id,
                'quantity'             => $item->quantity,
                'price'                => $unitPrice,
                'custom_image'         => $item->custom_image,
                'custom_images'        => $item->custom_images,
                'selected_options'     => $item->selected_options,
                'product_quantity_id'  => $item->product_quantity_id,
                'tier_qty'             => $item->tier_qty,
                'tier_price'           => $item->tier_price,
            ]);

            // (stock decrement removed — stock is no longer tracked)
        }

        $order->total_price = $grandTotal;
        $order->save();

        CartItem::where('cart_id', $cart->id)->delete();
        Cart::where('user_id', $user->id)->delete();

        DB::commit();

        session()->forget('shipping_address');

        $to = "yogeshkanzariya5@mail.com";

        Mail::to($to)->send(
            new TestMail($order)
        );

        return redirect('orders')->with('success', 'Order placed successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        dd($e->getMessage());
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
public function checkout_razorpay()
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login')->with('error', 'Please login');
    }

    if (!session()->has('shipping_address')) {
        return redirect()->route('shipping.form');
    }

    $cart = Cart::where('user_id', $user->id)->first();

    if (!$cart) {
        return redirect('/cart')->with('error', 'Cart not found');
    }

    $items = CartItem::with('product')->where('cart_id', $cart->id)->get();

    if ($items->isEmpty()) {
        return redirect('/cart')->with('error', 'Cart is empty');
    }

    $grandTotal = 0;

    foreach ($items as $item) {
        $product = $item->product;
        if ($product) {
            $unitPrice = $item->tier_price ?? $product->price;
            $grandTotal += $unitPrice * $item->quantity;
        }
    }

    if ($grandTotal <= 0) {
        return redirect('/cart')->with('error', 'No valid products found in cart');
    }

    $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

    // Razorpay amount is in paise (smallest currency unit)
    $razorpayOrder = $api->order->create([
        'receipt'         => 'order_rcpt_' . time(),
        'amount'          => (int) round($grandTotal * 100),
        'currency'        => 'INR',
        'payment_capture' => 1,
    ]);

    return view('razorpay_checkout', [
        'razorpay_order_id' => $razorpayOrder['id'],
        'amount'            => $grandTotal,
        'key'               => env('RAZORPAY_KEY'),
        'user'              => $user,
    ]);
}

public function razorpay_verify(Request $request)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $api = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));

    $attributes = [
        'razorpay_order_id'   => $request->razorpay_order_id,
        'razorpay_payment_id' => $request->razorpay_payment_id,
        'razorpay_signature'  => $request->razorpay_signature,
    ];

    try {
        // Throws an exception if the signature doesn't match
        $api->utility->verifyPaymentSignature($attributes);

    } catch (\Exception $e) {
        return redirect('/cart')->with('error', 'Payment verification failed.');
    }

    // Signature verified — payment is genuine, place the order
    return $this->order();
}
public function payment_choice()
{
    if (!session()->has('shipping_address')) {
        return redirect()->route('shipping.form');
    }

    return view('payment_choice');
}

public function invoice($id)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $order = Order::with(['user', 'items.product'])->findOrFail($id);

    if ($user->role !== 'admin' && $order->user_id != $user->id) {
        abort(403);
    }

    $gstRate = 18; // %

    // Per-item GST breakup (item price is assumed GST-inclusive)
    $items = $order->items->map(function ($item) use ($gstRate) {
        $lineTotal   = $item->price * $item->quantity;
        $taxable     = $lineTotal / (1 + $gstRate / 100);
        $gstAmount   = $lineTotal - $taxable;

        return (object) [
            'name'        => $item->product->name ?? 'Product Deleted',
            'quantity'    => $item->quantity,
            'price'       => $item->price,
            'line_total'  => $lineTotal,
            'taxable'     => $taxable,
            'gst_amount'  => $gstAmount,
            'cgst'        => $gstAmount / 2,
            'sgst'        => $gstAmount / 2,
        ];
    });

    $grandTotal   = $order->total_price;
    $taxableTotal = $grandTotal / (1 + $gstRate / 100);
    $gstTotal     = $grandTotal - $taxableTotal;
    $cgstTotal    = $gstTotal / 2;
    $sgstTotal    = $gstTotal / 2;

    return view('invoice', [
        'order'        => $order,
        'items'        => $items,
        'gstRate'      => $gstRate,
        'taxableTotal' => $taxableTotal,
        'gstTotal'     => $gstTotal,
        'cgstTotal'    => $cgstTotal,
        'sgstTotal'    => $sgstTotal,
        'grandTotal'   => $grandTotal,
    ]);
}
}