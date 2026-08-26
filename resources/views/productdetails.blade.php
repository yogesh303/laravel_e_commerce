<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $product->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>
        .option-row {
            display: grid;
            grid-template-columns: minmax(120px, 180px) minmax(0, 1fr);
            align-items: center;
            gap: 15px;
        }

        .option-row .label {
            margin: 0;
            font-weight: 600;
            line-height: 1.3;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .option-row select {
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
        }
        .desc ul{
            padding-left: 2rem;
            list-style-type: disc;
        }

        @media (max-width: 600px) {
            .option-row {
                grid-template-columns: minmax(90px, 120px) minmax(0, 1fr);
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<x-layout></x-layout>

<main id="main">
<div class="container">

    @if(session('success'))
        <div class="alert alert-success" style="padding:var(--s4);border-radius:var(--r);background:#e6f7ee;color:#1a7f4e;margin-top:var(--s5)">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="padding:var(--s4);border-radius:var(--r);background:#fdecea;color:#c0392b;margin-top:var(--s5)">{{ session('error') }}</div>
    @endif

    @php
        $hasCustomizable = $product->images->where('is_customizable', true)->count() > 0;
    @endphp

    <div class="crumbs" style="padding-top: var(--s5)">
        <a href="{{ url('/') }}">Home</a> <span class="sep">›</span>
        <a href="{{ url('products') }}">Products</a> <span class="sep">›</span>
        <span>{{ $product->name }}</span>
    </div>

    <section class="product-detail">

        <!-- Gallery -->
        <div class="gallery">
            <div class="gallery-thumbs">
                <button class="is-active" onclick="document.getElementById('mainImage').src=this.querySelector('img').src; document.querySelectorAll('.gallery-thumbs button').forEach(b=>b.classList.remove('is-active')); this.classList.add('is-active');">
                    <img src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}">
                </button>

                @foreach($product->images as $img)
                    <button onclick="document.getElementById('mainImage').src=this.querySelector('img').src; document.querySelectorAll('.gallery-thumbs button').forEach(b=>b.classList.remove('is-active')); this.classList.add('is-active');">
                        <img src="{{ asset('images/' . $img->image) }}" alt="{{ $product->name }}">
                    </button>
                @endforeach
            </div>

            <figure class="gallery-main">
                <img id="mainImage" src="{{ asset('images/' . $product->image) }}" alt="{{ $product->name }}">
            </figure>
        </div>

        <!-- Details -->
        <div class="pdp-info">

            <h1>{{ $product->name }}</h1>

            <div class="price-row">
                <span class="now" id="displayPrice">₹ {{ number_format($product->quantities->first()->price ?? $product->price) }}</span>
            </div>

            <div class="desc">{!! $product->description !!}</div>

            @if($hasCustomizable)

                {{-- This product requires customization before it can be added to cart --}}
                <div style="background:var(--bg);border-radius:var(--r);padding:var(--s4);font-size:var(--text-sm);margin:var(--s5) 0;">
                    This product must be customized before adding to cart.
                </div>

                <div class="pdp-cta">
                    <a href="{{ route('product.customize', $product->id) }}" class="btn btn--indigo" style="flex:1;justify-content:center" id="customizeLink">
                        🎨 Customize This Product
                    </a>
                </div>

                <p style="font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute);margin-top:var(--s3)">
                    {{ $product->images->where('is_customizable', true)->count() }} image(s) available to customize
                </p>

                {{-- Hidden helper form — not shown to the user, only used to capture
                     quantity/sizes/options so we can carry them to the customize page --}}
                <form id="addToCartForm" class="d-none" style="display:none">
                    @if($product->options->count())
                        @foreach($product->options as $option)
                            <select name="options[{{ $option->name }}]">
                                @foreach($option->values_array as $val)
                                    <option value="{{ $val }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        @endforeach
                    @endif

                    @if(!$product->use_stepper && $product->is_cloth)
                        @php $sizes = ['S', 'M', 'L', 'XL', 'XXL']; @endphp
                        @foreach($sizes as $size)
                            <input type="number" class="size-input" name="sizes[{{ $size }}]" value="0">
                        @endforeach
                    @endif

                    @if($product->quantities->count())
                        <select id="quantitySelect" name="quantity_id">
                            @foreach($product->quantities as $q)
                                <option value="{{ $q->id }}" data-price="{{ $q->price }}" data-qty="{{ $q->quantity }}" data-step="{{ $q->step }}">
                                    {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="qtyMultiplier" name="quantity" value="1">
                    @endif
                </form>

            @else

                {{-- Normal product: no customizable images, show direct Add to Cart --}}
                <form method="GET" action="{{ route('cart.remarks.form', $product->id) }}" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    {{-- Product options first --}}
                    @if($product->options->count())
                        @foreach($product->options as $option)
                            <div class="option-block">
                                <div class="option-row">

                                    <div class="label">
                                        {{ $option->name }}
                                    </div>

                                    <select
                                        class="option-select"
                                        name="options[{{ $option->name }}]"
                                        required
                                        style="padding:10px 14px;border-radius:var(--r);border:1px solid var(--rule-strong);font-family:var(--ff-body);font-size:var(--text-sm);background:#fff;"
                                    >
                                        @foreach($option->values_array as $val)
                                            <option
                                                value="{{ $val }}"
                                                data-extra="{{ $option->value_prices[$val] ?? 0 }}"
                                            >
                                                {{ $val }}

                                                @if(!empty($option->value_prices[$val]))
                                                    (+₹{{ number_format($option->value_prices[$val]) }} / pc)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                            </div>
                        @endforeach
                    @endif

                    {{-- Size-wise quantity (tied to the Quantity total below) --}}
                    @if(!$product->use_stepper && $product->is_cloth)
                        <div class="option-block">
                            <div class="label">Size-wise Quantity</div>

                            @php
                                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                            @endphp

                            <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:var(--s3);margin-top:var(--s2)">
                                @foreach($sizes as $size)
                                    <div>
                                        <label style="display:block;font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute);margin-bottom:4px">{{ $size }}</label>
                                        <input type="number"
                                               name="sizes[{{ $size }}]"
                                               class="size-input"
                                               min="0"
                                               value="0"
                                               style="width:100%;padding:8px;border-radius:var(--r);border:1px solid var(--rule-strong);text-align:center;">
                                    </div>
                                @endforeach
                            </div>

                            <div style="margin-top:var(--s3);font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-soft)">
                                Selected: <span id="sizeTotal">0</span> /
                                Required: <span id="requiredTotal">0</span>
                            </div>

                            <div id="sizeMismatchWarning" style="display:none;color:#c0392b;font-size:var(--text-xs);margin-top:4px;">
                                Quantity not match — size quantities must add up to the selected total quantity.
                            </div>
                        </div>
                    @endif

                    {{-- Quantity last --}}
                    @if($product->quantities->count())
                        <div class="option-block">

                           @if($product->use_stepper)

                                <div class="option-row">

                                    <div class="label">
                                        Quantity
                                    </div>

                                    <div>
                                        <select name="quantity_id" id="quantitySelect" required
                                            style="width:100%;padding:10px 14px;border-radius:var(--r);border:1px solid var(--rule-strong);font-family:var(--ff-body);font-size:var(--text-sm);background:#fff;margin-bottom:var(--s3);box-sizing:border-box;">
                                            
                                            @foreach($product->quantities as $q)
                                                <option
                                                    value="{{ $q->id }}"
                                                    data-price="{{ $q->price }}"
                                                    data-qty="{{ $q->quantity }}"
                                                    data-step="{{ $q->step }}"
                                                >
                                                    {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }} (base)
                                                </option>
                                            @endforeach

                                        </select>

                                        <input type="hidden" name="quantity" id="qtyMultiplier" value="1">

                                        <div class="qty" style="max-width:180px;">
                                            <button type="button" data-act="-" id="tierMinus" aria-label="Decrease">−</button>

                                            <div style="text-align:center;flex:1;">
                                                <div style="font-weight:700" id="totalPcsDisplay"></div>
                                            </div>

                                            <button type="button" data-act="+" id="tierPlus" aria-label="Increase">+</button>
                                        </div>

                                        <div style="font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute);margin-top:var(--s2)"
                                            id="totalPriceDisplay"></div>

                                        <div style="font-family:var(--ff-mono);font-size:var(--text-xs);color:var(--fg-mute);margin-top:4px"
                                            id="stepHint"></div>
                                    </div>

                                </div>

                            @else

                                <div class="option-row">

                                    <div class="label">
                                        Quantity
                                    </div>

                                    <select name="quantity_id" id="quantitySelect" required
                                        style="padding:10px 14px;border-radius:var(--r);border:1px solid var(--rule-strong);font-family:var(--ff-body);font-size:var(--text-sm);background:#fff;">
                                        
                                        @foreach($product->quantities as $q)
                                            <option
                                                value="{{ $q->id }}"
                                                data-price="{{ $q->price }}"
                                                data-qty="{{ $q->quantity }}"
                                                data-step="{{ $q->step }}"
                                            >
                                                {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                            </option>
                                        @endforeach

                                    </select>

                                </div>

                            @endif
                        </div>
                    @endif

                    <div class="pdp-cta">
                        <button type="submit" class="btn btn--indigo" style="flex:1;min-width:160px;justify-content:center">🛒 Add to cart →</button>
                    </div>
                </form>

            @endif

            <div class="pdp-features">
                <div class="pf"><span class="ic">⚡</span><span>Free shipping over ₹999</span></div>
                <div class="pf"><span class="ic">↺</span><span>Easy returns</span></div>
                <div class="pf"><span class="ic">✓</span><span>Quality checked</span></div>
            </div>

        </div>

    </section>

</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const qtySelect = document.getElementById('quantitySelect');
    const priceEl = document.getElementById('displayPrice');
    const sizeInputs = document.querySelectorAll('.size-input');
    const sizeTotalEl = document.getElementById('sizeTotal');
    const requiredTotalEl = document.getElementById('requiredTotal');
    const mismatchWarning = document.getElementById('sizeMismatchWarning');
    const form = document.getElementById('addToCartForm');

    // --- Option value surcharges are PER PIECE (e.g. Size: A4 -> +₹25 per pc) ---
    const optionSelects = document.querySelectorAll('.option-select');

    function getOptionsExtraPerPiece() {
        let extra = 0;
        optionSelects.forEach(function (sel) {
            const opt = sel.options[sel.selectedIndex];
            if (opt) {
                extra += parseFloat(opt.dataset.extra) || 0;
            }
        });
        return extra;
    }

    // --- Stepper: +/- steps by a custom step size (falls back to the tier's own
    //     quantity if no step is set, which reproduces the old behavior exactly) ---
    const tierMinus = document.getElementById('tierMinus');
    const tierPlus  = document.getElementById('tierPlus');
    const qtyMultiplierInput = document.getElementById('qtyMultiplier');
    const totalPcsDisplay = document.getElementById('totalPcsDisplay');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const stepHint = document.getElementById('stepHint');

    let addedUnits = 0;

    function getSelectedTier() {
        if (!qtySelect) return null;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        if (!opt) return null;
        const qty = parseInt(opt.dataset.qty, 10) || 0;
        const step = parseInt(opt.dataset.step, 10) || 0;
        return {
            qty: qty,
            price: parseFloat(opt.dataset.price) || 0,
            step: step > 0 ? step : qty
        };
    }

    function updateStepperDisplay() {
        if (!totalPcsDisplay || !qtyMultiplierInput) return;

        const tier = getSelectedTier();
        if (!tier || tier.qty <= 0) return;

        const extraPerPiece = getOptionsExtraPerPiece();
        const totalPcs = tier.qty + (addedUnits * tier.step);

        const perPieceRate = (tier.price / tier.qty) + extraPerPiece;
        const totalPrice = perPieceRate * totalPcs;

        qtyMultiplierInput.value = addedUnits;
        totalPcsDisplay.textContent = totalPcs.toLocaleString('en-IN') + ' pcs';

        if (totalPriceDisplay) {
            totalPriceDisplay.textContent = '₹ ' + totalPrice.toLocaleString('en-IN');
        }
        if (priceEl) {
            priceEl.textContent = '₹ ' + totalPrice.toLocaleString('en-IN');
        }
        if (stepHint) {
            stepHint.textContent = 'Each click adds/removes ' + tier.step + ' pcs.';
        }
    }

    if (tierMinus && tierPlus) {
        tierMinus.addEventListener('click', function () {
            if (addedUnits > 0) {
                addedUnits -= 1;
                updateStepperDisplay();
            }
        });
        tierPlus.addEventListener('click', function () {
            addedUnits += 1;
            updateStepperDisplay();
        });

        if (qtySelect) {
            qtySelect.addEventListener('change', function () {
                addedUnits = 0;
                updateStepperDisplay();
            });
        }

        updateStepperDisplay();
    }

    // --- Existing tier / size logic (unchanged, used by non-stepper products) ---
    function getRequiredQty() {
        if (!qtySelect) return null;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        return parseInt(opt.dataset.qty, 10) || 0;
    }

    function updatePrice() {
        if (!qtySelect || !priceEl || tierMinus) return;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        const price = parseFloat(opt.dataset.price) || 0;
        const qty = parseInt(opt.dataset.qty, 10) || 0;
        const extraPerPiece = getOptionsExtraPerPiece();
        const total = price + (extraPerPiece * qty);
        priceEl.textContent = '₹ ' + total.toLocaleString('en-IN');
    }

    function updateSizeTotals() {
        if (!sizeInputs.length || !sizeTotalEl) return;

        let total = 0;
        sizeInputs.forEach(function (input) {
            total += parseInt(input.value, 10) || 0;
        });

        const required = getRequiredQty();

        sizeTotalEl.textContent = total;

        if (required === null) {
            requiredTotalEl.textContent = total;
            if (mismatchWarning) mismatchWarning.style.display = 'none';
        } else {
            requiredTotalEl.textContent = required;
            if (mismatchWarning) mismatchWarning.style.display = (total !== required) ? '' : 'none';
        }
    }

    if (qtySelect && !tierMinus) {
        qtySelect.addEventListener('change', function () {
            updatePrice();
            updateSizeTotals();
        });
        updatePrice();
    }

    optionSelects.forEach(function (sel) {
        sel.addEventListener('change', function () {
            if (tierMinus) {
                updateStepperDisplay();
            } else {
                updatePrice();
            }
        });
    });

    sizeInputs.forEach(function (input) {
        input.addEventListener('input', updateSizeTotals);
    });

    updateSizeTotals();

    if (form && sizeInputs.length && form.getAttribute('action')) {
        form.addEventListener('submit', function (e) {
            let total = 0;
            sizeInputs.forEach(function (input) {
                total += parseInt(input.value, 10) || 0;
            });

            const required = getRequiredQty();

            if (required === null) {
                if (total < 1) {
                    e.preventDefault();
                    mismatchWarning.style.display = '';
                    mismatchWarning.textContent = 'Please enter at least 1 in one of the sizes.';
                    alert('Please enter at least 1 in one of the sizes.');
                }
                return;
            }

            if (total !== required) {
                e.preventDefault();
                mismatchWarning.style.display = '';
                alert('Quantity not match. Size quantities must add up to ' + required + '.');
            }
        });
    }
});
</script>

<x-footer></x-footer>
<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>