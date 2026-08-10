<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>
<h2 class="mb-4 mt-2 text-center">Products</h2>
<div class="container">
    <div class="row">

        @foreach($products as $row)
        <div class="col-md-3 mb-4">
            <div class="card h-100 shadow-sm">

                {{-- Product Image --}}
                <img src="{{ asset('images/'.$row->image) }}" 
                     class="card-img-top" 
                     style="height:200px; object-fit:cover;" 
                     alt="product">

                <div class="card-body d-flex flex-column">

                    {{-- Name --}}
                    <h5 class="card-title">{{ $row->name }}</h5>

                    {{-- Price --}}
                    <p class="text-success fw-bold">₹ {{ $row->price }}</p>

                    {{-- Description --}}
                    <p class="card-text">{{ $row->description }}</p>

                    {{-- Stock --}}
                    <p class="text-muted">Stock: {{ $row->stock }}</p>

                    {{-- Buttons --}}
                    <div class="mt-auto">
                        <a href="{{ url('/product/' . $row->id) }}"
                        class="btn btn-warning w-100">
                            View Product
                        </a>

                        {{-- Add to Cart --}}
                        <!-- <form action="{{ url('add_cart') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $row->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button class="btn btn-primary w-100 mb-2">
                                Add to Cart
                            </button>
                        </form> -->

                    </div>

                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>