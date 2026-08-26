<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">
<div class="container">

    <div class="crumbs" style="padding-top: var(--s5)">
        <a href="{{ url('/') }}">Home</a> <span class="sep">›</span>
        <span>Cart</span>
    </div>

    @if(session('error'))
        <div style="padding:var(--s4);border-radius:var(--r);background:#fdecea;color:#c0392b;margin-bottom:var(--s4)">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div style="padding:var(--s4);border-radius:var(--r);background:#e6f7ee;color:#1a7f4e;margin-bottom:var(--s4)">{{ session('success') }}</div>
    @endif

    <h1 style="font-size:var(--text-2xl);margin-bottom:var(--s5)">Your Cart</h1>

    @php $grandTotal = 0; @endphp

    @if(count($carts) > 0)

        <section class="section" style="padding-top:0">
            <div class="cart-layout">

                <div>
                    <div class="cart-list">

                        @foreach($carts as $item)
                            @php
                                $unitPrice = $item->tier_price ?? $item->product->price;
                                $total = $unitPrice * $item->quantity;
                                $grandTotal += $total;
                            @endphp

                            <article class="cart-row">

                                <div class="pic">
                                    @if(!empty($item->custom_images) && count($item->custom_images))
                                        <img src="{{ asset('uploads/customizations/' . $item->custom_images[0]) }}" alt="{{ $item->product->name ?? 'Product' }}">
                                    @elseif($item->custom_image)
                                        <img src="{{ asset('uploads/customizations/' . $item->custom_image) }}" alt="{{ $item->product->name ?? 'Product' }}">
                                    @else
                                        <img src="{{ asset('images/' . $item->product->image) }}" alt="{{ $item->product->name ?? 'Product' }}">
                                    @endif
                                </div>

                                <div class="info">
                                    <div class="name">{{ $item->product->name ?? 'Product' }}</div>

                                    <div class="variant">
                                        @if(!empty($item->custom_images) && count($item->custom_images) > 1)
                                            Customized ({{ count($item->custom_images) }} images) ·
                                        @elseif($item->custom_image || (!empty($item->custom_images) && count($item->custom_images)))
                                            Customized ·
                                        @endif

                                        @if($item->selected_options && count($item->selected_options))
                                            @foreach($item->selected_options as $optName => $optValue)
                                                {{ $optName }}: {{ $optValue }}@if(!$loop->last) · @endif
                                            @endforeach
                                        @endif

                                        @if($item->tier_price)
                                            <br><span style="font-family:var(--ff-mono);font-size:11px">₹{{ number_format($item->tier_price) }} per {{ $item->tier_qty }} pcs</span>
                                        @endif
                                    </div>

                                    @if(!empty($item->size_breakdown) && count($item->size_breakdown))
                                        <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap">
                                            @foreach($item->size_breakdown as $size => $qty)
                                                <span style="font-family:var(--ff-mono);font-size:10px;padding:2px 8px;background:var(--bg);border-radius:4px;border:1px solid var(--rule)">{{ $size }}: {{ $qty }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="qty">
                                    @if(!empty($item->size_breakdown) && count($item->size_breakdown))

                                        {{-- Cloth item: quantity is locked by size breakdown, no +/- --}}
                                        <span style="font-weight:700;padding:0 var(--s3)">
                                            {{ $item->quantity }}
                                            @if($item->tier_qty)
                                                <div style="font-family:var(--ff-mono);font-size:10px;color:var(--fg-mute);font-weight:400">{{ $item->quantity * $item->tier_qty }} pcs</div>
                                            @endif
                                        </span>

                                    @else

                                        <form action="{{ url('add_quantity') }}" method="POST" style="display:inline">
                                            @csrf
                                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                            <input type="hidden" name="action" value="minus">
                                            <button class="btn btn-danger btn-sm">-</button>
                                        </form>

                                        <input type="text" value="{{ $item->quantity }}" inputmode="numeric" aria-label="Quantity" readonly>

                                        <form action="{{ url('add_quantity') }}" method="POST" style="display:inline">
                                            @csrf
                                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                            <input type="hidden" name="action" value="add">
                                            <button type="submit" data-act="+" aria-label="Increase">+</button>
                                        </form>

                                        @if($item->tier_qty)
                                            <div style="font-family:var(--ff-mono);font-size:10px;color:var(--fg-mute);width:100%;text-align:center;margin-top:4px">{{ $item->quantity * $item->tier_qty }} pcs total</div>
                                        @endif

                                    @endif
                                </div>

                                <span class="subtotal">₹ {{ number_format($total) }}</span>

                                <form action="{{ url('add_quantity') }}" method="POST"
                                      onsubmit="return confirm('Remove this item from your cart?');">
                                    @csrf
                                    <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                                    <input type="hidden" name="action" value="minus_all">
                                    <button type="submit" class="remove" aria-label="Remove">✕</button>
                                </form>

                            </article>
                        @endforeach

                    </div>

                    <div style="margin-top: var(--s5); display: flex; gap: var(--s3); flex-wrap: wrap">
                        <a href="{{ url('products') }}" class="btn btn--ghost">← Continue shopping</a>
                    </div>

                    <!-- Trust strip -->
                    <div style="margin-top: var(--s7); display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--s4); padding: var(--s5); background: var(--bg); border-radius: var(--r)">
                        <div style="display:flex; align-items:center; gap:var(--s3)">
                            <div style="width:40px; height:40px; background:var(--indigo-soft); color:var(--indigo); border-radius:999px; display:grid; place-items:center; font-size:18px">⚡</div>
                            <div>
                                <div style="font-family:var(--ff-display); font-weight:700; font-size:var(--text-sm)">Free shipping over ₹999</div>
                                <div style="font-family:var(--ff-mono); font-size:11px; color:var(--fg-mute)">On all orders</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--s3)">
                            <div style="width:40px; height:40px; background:var(--indigo-soft); color:var(--indigo); border-radius:999px; display:grid; place-items:center; font-size:18px">↺</div>
                            <div>
                                <div style="font-family:var(--ff-display); font-weight:700; font-size:var(--text-sm)">Easy returns</div>
                                <div style="font-family:var(--ff-mono); font-size:11px; color:var(--fg-mute)">Hassle-free</div>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--s3)">
                            <div style="width:40px; height:40px; background:var(--indigo-soft); color:var(--indigo); border-radius:999px; display:grid; place-items:center; font-size:18px">✓</div>
                            <div>
                                <div style="font-family:var(--ff-display); font-weight:700; font-size:var(--text-sm)">Quality checked</div>
                                <div style="font-family:var(--ff-mono); font-size:11px; color:var(--fg-mute)">Every order</div>
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="cart-summary">
                    <h3>Order summary</h3>

                    <div class="cart-line">
                        <span>Subtotal · {{ count($carts) }} item(s)</span>
                        <span style="font-family:var(--ff-display); font-weight:600; color:var(--ink)">₹ {{ number_format($grandTotal) }}</span>
                    </div>
                    <div class="cart-line">
                        <span>Shipping</span>
                        <span style="color: var(--emerald); font-weight: 600">Calculated at checkout</span>
                    </div>

                    <div class="cart-line is-total">
                        <span>Total</span>
                        <span>₹ {{ number_format($grandTotal) }}</span>
                    </div>

                    @auth
                        <a href="{{ route('shipping.form') }}" class="btn btn--indigo btn--block">Proceed to checkout →</a>
                    @else
                        <a href="{{ url('/login?redirect=' . urlencode('/checkout/address')) }}" class="btn btn--indigo btn--block">Login to Checkout →</a>
                    @endauth

                    <p style="margin-top: var(--s5); font-size: 11px; font-family: var(--ff-mono); color: var(--fg-mute); text-align: center; line-height: 1.6">
                        Secure checkout. Your payment information is never stored on our servers.
                    </p>
                </aside>

            </div>
        </section>

    @else

        <div style="text-align:center;padding:var(--s8) 0;">
            <p style="font-size:var(--text-lg);color:var(--fg-mute)">Cart is empty 😢</p>
            <a href="{{ url('products') }}" class="btn btn--indigo" style="margin-top:var(--s4)">Browse Products →</a>
        </div>

    @endif

</div>
</main>

<x-footer></x-footer>
<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>