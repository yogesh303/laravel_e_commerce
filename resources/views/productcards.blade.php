<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>
        .hero-carousel {
            width: 100%;
            position: relative;
        }

        .carousel-slide {
            width: 100%;
            min-height: 460px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            position: relative;
        }

        .carousel-slide::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(245, 179, 179, 0.6) 0%, rgba(0,0,0,0.25) 55%, rgba(0,0,0,0) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 560px;
            margin-left: 6%;
            color: #fff;
        }

        .hero-content h1 {
            font-size: 2.6rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.2;
            color: #2E1A3D;
        }

        .hero-content p {
            font-size: 1.05rem;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        .hero-content .hero-btn {
            display: inline-block;
            background: #fff;
            color: #111;
            padding: 10px 22px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .carousel-slide { min-height: 320px; }
            .hero-content { margin-left: 5%; max-width: 90%; }
            .hero-content h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
<x-layout></x-layout>

<main id="main">

    @if(!(isset($activeSubcategory) && $activeSubcategory) && !(isset($activeCategory) && $activeCategory))
        <section class="hero-carousel">
            <div class="carousel-track" id="heroCarousel">
                <div class="carousel-slide active" style="background-image: url('{{ asset('assets/images/banner.PNG') }}');">
                    <div class="hero-content">
                        <h1>Corporate Gifting Essentials</h1>
                        <p>Bottles, notebooks, brochures, calendars &amp; more — everything for your brand.</p>
                        <a href="{{ url('contact') }}" class="btn hero-btn">Inquiry</a>
                    </div>
                </div>
                {{-- add more .carousel-slide blocks here for multiple slides --}}
            </div>
        </section>
    @endif

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