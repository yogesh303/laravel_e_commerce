<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
</head>
<body>
<x-layout></x-layout>

<main id="main">
    <section class="section" style="padding-top: var(--s5)">
        <div class="container">

            <div class="section-head">
                @if(isset($activeSubcategory) && $activeSubcategory)
                    <h2>{{ $activeSubcategory->name }}</h2>
                    <p style="opacity:.65;margin-top:4px;">
                        in {{ $activeSubcategory->category->name ?? '' }}
                        &nbsp;·&nbsp;
                        <a href="{{ url('products') }}">Clear filter</a>
                    </p>
                @elseif(isset($activeCategory) && $activeCategory)
                    <h2>{{ $activeCategory->name }}</h2>
                    <p style="opacity:.65;margin-top:4px;">
                        <a href="{{ url('products') }}">Clear filter</a>
                    </p>
                @else
                    <h2>All Products</h2>
                @endif
            </div>

            <div class="products">

                @forelse($products as $row)
                    <article class="product-card">
                        <div class="img-wrap">
                            @if($row->quantity <= 5 && $row->quantity > 0)
                                <span class="badge badge--sale">Low stock</span>
                            @elseif($row->created_at && $row->created_at->gt(now()->subDays(14)))
                                <span class="badge">New</span>
                            @endif

                            <button class="wishlist" aria-label="Wishlist">♡</button>

                            <a href="{{ url('/product/' . $row->id) }}">
                                <img src="{{ asset('images/' . $row->image) }}" alt="{{ $row->name }}" />
                            </a>
                        </div>

                        <a href="{{ url('/product/' . $row->id) }}" class="name">{{ $row->name }}</a>

                        <div class="price">
                            <span class="now">₹ {{ number_format($row->price) }}</span>
                        </div>

                        <a href="{{ url('/product/' . $row->id) }}" class="btn">View Product →</a>
                    </article>
                @empty
                    <p>No products found.</p>
                @endforelse

            </div>

        </div>
    </section>
</main>

<x-footer></x-footer>

<script src="{{ asset('assets/js/main.js') }}" defer></script>
</body>
</html>