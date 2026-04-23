@php
    $user_role = Auth::user()->role;
    $user_email = Auth::user()->email;
@endphp
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="{{ url('dashboard') }}">
            🛒 E-Commerce
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav me-auto">

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('products') }}">Products</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('cart_items') }}">My Cart</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="{{ url('orders') }}">All Orders</a>
                </li>

                @if(Auth::user() && Auth::user()->role == 'admin')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('product_list') }}">Product List</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('product_form') }}">Add Product</a>
                    </li>
                @endif

            </ul>

            <ul class="navbar-nav">

                <li class="nav-item">
                    <li class="nav-item">
                        <a class="nav-link" href="#">{{$user_email}}</a>
                    </li>
                </li>
                <li class="nav-item" style="padding-top: 4px;">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button class="btn btn-danger btn-sm">Logout</button>
                    </form>
                </li>

            </ul>

        </div>
    </div>
</nav>