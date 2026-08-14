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

            <!-- (stock badge removed) -->

                <!-- Add to Cart (with dynamic options: Size, Color, etc.) -->
                <form method="POST" action="{{ url('/add_cart') }}" class="mb-3" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if($product->quantities->count())
                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity</label>
                            <select class="form-select" name="quantity_id" id="quantitySelect" required>
                                @foreach($product->quantities as $q)
                                    <option value="{{ $q->id }}" data-price="{{ $q->price }}" data-qty="{{ $q->quantity }}">
                                        {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if($product->is_cloth)
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
                                <select class="form-select" name="options[{{ $option->name }}]" required>
                                    @foreach($option->values_array as $val)
                                        <option value="{{ $val }}">{{ $val }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    @endif

                    <button class="btn btn-outline-primary btn-lg w-100">🛒 Add to Cart</button>
                </form>

                @if($product->images->where('is_customizable', true)->count() || $product->image)
                    <a href="{{ route('product.customize', $product->id) }}" class="btn btn-success btn-lg w-100">
                        🎨 Customize This Product
                    </a>
                @endif

                <div class="mt-2">
                    <small class="text-muted">
                        {{ $product->images->where('is_customizable', true)->count() }} image(s) available to customize
                    </small>
                </div>

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

    function getRequiredQty() {
        if (!qtySelect) return null; // no tiers on this product — nothing to match against
        const opt = qtySelect.options[qtySelect.selectedIndex];
        return parseInt(opt.dataset.qty, 10) || 0;
    }

    function updatePrice() {
        if (!qtySelect) return;
        const opt = qtySelect.options[qtySelect.selectedIndex];
        const price = parseFloat(opt.dataset.price);
        priceEl.textContent = '₹ ' + price.toLocaleString('en-IN');
    }

    function updateSizeTotals() {
    if (!sizeInputs.length) return;

    let total = 0;
    sizeInputs.forEach(function (input) {
        total += parseInt(input.value, 10) || 0;
    });

    const required = getRequiredQty();

    sizeTotalEl.textContent = total;

    if (required === null) {
        // No quantity tiers — just show what they've entered, no "required" target
        requiredTotalEl.textContent = total;
            mismatchWarning.style.display = 'none';
        } else {
            requiredTotalEl.textContent = required;
            mismatchWarning.style.display = (total !== required) ? '' : 'none';
        }
    }

    if (qtySelect) {
        qtySelect.addEventListener('change', function () {
            updatePrice();
            updateSizeTotals();
        });
        updatePrice();
    }

    sizeInputs.forEach(function (input) {
        input.addEventListener('input', updateSizeTotals);
    });

    updateSizeTotals(); // initial state on load

    // Block submission client-side if sizes don't add up (server also re-checks this)
    if (form && sizeInputs.length) {
        form.addEventListener('submit', function (e) {
            let total = 0;
            sizeInputs.forEach(function (input) {
                total += parseInt(input.value, 10) || 0;
            });

            const required = getRequiredQty();

            if (required === null) {
                // No tiers — just require at least one item across sizes
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