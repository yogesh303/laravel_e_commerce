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
                <div class="fs-4 text-success fw-bold mb-3">₹ {{ number_format($product->price) }}</div>

                <p class="text-muted">{{ $product->description }}</p>

                @if($product->stock > 0)
                    <span class="badge bg-success mb-3">In Stock ({{ $product->stock }})</span>
                @else
                    <span class="badge bg-danger mb-3">Out of Stock</span>
                @endif

                <!-- Add to Cart (with dynamic options: Size, Color, etc.) -->
                <form method="POST" action="{{ url('/add_cart') }}" class="mb-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

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
</body>
</html>