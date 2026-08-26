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
use Razorpay\Api\Api;
use App\Traits\MergesGuestCart;

class CartController extends Controller
{
    use MergesGuestCart;
    //
    /**
     * Get the current cart — for a logged-in user, their user cart (merging in
     * any guest cart from this browser session). For a guest, a cart tied to
     * a persistent session id stored in the Laravel session.
     */
    private function getOrCreateCart(Request $request): Cart
    {
        $user = Auth::user();

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();

            if (!$cart) {
                $cart = Cart::create(['user_id' => $user->id]);
            }

            $this->mergeGuestCartIntoUser($request, $user);

            return Cart::where('user_id', $user->id)->first(); // re-fetch in case merge reassigned the cart
        }

        if (!$request->session()->has('guest_cart_id')) {
            $request->session()->put('guest_cart_id', (string) \Illuminate\Support\Str::uuid());
        }

        $sessionId = $request->session()->get('guest_cart_id');

        return Cart::firstOrCreate(['session_id' => $sessionId, 'user_id' => null]);
    }

    /**
     * If the person had items in a guest cart before logging in, move those
     * items onto their real (user_id) cart, then forget the guest cart.
     */

    public function add_cart(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $request->validate([
            'remarks'             => 'nullable|string|max:2000',
            'additional_files.*'  => 'nullable|file',
        ]);

        // Capture remarks/files once, up front — reused in every CartItem::create below
        $remarks = $request->input('remarks');
        $additionalFiles = [];

        if ($request->hasFile('additional_files')) {
            $folder = public_path('uploads/attachments');

            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            foreach ($request->file('additional_files') as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($folder, $filename);
                $additionalFiles[] = $filename;
            }
        }

        $selectedOptions = $request->filled('options') ? $request->options : null;

        $product = \App\Models\products::with('options')->find($request->product_id);

        $tier = null;
        if ($request->filled('quantity_id')) {
            $tier = \App\Models\ProductQuantity::where('id', $request->quantity_id)
                ->where('product_id', $request->product_id)
                ->first();
        }

        $optionExtraPerPiece = 0;

        if ($selectedOptions && $product) {
            foreach ($selectedOptions as $optName => $optValue) {
                $productOption = $product->options->firstWhere('name', $optName);

                if ($productOption && $productOption->value_prices && isset($productOption->value_prices[$optValue])) {
                    $optionExtraPerPiece += (float) $productOption->value_prices[$optValue];
                }
            }
        }

        $baseUnitPrice = $tier->price ?? ($product->price ?? 0);
        $piecesInTier  = $tier->quantity ?? 1;

        if ($tier) {
            $addedUnits = max(0, (int) $request->input('quantity', 0));
            $effectiveStep = ((int) ($tier->step ?? 0)) > 0 ? (int) $tier->step : $piecesInTier;
            $totalPcs = $piecesInTier + ($addedUnits * $effectiveStep);

            $perPieceRate = $piecesInTier > 0
                ? (($baseUnitPrice + ($optionExtraPerPiece * $piecesInTier)) / $piecesInTier)
                : $baseUnitPrice;

            $unitPrice = $perPieceRate * $totalPcs;
        } else {
            $qty = max(1, (int) $request->input('quantity', 1));
            $totalPcs = $qty;
            $unitPrice = $baseUnitPrice + ($optionExtraPerPiece * $piecesInTier);
        }

        $sizeBreakdown = null;

        if ($product && $product->is_cloth) {
            $request->validate([
                'sizes' => 'required|array',
            ]);

            $sizes = collect($request->sizes)->map(fn ($v) => (int) $v);
            $totalSizes = $sizes->sum();

            if ($tier) {
                $requiredQty = (int) $tier->quantity;

                if ($totalSizes !== $requiredQty) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Quantity not match. Size quantities must add up to ' . $requiredQty . '.');
                }
            } else {
                if ($totalSizes < 1) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please enter at least 1 in one of the sizes.');
                }
            }

            $sizeBreakdown = $sizes->filter(fn ($v) => $v > 0)->toArray();
        }

        if ($sizeBreakdown) {
            CartItem::create([
                'cart_id'             => $cart->id,
                'product_id'          => $request->product_id,
                'quantity'            => 1,
                'selected_options'    => $selectedOptions,
                'product_quantity_id' => $tier->id ?? null,
                'tier_qty'            => $tier ? $totalPcs : ($tier->quantity ?? null),
                'tier_price'          => $unitPrice,
                'size_breakdown'      => $sizeBreakdown,
                'remarks'             => $remarks,
                'additional_file'     => $additionalFiles[0] ?? null,
                'additional_files'    => $additionalFiles ?: null,
            ]);

            return redirect('/cart')->with('success', 'Product added to cart');
        }

        if ($tier) {
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->whereNull('custom_image')
                ->where('selected_options', json_encode($selectedOptions))
                ->where('product_quantity_id', $tier->id)
                ->where('tier_qty', $totalPcs)
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
                    'product_quantity_id' => $tier->id,
                    'tier_qty'            => $totalPcs,
                    'tier_price'          => $unitPrice,
                    'remarks'             => $remarks,
                    'additional_file'     => $additionalFiles[0] ?? null,
                    'additional_files'    => $additionalFiles ?: null,
                ]);
            }
        } else {
            $qty = $totalPcs;

            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->whereNull('custom_image')
                ->where('selected_options', json_encode($selectedOptions))
                ->where('product_quantity_id', null)
                ->first();

            if ($existingItem) {
                $existingItem->quantity += $qty;
                $existingItem->save();
            } else {
                CartItem::create([
                    'cart_id'             => $cart->id,
                    'product_id'          => $request->product_id,
                    'quantity'            => $qty,
                    'selected_options'    => $selectedOptions,
                    'product_quantity_id' => null,
                    'tier_qty'            => null,
                    'tier_price'          => $unitPrice,
                    'remarks'             => $remarks,
                    'additional_file'     => $additionalFiles[0] ?? null,
                    'additional_files'    => $additionalFiles ?: null,
                ]);
            }
        }

        return redirect('/cart')->with('success', 'Product added to cart');
    }

    public function cart_remarks_form(Request $request, $id)
    {
        // No login gate — guests can reach this page too
        $product = \App\Models\products::with(['options', 'quantities'])->findOrFail($id);

        $pending = session('pending_customization');
        $isCustomization = $pending && (int) $pending['product_id'] === (int) $id;

        $prefill = [
            'quantity_id' => old('quantity_id', $request->query('quantity_id')),
            'quantity'    => old('quantity', $request->query('quantity', 0)),
            'options'     => old('options', $request->query('options', [])),
            'sizes'       => old('sizes', $request->query('sizes', [])),
        ];

        return view('cart_remarks', [
            'product'         => $product,
            'prefill'         => $prefill,
            'isCustomization' => $isCustomization,
        ]);
    }

    public function add_quantity(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $existingItem = CartItem::where('id', $request->cart_item_id)
            ->where('cart_id', $cart->id)
            ->with('tier') // ProductQuantity relation, needed for step + base quantity
            ->first();

        if (!$existingItem) {
            return redirect()->back()->with('error', 'Cart item not found');
        }

        $tier = $existingItem->tier;

        // ---- Stepper/tier items: move PIECES by the tier's custom step, not by
        //      duplicating the whole bundle. Falls back to the tier's own quantity
        //      when no custom step is set (reproduces the old behavior exactly). ----
        if ($tier && $existingItem->tier_qty) {
            $effectiveStep = ((int) ($tier->step ?? 0)) > 0 ? (int) $tier->step : (int) $tier->quantity;

            // Per-piece rate derived from THIS line's own stored total, so any option
            // surcharges baked in when it was added to cart stay proportionally correct.
            $perPieceRate = $existingItem->tier_qty > 0
                ? ($existingItem->tier_price / $existingItem->tier_qty)
                : 0;

            if ($request->action === 'add') {
                $newTotalPcs = $existingItem->tier_qty + $effectiveStep;

                $existingItem->tier_qty   = $newTotalPcs;
                $existingItem->tier_price = $perPieceRate * $newTotalPcs;
                $existingItem->save();
            } else {
                $newTotalPcs = $existingItem->tier_qty - $effectiveStep;

                // Don't drop below the tier's own base quantity — if the next step
                // would go under it, remove the row entirely (same as old floor behavior).
                if ($newTotalPcs >= $tier->quantity) {
                    $existingItem->tier_qty   = $newTotalPcs;
                    $existingItem->tier_price = $perPieceRate * $newTotalPcs;
                    $existingItem->save();
                } else {
                    $this->deleteCustomImages($existingItem);
                    $existingItem->delete();
                }
            }

            return redirect()->back();
        }

        // ---- Non-tier items: unchanged, old behavior (bundle count) ----
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

    public function cart(Request $request)
    {
        $cart = $this->getOrCreateCart($request);

        $cart_items = CartItem::where('cart_id', $cart->id)->get();

        return view('cart', ['carts' => $cart_items]);
    }

    public function update_remarks(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->back()->with('error', 'Please login first');
        }

        $request->validate([
            'cart_item_id' => 'required|exists:cart_items,id',
            'remarks'       => 'nullable|string|max:2000',
            'additional_file' => 'nullable|file|max:10240', // 10MB
        ]);

        $item = CartItem::where('id', $request->cart_item_id)
            ->whereHas('cart', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Cart item not found');
        }

        $item->remarks = $request->remarks;

        if ($request->hasFile('additional_file')) {

            $folder = public_path('uploads/attachments');

            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            // remove old file if replacing
            if ($item->additional_file && file_exists($folder . '/' . $item->additional_file)) {
                unlink($folder . '/' . $item->additional_file);
            }

            $file = $request->file('additional_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($folder, $filename);

            $item->additional_file = $filename;
        }

        $item->save();

        return redirect()->back()->with('success', 'Remarks / file saved');
    }
    /**
     * Delete any uploaded customization image files tied to a cart item
     * before the row itself is removed.
     */
    private function deleteCustomImages(CartItem $item)
    {
        $folder     = public_path('uploads/customizations');
        $logoFolder = public_path('uploads/logos');
        $attachFolder = public_path('uploads/attachments');

        if (!empty($item->custom_images) && is_array($item->custom_images)) {
            foreach ($item->custom_images as $img) {
                $path = $folder . '/' . $img;
                if (file_exists($path)) unlink($path);
            }
        }

        // NEW — loop the multi-file list
        if (!empty($item->additional_files) && is_array($item->additional_files)) {
            foreach ($item->additional_files as $f) {
                $path = $attachFolder . '/' . $f;
                if (file_exists($path)) unlink($path);
            }
        } elseif (!empty($item->additional_file)) {
            // legacy single-file rows
            $path = $attachFolder . '/' . $item->additional_file;
            if (file_exists($path)) unlink($path);
        }

        if (!empty($item->custom_image)) {
            $path = $folder . '/' . $item->custom_image;
            if (file_exists($path)) unlink($path);
        }

        if (!empty($item->logo_images) && is_array($item->logo_images)) {
            foreach ($item->logo_images as $logo) {
                if (!$logo) continue;
                $path = $logoFolder . '/' . $logo;
                if (file_exists($path)) unlink($path);
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

        Stripe::setApiKey(config('services.stripe.secret'));

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

    public function customize(Request $request, $id)
    {
        $product = Products::with(['images', 'options', 'quantities'])->findOrFail($id);

        $customizableImages = $product->images->where('is_customizable', true)->values();

        if ($customizableImages->isEmpty() && $product->image) {
            $customizableImages = collect([
                (object) [
                    'id'             => 0,
                    'image'          => $product->image,
                    'trigger_values' => [],
                ],
            ]);
        }

        if ($customizableImages->isEmpty()) {
            return redirect('/product/' . $id)
                ->with('error', 'This product has no customizable images.');
        }

        // Prefer validation-failure old() input, then fall back to query params
        // carried over from the product detail page
        $prefill = [
            'quantity_id' => old('quantity_id', $request->query('quantity_id')),
            'options'     => old('options', $request->query('options', [])),
            'sizes'       => old('sizes', $request->query('sizes', [])),
        ];

        return view('customize', [
            'product'            => $product,
            'customizableImages' => $customizableImages,
            'prefill'            => $prefill,
        ]);
    }

    public function saveCustomization(Request $request, $id)
    {
        // No login gate — guests can customize too, same as normal add-to-cart
        $product = Products::with(['options', 'quantities'])->findOrFail($id);

        $request->validate([
            'custom_images'   => 'required|array|min:1',
            'custom_images.*' => 'required|string',
        ]);

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

        $sizeBreakdown = null;

        if ($product->is_cloth) {
            $request->validate([
                'sizes' => 'required|array',
            ]);

            $sizes = collect($request->sizes)->map(fn ($v) => (int) $v);
            $totalSizes = $sizes->sum();

            if ($tier) {
                $requiredQty = (int) $tier->quantity;

                if ($totalSizes !== $requiredQty) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Quantity not match. Size quantities must add up to ' . $requiredQty . '.');
                }
            } else {
                if ($totalSizes < 1) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Please enter at least 1 in one of the sizes.');
                }
            }

            $sizeBreakdown = $sizes->filter(fn ($v) => $v > 0)->toArray();
        }

        $folder     = public_path('uploads/customizations');
        $logoFolder = public_path('uploads/logos');

        if (!file_exists($folder)) {
            mkdir($folder, 0755, true);
        }

        if (!file_exists($logoFolder)) {
            mkdir($logoFolder, 0755, true);
        }

        $savedFiles     = [];
        $savedLogoFiles = [];

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

            $logoFilename = null;

            if ($request->filled("logo_images.$imageId")) {

                $logoData = $request->input("logo_images.$imageId");

                if (preg_match('/^data:image\/(\w+);base64,/', $logoData, $logoMatches)) {

                    $rawLogo = substr($logoData, strpos($logoData, ',') + 1);
                    $rawLogo = base64_decode($rawLogo);

                    if ($rawLogo !== false) {

                        $logoExt = strtolower($logoMatches[1]);

                        if ($logoExt === 'jpeg') {
                            $logoExt = 'jpg';
                        }

                        $logoFilename = 'logo_' . time() . '_' . uniqid() . '.' . $logoExt;

                        file_put_contents($logoFolder . '/' . $logoFilename, $rawLogo);
                    }
                }
            }

            $savedLogoFiles[] = $logoFilename;
        }

        if (empty($savedFiles)) {
            return redirect()->back()
                ->with('error', 'Please customize at least one image before saving.');
        }

        $selectedOptions = $request->filled('options') ? $request->options : null;

        /*
        |--------------------------------------------------------------------------
        | Images are saved to disk now. Stash the reference in session (works for
        | guests too — Laravel session persists via cookie regardless of login)
        | and send the user to the remarks/file page — the CartItem is created
        | there, same as a normal product.
        |--------------------------------------------------------------------------
        */

        session(['pending_customization' => [
            'product_id'           => $product->id,
            'custom_images'        => $savedFiles,
            'logo_images'          => $savedLogoFiles,
            'selected_options'     => $selectedOptions,
            'product_quantity_id'  => $tier->id ?? null,
            'tier_qty'             => $tier->quantity ?? null,
            'tier_price'           => $tier->price ?? null,
            'size_breakdown'       => $sizeBreakdown,
        ]]);

        return redirect()->route('cart.remarks.form', $product->id);
    }
    public function finalize_customization(Request $request, $id)
    {
        $pending = session('pending_customization');

        if (!$pending || (int) $pending['product_id'] !== (int) $id) {
            return redirect()->route('product.customize', $id)
                ->with('error', 'Your customization session expired. Please customize again.');
        }

        $request->validate([
            'remarks'             => 'nullable|string|max:2000',
            'additional_files.*'  => 'nullable|file',
        ]);

        $remarks = $request->input('remarks');
        $additionalFiles = [];

        if ($request->hasFile('additional_files')) {
            $folder = public_path('uploads/attachments');

            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            foreach ($request->file('additional_files') as $file) {
                if (!$file->isValid()) {
                    continue;
                }
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($folder, $filename);
                $additionalFiles[] = $filename;
            }
        }

        // Guest-aware — same cart resolution as add_cart / add_quantity
        $cart = $this->getOrCreateCart($request);

        CartItem::create([
            'cart_id'             => $cart->id,
            'product_id'          => $pending['product_id'],
            'quantity'            => 1,
            'custom_image'        => $pending['custom_images'][0] ?? null,
            'custom_images'       => $pending['custom_images'],
            'logo_images'         => $pending['logo_images'],
            'selected_options'    => $pending['selected_options'],
            'product_quantity_id' => $pending['product_quantity_id'],
            'tier_qty'            => $pending['tier_qty'],
            'tier_price'          => $pending['tier_price'],
            'size_breakdown'      => $pending['size_breakdown'],
            'remarks'             => $remarks,
            'additional_file'     => $additionalFiles[0] ?? null,
            'additional_files'    => $additionalFiles ?: null,
        ]);

        session()->forget('pending_customization');

        return redirect('/cart')->with('success', 'Customized product added to cart!');
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

    Stripe::setApiKey(config('services.stripe.secret'));

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

            $unitPrice = $item->tier_price ?? $product->price;
            $total = $unitPrice * $item->quantity;
            $grandTotal += $total;

            // Total pieces for this line (e.g. tier_qty 10 * quantity 1 = 10)
            $orderQuantity = $item->tier_qty
                ? $item->tier_qty * $item->quantity
                : $item->quantity;

            OrderItem::create([
                'order_id'             => $order->id,
                'product_id'           => $product->id,
                'quantity'             => $item->quantity,
                'order_quantity'       => $orderQuantity,          // e.g. 10
                'price'                => $unitPrice,
                'custom_image'         => $item->custom_image,
                'custom_images'        => $item->custom_images,
                'logo_images'          => $item->logo_images,   // ADD THIS
                'selected_options'     => $item->selected_options,
                'product_quantity_id'  => $item->product_quantity_id,
                'tier_qty'             => $item->tier_qty,
                'tier_price'           => $item->tier_price,
                'size_breakdown'       => $item->size_breakdown,
                'remarks'              => $item->remarks,          // NEW
                'additional_file'  => $item->additional_file,
                'additional_files' => $item->additional_files,   // NEW
            ]);
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
public function delete_order_item_files($id)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $item = OrderItem::with('order')->findOrFail($id);

    if ($user->role !== 'admin' && $item->order->user_id != $user->id) {
        abort(403);
    }

    $customFolder = public_path('uploads/customizations');
    $logoFolder   = public_path('uploads/logos');
    $attachFolder = public_path('uploads/attachments');

    if (!empty($item->custom_images) && is_array($item->custom_images)) {
        foreach ($item->custom_images as $img) {
            $path = $customFolder . '/' . $img;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    if (!empty($item->custom_image)) {
        $path = $customFolder . '/' . $item->custom_image;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    if (!empty($item->logo_images) && is_array($item->logo_images)) {
        foreach ($item->logo_images as $logo) {
            if (!$logo) continue;
            $path = $logoFolder . '/' . $logo;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    // NEW — delete every file in the multi-file list
    if (!empty($item->additional_files) && is_array($item->additional_files)) {
        foreach ($item->additional_files as $file) {
            $path = $attachFolder . '/' . $file;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    } elseif (!empty($item->additional_file)) {
        // legacy single-file rows
        $path = $attachFolder . '/' . $item->additional_file;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $item->update([
        'custom_image'     => null,
        'custom_images'    => null,
        'logo_images'      => null,
        'additional_file'  => null,
        'additional_files' => null, // NEW
    ]);

    return redirect()->back()->with('success', 'All files deleted for this item');
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

    $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

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

    $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));

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

    $items = $order->items->map(function ($item) use ($gstRate) {
        $lineTotal = $item->price * $item->quantity;
        $taxable   = $lineTotal / (1 + $gstRate / 100);
        $gstAmount = $lineTotal - $taxable;

        return (object) [
            'name'       => $item->product->name ?? 'Product Deleted',
            'quantity'   => $item->quantity,
            'price'      => $item->price,
            'line_total' => $lineTotal,
            'taxable'    => $taxable,
            'gst_amount' => $gstAmount,
            'cgst'       => $gstAmount / 2,
            'sgst'       => $gstAmount / 2,
        ];
    });

    $grandTotal   = $order->total_price;
    $taxableTotal = $grandTotal / (1 + $gstRate / 100);
    $gstTotal     = $grandTotal - $taxableTotal;
    $cgstTotal    = $gstTotal / 2;
    $sgstTotal    = $gstTotal / 2;

    // Use the manually-set invoice number if there is one, otherwise auto-generate
    $invoiceNo = $order->invoice_no ?: ('INV-' . str_pad($order->id, 6, '0', STR_PAD_LEFT));

    return view('invoice', [
        'order'        => $order,
        'items'        => $items,
        'gstRate'      => $gstRate,
        'taxableTotal' => $taxableTotal,
        'gstTotal'     => $gstTotal,
        'cgstTotal'    => $cgstTotal,
        'sgstTotal'    => $sgstTotal,
        'grandTotal'   => $grandTotal,
        'invoiceNo'    => $invoiceNo,
    ]);
}
public function set_invoice_number(Request $request, $id)
{
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    $order = Order::findOrFail($id);

    if ($user->role !== 'admin' && $order->user_id != $user->id) {
        abort(403);
    }

    $request->validate([
        'invoice_no' => 'required|string|max:50',
    ]);

    $order->invoice_no = $request->invoice_no;
    $order->save();

    return redirect()->back()->with('success', 'Invoice number saved');
}
}