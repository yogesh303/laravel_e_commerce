{{-- resources/views/components/layout.blade.php --}}

@php
    $user_role  = Auth::check() ? Auth::user()->role  : null;
    $user_email = Auth::check() ? Auth::user()->email : null;

    // Categories + subcategories + their products (need product count/id to decide
    // whether a subcategory link should go to the listing page or straight to the
    // single product it contains).
    $navCategories = \App\Models\Category::with(['subcategories.products' => function ($q) {
        $q->select('id', 'subcategory_id');
    }])->get();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Outfit:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

<style>
    /* --- Dropdown styles (shared by category links + the Menu link, so they look identical) --- */
    .dropdown { position: relative; display: inline-block; }

    .dropdown-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: none;
        border: none;
        font: inherit;
        color: inherit;
        text-decoration: none;
        cursor: pointer;
        padding: 8px 4px;
    }
    .dropdown-toggle svg.chevron { transition: transform .15s ease; opacity: .6; }
    .dropdown.open .dropdown-toggle svg.chevron { transform: rotate(180deg); }
    .dropdown-toggle[aria-current="page"] { text-decoration: underline; text-underline-offset: 3px; }

    .category-links {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    position: relative; /* ADDED */
}

/* Update mega menu positioning and scroll behavior */
.mega { 
    position: relative; /* CHANGED: keep context predictable */
} 

.mega-panel {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin: 6px auto 0;
    width: min(1000px, calc(100vw - 32px));
    background: #fff;
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 12px;
    box-shadow: 0 18px 40px rgba(0,0,0,.14);
    padding: 22px 26px;
    display: none;
    z-index: 60;

    /* FIXED: Constraints & Internal Scrolling */
    max-height: calc(85vh - 70px); /* Keeps panel within viewport height */
    overflow-y: auto;              /* Enables scrolling inside the menu */
}

    .dropdown-menu {
        position: absolute;
        top: 68%;
        left: 0;
        min-width: 220px;
        background: #fff;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(0,0,0,.12);
        padding: 6px;
        display: none;
        z-index: 60;
        margin-top: 6px;
    }
    /* Menu link: opens via click (JS toggles .open) */
    .dropdown.open .dropdown-menu { display: block; }
    /* Category links: opens via hover, same look, no click needed */
    .nav-cat-item:hover > .dropdown-menu,
    .nav-cat-item:focus-within > .dropdown-menu {
        display: block;
    }

    .dropdown-menu a,
    .dropdown-menu button {
        display: flex;
        align-items: center;
        width: 100%;
        text-align: left;
        padding: 9px 10px;
        border-radius: 7px;
        color: inherit;
        text-decoration: none;
        font: inherit;
        background: none;
        border: none;
        cursor: pointer;
    }
    .dropdown-menu a:hover,
    .dropdown-menu button:hover { background: rgba(0,0,0,.05); }

    .dropdown-menu .divider {
        height: 1px;
        margin: 6px 4px;
        background: rgba(0,0,0,.08);
        border: none;
    }
    .dropdown-menu .group-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        opacity: .55;
        padding: 6px 10px 2px;
    }

    /* If a dropdown would overflow the right edge, flip it to hang from the right instead */
    .dropdown.flip > .dropdown-menu {
        left: auto;
        right: 0;
    }
    .dropdown-toggle svg.chevron {
        display: none;
    }
    .dropdown-toggle::after {
        display: none;
    }

    /* ---------------------------------------------------------------
     | "Products" mega menu — sits first in .category-links, opens on
     | click, shows every category as a column with its subcategories
     | listed underneath. All other category links are untouched.
     * -------------------------------------------------------------*/
   
    .mega.open .mega-panel { display: block; }

    .mega-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 24px;
    }

    .mega-col-title {
        display: block;
        font-weight: 700;
        font-size: .95rem;
        color: inherit;
        text-decoration: none;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid rgba(0,0,0,.06);
    }
    .mega-col-title:hover { opacity: .7; }

    .mega-sub-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .mega-sub-list a {
        text-decoration: none;
        color: inherit;
        opacity: .78;
        font-size: .88rem;
    }
    .mega-sub-list a:hover { opacity: 1; }

    .mega-sub-empty {
        font-size: .8rem;
        opacity: .45;
        font-style: italic;
    }

    /* --- Search suggestions dropdown --- */
    .search-wrap {
        position: relative;
        flex: 1;
    }

    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 8px;
        background: #fff;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 10px;
        box-shadow: 0 12px 28px rgba(0,0,0,.12);
        max-height: 420px;
        overflow-y: auto;
        z-index: 70;
        display: none;
    }
    .search-suggestions.open { display: block; }

    .search-suggestions .group-label {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        opacity: .55;
        padding: 10px 14px 4px;
    }

    .search-suggestions .sugg-row {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        text-align: left;
        padding: 8px 14px;
        color: inherit;
        text-decoration: none;
        background: none;
        border: none;
        cursor: pointer;
        font: inherit;
    }
    .search-suggestions .sugg-row:hover,
    .search-suggestions .sugg-row.active { background: rgba(0,0,0,.05); }

    .search-suggestions .sugg-thumb {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 6px;
        object-fit: cover;
        background: rgba(0,0,0,.05);
    }

    .search-suggestions .sugg-icon {
        flex-shrink: 0;
        width: 34px;
        height: 34px;
        border-radius: 6px;
        background: rgba(0,0,0,.05);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: .6;
    }

    .search-suggestions .sugg-text {
        min-width: 0;
    }
    .search-suggestions .sugg-name {
        font-size: .88rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .search-suggestions .sugg-meta {
        font-size: .76rem;
        opacity: .6;
    }

    .search-suggestions .sugg-empty {
        padding: 16px 14px;
        font-size: .85rem;
        opacity: .6;
    }

    .search-suggestions .sugg-viewall {
        border-top: 1px solid rgba(0,0,0,.06);
        font-weight: 600;
        color: var(--indigo, inherit);
    }

    /* --- Mobile drawer category accordion --- */
    .drawer .cat-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        list-style: none;
    }
    .drawer .cat-summary::-webkit-details-marker { display: none; }
    .drawer .cat-summary::marker { content: ""; }
    .drawer .cat-summary .cat-chevron {
        transition: transform .15s ease;
        opacity: .6;
        flex-shrink: 0;
    }
    .drawer details[open] > .cat-summary .cat-chevron {
        transform: rotate(180deg);
    }
    @media (max-width: 768px) {
        .nav-bar {
            display: none !important;
        }
    }
    .site-header .container {
        display: flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 16px;
    }

    .site-header .brand {
        flex-shrink: 0;
    }

    .site-header .search-wrap {
        flex: 1 1 auto;
        min-width: 0; /* allows the search box to shrink instead of pushing siblings down */
    }

    .site-header .nav-cta {
        flex-shrink: 0;
        white-space: nowrap;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .site-header .icon-row {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    @media (max-width: 992px) {
        .site-header .nav-cta span,
        .site-header .nav-cta a {
            font-size: 0.85rem;
        }
        .site-header .container {
            gap: 10px;
        }
    }
    /* --- Mobile header: logo + menu icon only, no horizontal scroll --- */
    @media (max-width: 768px) {
        body {
            overflow-x: hidden;
        }

        .site-header .container {
            flex-wrap: nowrap;
            gap: 10px;
        }

        .site-header .search-wrap,
        .site-header .nav-cta {
            display: none !important;
        }

        .site-header .icon-row {
            gap: 0;
        }

        .site-header .icon-row .icon-btn {
            display: none !important;
        }

        .site-header .icon-row .nav-toggle {
            display: inline-flex !important;
        }

        .site-header .brand-logo {
            width: 140px !important;
            height: auto;
        }

        .utility .container {
            flex-wrap: wrap;
            gap: 6px;
            font-size: 0.8rem;
        }
        .last_menus{
            margin-left: auto;
        }
    }
</style>

<a class="skip-link" href="#main">Skip to content</a>

<!-- Top utility strip -->
<div class="utility">
    <div class="container">
        <span class="promo">

        </span>
        <span class="links">
            <a href="{{ url('orders') }}">Track order</a>
            <a href="{{ url('contact') }}">Help</a>
        </span>
    </div>
</div>

<!-- Header -->
<header class="site-header">
    <div class="container">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('assets/images/logo.jpg') }}" alt="E-Commerce" width="200px" class="brand-logo">
        </a>

        <div class="search-wrap" id="searchWrap">
            <form class="search" role="search" action="{{ url('products') }}" method="GET" autocomplete="off">
                <input type="text" name="search" id="searchInput" placeholder="Search for products, brands, categories…" aria-label="Search the store" />
                <button type="submit" aria-label="Search">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                </button>
            </form>

            <div class="search-suggestions" id="searchSuggestions" role="listbox"></div>
        </div>

        <span class="nav-cta">
            @auth
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

        <div class="icon-row last_menus">
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

        <!-- Categories, same look as the "Menu" link, hover to reveal subcategories -->
        <div class="category-links">

            <!-- "Products" mega menu — first item, opens on click, shows every
                 category as a column with its subcategories underneath. -->
            <div class="dropdown mega" id="productsMega">
                <button type="button"
                        class="dropdown-toggle"
                        aria-haspopup="true"
                        aria-expanded="false"
                        @if(request()->is('products') && !request()->has('category_id') && !request()->has('subcategory_id')) aria-current="page" @endif>
                    All Products
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div class="mega-panel" role="menu">
                    <div class="mega-grid">
                        @foreach($navCategories as $cat)
                            <div class="mega-col">
                                <a href="{{ url('products') }}?category_id={{ $cat->id }}" class="mega-col-title">
                                    {{ $cat->name }}
                                </a>

                                @if($cat->subcategories->count())
                                    <ul class="mega-sub-list">
                                        @foreach($cat->subcategories as $sub)
                                            @php
                                                $onlyProduct = $sub->products->count() === 1 ? $sub->products->first() : null;
                                                $subLink = $onlyProduct
                                                    ? url('/product/' . $onlyProduct->id)
                                                    : url('products') . '?subcategory_id=' . $sub->id;
                                            @endphp
                                            <li><a href="{{ $subLink }}">{{ $sub->name }}</a></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="mega-sub-empty">No subcategories</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach($navCategories as $cat)
                <div class="dropdown nav-cat-item">
                    <a href="{{ url('products') }}?category_id={{ $cat->id }}"
                       class="dropdown-toggle"
                       @if((int) request()->get('category_id') === $cat->id) aria-current="page" @endif>
                        {{ $cat->name }}
                        @if($cat->subcategories->count())
                            <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        @endif
                    </a>

                    @if($cat->subcategories->count())
                        <div class="dropdown-menu" role="menu">
                            @foreach($cat->subcategories as $sub)
                                @php
                                    $onlyProduct = $sub->products->count() === 1 ? $sub->products->first() : null;
                                    $subLink = $onlyProduct
                                        ? url('/product/' . $onlyProduct->id)
                                        : url('products') . '?subcategory_id=' . $sub->id;
                                @endphp
                                <a href="{{ $subLink }}" role="menuitem">{{ $sub->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- "Menu" link: identical styling, opens on click -->
            @if($user_role == 'admin')
            <div class="dropdown" id="menuDropdown">
                <button type="button" class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">
                    Menu
                    <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>

                <div class="dropdown-menu" role="menu">
                    <a href="{{ url('products') }}" @if(request()->is('products*')) aria-current="page" @endif>Products</a>
                    <a href="{{ url('cart') }}" @if(request()->is('cart')) aria-current="page" @endif>My Cart</a>

                    @auth
                        <a href="{{ url('orders') }}" @if(request()->is('orders*')) aria-current="page" @endif>All Orders</a>

                        
                            <hr class="divider">
                            <div class="group-label">Admin</div>
                            <a href="{{ url('product_list') }}" @if(request()->is('product_list')) aria-current="page" @endif>Product List</a>
                            <a href="{{ url('product_form') }}" @if(request()->is('product_form')) aria-current="page" @endif>Add Product</a>
                            <a href="{{ url('categories') }}" @if(request()->is('categories')) aria-current="page" @endif>Add Categories</a>
                            <a href="{{ url('admin/reviews') }}" @if(request()->is('admin/reviews')) aria-current="page" @endif>All Reviews</a>
                            <a href="{{ route('settings.index') }}" @if(request()->routeIs('settings.index')) aria-current="page" @endif>⚙️ Settings</a>
                        
                    @endauth
                </div>
            </div>
            @endif
        </div>
    </div>
</nav>

<!-- Mobile drawer -->
<div class="drawer" id="drawer" aria-hidden="true">
    <div class="drawer-head">
        <a href="{{ url('/') }}" class="brand"><span class="brand-mark">🛒</span> E-Commerce</a>
        <button class="drawer-close" aria-label="Close menu">Close ✕</button>
    </div>

    <!-- Categories first. Tapping a category only expands/collapses its subcategories
         (no navigation) unless it has no subcategories, in which case it links straight
         to that category's products. -->
    @foreach($navCategories as $cat)
        @if($cat->subcategories->count())
            <details style="margin: 2px 0;">
                <summary class="cat-summary" style="cursor:pointer; padding:8px 0; color:inherit;">
                    {{ $cat->name }}
                    <svg class="cat-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div style="padding-left: 16px;">
                    @foreach($cat->subcategories as $sub)
                        @php
                            $onlyProduct = $sub->products->count() === 1 ? $sub->products->first() : null;
                            $subLink = $onlyProduct
                                ? url('/product/' . $onlyProduct->id)
                                : url('products') . '?subcategory_id=' . $sub->id;
                        @endphp
                        <a href="{{ $subLink }}" style="display:block; padding:6px 0; opacity:.85;">{{ $sub->name }}</a>
                    @endforeach
                </div>
            </details>
        @else
            <a href="{{ url('products') }}?category_id={{ $cat->id }}" style="display:block; padding:8px 0;">{{ $cat->name }}</a>
        @endif
    @endforeach

    <hr style="margin: var(--s4, 16px) 0; border: none; border-top: 1px solid rgba(0,0,0,.08);">

    <!-- Menu -->
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

<script>
    document.addEventListener('DOMContentLoaded', function () {

        var dropdowns = document.querySelectorAll('.dropdown');

        // Click-to-toggle dropdowns: the "Products" mega menu and the admin "Menu" link.
        // Category links keep opening via CSS :hover, unchanged.
        var clickDropdowns = [
            document.getElementById('productsMega'),
            document.getElementById('menuDropdown')
        ].filter(Boolean);

        clickDropdowns.forEach(function (dropdown) {
            var toggle = dropdown.querySelector(':scope > .dropdown-toggle');

            toggle.addEventListener('click', function (e) {
                e.stopPropagation();

                // Close any other click-toggled dropdown before opening this one
                clickDropdowns.forEach(function (other) {
                    if (other !== dropdown) {
                        other.classList.remove('open');
                        var otherToggle = other.querySelector(':scope > .dropdown-toggle');
                        if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
                    }
                });

                var isOpen = dropdown.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function (e) {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    dropdown.classList.remove('open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });

        // Flip any dropdown (category or Menu) left if its panel would overflow the right edge
        dropdowns.forEach(function (dropdown) {
            var panel = dropdown.querySelector(':scope > .dropdown-menu');
            if (!panel) return;

            dropdown.addEventListener('mouseenter', checkOverflow);
            dropdown.addEventListener('click', checkOverflow);

            function checkOverflow() {
                dropdown.classList.remove('flip');
                var rect = dropdown.getBoundingClientRect();
                var panelWidth = panel.offsetWidth || 220;
                if (rect.left + panelWidth + 12 > window.innerWidth) {
                    dropdown.classList.add('flip');
                }
            }
        });

        /* ---------------------------------------------------------------
         | Live search suggestions
         | Typing in the header search box shows matching subcategories
         | (→ /products?subcategory_id=X) and products (→ /product/X).
         * -------------------------------------------------------------*/
        var searchWrap  = document.getElementById('searchWrap');
        var searchInput = document.getElementById('searchInput');
        var suggBox     = document.getElementById('searchSuggestions');

        if (searchWrap && searchInput && suggBox) {
            var debounceTimer = null;
            var currentController = null; // AbortController for in-flight fetch
            var activeIndex = -1;

            function closeSuggestions() {
                suggBox.classList.remove('open');
                suggBox.innerHTML = '';
                activeIndex = -1;
            }

            function escapeHtml(str) {
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function renderSuggestions(query, data) {
                var subs = data.subcategories || [];
                var prods = data.products || [];

                if (subs.length === 0 && prods.length === 0) {
                    suggBox.innerHTML = '<div class="sugg-empty">No matches for "' + escapeHtml(query) + '"</div>';
                    suggBox.classList.add('open');
                    return;
                }

                var html = '';

                if (subs.length) {
                    html += '<div class="group-label">Categories</div>';
                    subs.forEach(function (s) {
                        html += ''
                            + '<a class="sugg-row" href="' + s.url + '" role="option">'
                            +   '<span class="sugg-icon">'
                            +     '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v16H4z" opacity="0"/><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>'
                            +   '</span>'
                            +   '<span class="sugg-text">'
                            +     '<div class="sugg-name">' + escapeHtml(s.name) + '</div>'
                            +     (s.category_name ? '<div class="sugg-meta">in ' + escapeHtml(s.category_name) + '</div>' : '')
                            +   '</span>'
                            + '</a>';
                    });
                }

                if (prods.length) {
                    html += '<div class="group-label">Products</div>';
                    prods.forEach(function (p) {
                        var thumb = p.image
                            ? '<img class="sugg-thumb" src="' + p.image + '" alt="" />'
                            : '<span class="sugg-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2l-2 5v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7l-2-5z"/><path d="M4 7h16"/><path d="M16 11a4 4 0 0 1-8 0"/></svg></span>';

                        html += ''
                            + '<a class="sugg-row" href="' + p.url + '" role="option">'
                            +   thumb
                            +   '<span class="sugg-text">'
                            +     '<div class="sugg-name">' + escapeHtml(p.name) + '</div>'
                            +     (p.price ? '<div class="sugg-meta">₹ ' + Number(p.price).toLocaleString('en-IN') + '</div>' : '')
                            +   '</span>'
                            + '</a>';
                    });
                }

                html += ''
                    + '<a class="sugg-row sugg-viewall" href="' + searchInput.form.action + '?search=' + encodeURIComponent(query) + '">'
                    +   'See all results for "' + escapeHtml(query) + '" →'
                    + '</a>';

                suggBox.innerHTML = html;
                suggBox.classList.add('open');
            }

            searchInput.addEventListener('input', function () {
                var query = searchInput.value.trim();

                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    closeSuggestions();
                    return;
                }

                debounceTimer = setTimeout(function () {
                    if (currentController) {
                        currentController.abort();
                    }
                    currentController = new AbortController();

                    fetch('{{ route('search.suggest') }}?q=' + encodeURIComponent(query), {
                        signal: currentController.signal,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then(function (res) { return res.json(); })
                        .then(function (data) { renderSuggestions(query, data); })
                        .catch(function (err) {
                            if (err.name !== 'AbortError') {
                                closeSuggestions();
                            }
                        });
                }, 250);
            });

            searchInput.addEventListener('keydown', function (e) {
                var rows = suggBox.querySelectorAll('.sugg-row');
                if (!rows.length || !suggBox.classList.contains('open')) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = Math.min(activeIndex + 1, rows.length - 1);
                    updateActive(rows);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, 0);
                    updateActive(rows);
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && rows[activeIndex]) {
                        e.preventDefault();
                        window.location.href = rows[activeIndex].getAttribute('href');
                    }
                } else if (e.key === 'Escape') {
                    closeSuggestions();
                }
            });

            function updateActive(rows) {
                rows.forEach(function (row, i) {
                    row.classList.toggle('active', i === activeIndex);
                });
                if (rows[activeIndex]) {
                    rows[activeIndex].scrollIntoView({ block: 'nearest' });
                }
            }

            document.addEventListener('click', function (e) {
                if (!searchWrap.contains(e.target)) {
                    closeSuggestions();
                }
            });
        }
    });
</script>