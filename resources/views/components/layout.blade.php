<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        {{-- Logo --}}
        <a class="navbar-brand" href="{{ url('products') }}">
            🛒 E-Commerce
        </a>

        {{-- Toggle (mobile) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            {{-- Left Side --}}
            <ul class="navbar-nav me-auto">

                {{-- All Users --}}
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('products') }}">Products</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('cart_items') }}">Cart 🛒</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('orders') }}">My Orders 📦</a>
                </li>

                {{-- Admin Only --}}
                @if(Auth::user() && Auth::user()->role == 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('product_list') }}">Product List</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('product_form') }}">Add Product</a>
                    </li>
                @endif

            </ul>

            {{-- Right Side --}}
            <ul class="navbar-nav">

                <li class="nav-item">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-sm">Logout</button>
                    </form>
                </li>

            </ul>

        </div>
    </div>
</nav>