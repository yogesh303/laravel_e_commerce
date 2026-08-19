<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $product->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>

<div class="container mt-5 mb-5">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @php
        $hasCustomizable = $product->images->where('is_customizable', true)->count() > 0;
    @endphp

    <div class="row g-4">

        <!-- Gallery -->
        <div class="col-lg-6">
            <div class="card shadow p-3">

                <img id="mainImage"
                     src="{{ asset('images/' . $product->image) }}"
                     class="img-fluid rounded mb-3"
                     style="height:400px;object-fit:cover;width:100%;">

                <div class="d-flex gap-2 flex-wrap">
                    <img src="{{ asset('images/' . $product->image) }}"
                         class="thumb rounded border"
                         style="width:70px;height:70px;object-fit:cover;cursor:pointer;"
                         onclick="document.getElementById('mainImage').src=this.src">

                    @foreach($product->images as $img)
                        <img src="{{ asset('images/' . $img->image) }}"
                             class="thumb rounded border"
                             style="width:70px;height:70px;object-fit:cover;cursor:pointer;"
                             onclick="document.getElementById('mainImage').src=this.src">
                    @endforeach
                </div>

            </div>
        </div>

        <!-- Details -->
        <div class="col-lg-6">
            <div class="card shadow p-4">

            <h3>{{ $product->name }}</h3>
            <div class="fs-4 text-success fw-bold mb-3" id="displayPrice">
                ₹ {{ number_format($product->quantities->first()->price ?? $product->price) }}
            </div>

            <p class="text-muted">{{ $product->description }}</p>

            @if($hasCustomizable)

                {{-- This product requires customization before it can be added to cart --}}
                <div class="alert alert-info small">
                    This product must be customized before adding to cart.
                </div>

                <a href="{{ route('product.customize', $product->id) }}" class="btn btn-success btn-lg w-100" id="customizeLink">
                    🎨 Customize This Product
                </a>

                <div class="mt-2">
                    <small class="text-muted">
                        {{ $product->images->where('is_customizable', true)->count() }} image(s) available to customize
                    </small>
                </div>

                {{-- Hidden helper form — not shown to the user, only used to capture
                     quantity/sizes/options so we can carry them to the customize page --}}
                <form id="addToCartForm" class="d-none">
                    @if($product->quantities->count())
                        <select id="quantitySelect" name="quantity_id">
                            @foreach($product->quantities as $q)
                                <option value="{{ $q->id }}" data-price="{{ $q->price }}" data-qty="{{ $q->quantity }}">
                                    {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="qtyMultiplier" name="quantity" value="1">
                    @endif

                    @if(!$product->use_stepper && $product->is_cloth)
                        @php $sizes = ['S', 'M', 'L', 'XL', 'XXL']; @endphp
                        @foreach($sizes as $size)
                            <input type="number" class="size-input" name="sizes[{{ $size }}]" value="0">
                        @endforeach
                    @endif

                    @if($product->options->count())
                        @foreach($product->options as $option)
                            <select name="options[{{ $option->name }}]">
                                @foreach($option->values_array as $val)
                                    <option value="{{ $val }}">{{ $val }}</option>
                                @endforeach
                            </select>
                        @endforeach
                    @endif
                </form>

            @else

                {{-- Normal product: no customizable images, show direct Add to Cart --}}
                <form method="POST" action="{{ url('/add_cart') }}" class="mb-3" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if($product->quantities->count())
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity</label>

                            @if($product->use_stepper)

                                {{-- Pick the base tier, then +/- steps by THAT tier's own
                                     quantity: base tier = 5 pcs -> click + -> 10 pcs -> 15 pcs ... --}}
                                <select class="form-select mb-2" name="quantity_id" id="quantitySelect" required>
                                    @foreach($product->quantities as $q)
                                        <option value="{{ $q->id }}" data-price="{{ $q->price }}" data-qty="{{ $q->quantity }}">
                                            {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }} (base)
                                        </option>
                                    @endforeach
                                </select>

                                <input type="hidden" name="quantity" id="qtyMultiplier" value="1">

                                <div class="d-flex align-items-center" style="max-width:260px;">
                                    <button type="button" class="btn btn-danger btn-sm" id="tierMinus">-</button>

                                    <div class="mx-3 text-center flex-grow-1">
                                        <div class="fw-bold" id="totalPcsDisplay"></div>
                                        <small class="text-muted" id="totalPriceDisplay"></small>
                                    </div>

                                    <button type="button" class="btn btn-success btn-sm" id="tierPlus">+</button>
                                </div>

                                <div class="form-text mt-1" id="stepHint"></div>

                            @else

                                <select class="form-select" name="quantity_id" id="quantitySelect" required>
                                    @foreach($product->quantities as $q)
                                        <option value="{{ $q->id }}" data-price="{{ $q->price }}" data-qty="{{ $q->quantity }}">
                                            {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                        </option>
                                    @endforeach
                                </select>

                            @endif
                        </div>
                    @endif

                    @if(!$product->use_stepper && $product->is_cloth)
                        <div class="mb-3 border rounded p-3 bg-light">
                            <label class="form-label fw-bold mb-2">Size-wise Quantity</label>

                            @php
                                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                            @endphp

                            <div class="row g-2">
                                @foreach($sizes as $size)
                                    <div class="col-4 col-md-2">
                                        <label class="form-label small mb-1">{{ $size }}</label>
                                        <input type="number"
                                               name="sizes[{{ $size }}]"
                                               class="form-control size-input"
                                               min="0"
                                               value="0">
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-2 small">
                                Selected: <span id="sizeTotal">0</span> /
                                Required: <span id="requiredTotal">0</span>
                            </div>

                            <div id="sizeMismatchWarning" class="text-danger small mt-1" style="display:none;">
                                Quantity not match — size quantities must add up to the selected total quantity.
                            </div>
                        </div>
                    @endif

                    @if($product->options->count())
                        @foreach($product->options as $option)
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ $option->name }}</label>
                                <select class="form-select option-select" name="options[{{ $option->name }}]" required>
                                    @foreach($option->values_array as $val)
                                        <option value="{{ $val }}"
                                            data-extra="{{ $option->value_prices[$val] ?? 0 }}">
                                            {{ $val }}
                                            @if(!empty($option->value_prices[$val]))
                                                (+₹{{ number_format($option->value_prices[$val]) }} / pc)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    @endif

                    <button class="btn btn-outline-primary btn-lg w-100">🛒 Add to Cart</button>
                </form>

            @endif

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
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

    // --- Stepper: +/- steps by the SELECTED TIER's own quantity ---
    // e.g. tier = "5 pcs @ ₹1605": click + -> 10 pcs, click again -> 15 pcs ...
    const tierMinus = document.getElementById('tierMinus');
    const tierPlus  = document.getElementById('tierPlus');
    const qtyMultiplierInput = document.getElementById('qtyMultiplier');
    const totalPcsDisplay = document.getElementById('totalPcsDisplay');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const stepHint = document.getElementById('stepHint');

    let multiplier = 1;

    function getSelectedTier() {
        if (!qtySelect) return null;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        if (!opt) return null;
        return {
            qty: parseInt(opt.dataset.qty, 10) || 0,
            price: parseFloat(opt.dataset.price) || 0
        };
    }

    function updateStepperDisplay() {
        if (!totalPcsDisplay || !qtyMultiplierInput) return;

        const tier = getSelectedTier();
        if (!tier) return;

        const extraPerPiece = getOptionsExtraPerPiece();
        const totalPcs = tier.qty * multiplier;

        // Base tier price scales with multiplier, option surcharge scales with total pieces
        const totalPrice = (tier.price * multiplier) + (extraPerPiece * totalPcs);

        qtyMultiplierInput.value = multiplier;
        totalPcsDisplay.textContent = totalPcs.toLocaleString('en-IN') + ' pcs';

        if (totalPriceDisplay) {
            totalPriceDisplay.textContent = '₹ ' + totalPrice.toLocaleString('en-IN');
        }
        if (priceEl) {
            priceEl.textContent = '₹ ' + totalPrice.toLocaleString('en-IN');
        }
        if (stepHint) {
            stepHint.textContent = 'Each click adds/removes ' + tier.qty + ' pcs (this tier\'s quantity).';
        }
    }

    if (tierMinus && tierPlus) {
        tierMinus.addEventListener('click', function () {
            if (multiplier > 1) {
                multiplier -= 1;
                updateStepperDisplay();
            }
        });
        tierPlus.addEventListener('click', function () {
            multiplier += 1;
            updateStepperDisplay();
        });

        if (qtySelect) {
            qtySelect.addEventListener('change', function () {
                multiplier = 1; // reset multiplier when a different base tier is picked
                updateStepperDisplay();
            });
        }

        updateStepperDisplay(); // initial render
    }

    // --- Existing tier / size logic (unchanged, used by non-stepper products) ---
    function getRequiredQty() {
        if (!qtySelect) return null;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        return parseInt(opt.dataset.qty, 10) || 0;
    }

    function updatePrice() {
        if (!qtySelect || !priceEl || tierMinus) return; // skip: stepper handles its own price display
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

    // Recalculate the displayed price whenever an option (e.g. Size) changes
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

    // Only relevant for the real (non-customizable-product) Add to Cart form
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
</body>
</html>