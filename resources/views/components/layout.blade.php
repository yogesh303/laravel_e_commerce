{{-- resources/views/components/layout.blade.php --}}

@php
    $user_role  = Auth::check() ? Auth::user()->role  : null;
    $user_email = Auth::check() ? Auth::user()->email : null;
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

<a class="skip-link" href="#main">Skip to content</a>

<!-- Top utility strip -->
<div class="utility">
    <div class="container">
        <span class="promo">
            <span class="tag">SALE</span>
            Free shipping on orders over ₹999 · Easy returns
        </span>
        <span class="links">
            <a href="{{ url('orders') }}">Track order</a>
            <a href="{{ url('contact') }}">Help</a>
            <a href="#">EN · INR</a>
        </span>
    </div>
</div>

<!-- Header -->
<header class="site-header">
    <div class="container">
        <a href="{{ url('/') }}" class="brand">
            <span class="brand-mark">🛒</span>
            E-Commerce
        </a>

        <form class="search" role="search" action="{{ url('products') }}" method="GET">
            <input type="text" name="search" placeholder="Search for products, brands, categories…" aria-label="Search the store" />
            <button type="submit" aria-label="Search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
            </button>
        </form>

        <div class="icon-row">
            @auth
                <a href="{{ url('dashboard') }}" class="icon-btn" aria-label="Account" title="{{ $user_email }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
                </a>
            @else
                <a href="{{ url('/login') }}" class="icon-btn" aria-label="Login">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
                </a>
            @endauth

            <a href="{{ url('cart') }}" class="icon-btn icon-btn--cart" aria-label="Cart">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2l-2 5v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7l-2-5z"/><path d="M4 7h16"/><path d="M16 11a4 4 0 0 1-8 0"/></svg>
                @if(!empty($cartCount))
                    <span class="count">{{ $cartCount }}</span>
                @endif
            </a>

            <button class="nav-toggle" aria-label="Open menu" aria-expanded="false">≡</button>
        </div>
    </div>
</header>

<!-- Category nav -->
<nav class="nav-bar" aria-label="Primary">
    <div class="container">
        <a href="{{ url('products') }}" class="all-cats">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            All Categories
        </a>

        <div class="main-nav">
            <a href="{{ url('products') }}" @if(request()->is('products*')) aria-current="page" @endif>Products</a>
            <a href="{{ url('cart') }}" @if(request()->is('cart')) aria-current="page" @endif>My Cart</a>

            @auth
                <a href="{{ url('orders') }}" @if(request()->is('orders*')) aria-current="page" @endif>All Orders</a>

                @if($user_role == 'admin')
                    <a href="{{ url('product_list') }}" @if(request()->is('product_list')) aria-current="page" @endif>Product List</a>
                    <a href="{{ url('product_form') }}" @if(request()->is('product_form')) aria-current="page" @endif>Add Product</a>
                    <a href="{{ url('categories') }}" @if(request()->is('categories')) aria-current="page" @endif>Add Categories</a>
                    <a href="{{ route('settings.index') }}" @if(request()->routeIs('settings.index')) aria-current="page" @endif>⚙️ Settings</a>
                @endif
            @endauth
        </div>

        <span class="nav-cta">
            @auth
                {{ $user_email }}
                &nbsp;·&nbsp;
                <form action="{{ route('logout') }}" method="POST" style="display:inline">
                    @csrf
                    <button type="submit" style="background:none;border:none;color:inherit;font:inherit;cursor:pointer;padding:0;">Logout</button>
                </form>
            @else
                <a href="{{ url('/login') }}" style="color:inherit;">Login</a>
                &nbsp;/&nbsp;
                <a href="{{ url('/signup') }}" style="color:inherit;">Sign Up</a>
            @endauth
        </span>
    </div>
</nav>

<!-- Mobile drawer -->
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-head">
        <a href="{{ url('/') }}" class="brand"><span class="brand-mark">🛒</span> E-Commerce</a>
        <button class="drawer-close" aria-label="Close menu">Close ✕</button>
    </div>

    <a href="{{ url('products') }}">Products</a>
    <a href="{{ url('cart') }}">My Cart</a>

    @auth
        <a href="{{ url('orders') }}">All Orders</a>

        @if($user_role == 'admin')
            <a href="{{ url('product_list') }}">Product List</a>
            <a href="{{ url('product_form') }}">Add Product</a>
            <a href="{{ url('categories') }}">Add Categories</a>
            <a href="{{ route('settings.index') }}">⚙️ Settings</a>
        @endif

        <div style="margin-top:var(--s4);padding-top:var(--s4);border-top:1px solid rgba(0,0,0,.08);">
            <div style="margin-bottom:var(--s3);opacity:.7;">{{ $user_email }}</div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="btn btn--indigo" style="width:100%;justify-content:center">Logout</button>
            </form>
        </div>
    @else
        <a href="{{ url('/login') }}" class="btn btn--indigo" style="margin-top:var(--s5); justify-content:center">Login</a>
        <a href="{{ url('/signup') }}" style="text-align:center;">Sign Up</a>
    @endauth
</div>