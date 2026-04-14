
<!DOCTYPE html>
<html>
<head>
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <x-layout></x-layout>
<h2 class="mb-4">🛒 Cart List</h2>
<div class="container">
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th width="180">Quantity</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>

        @php $grandTotal = 0; @endphp

        @forelse($carts as $item)
            @php 
                $total = $item->product->price * $item->quantity;
                $grandTotal += $total;
            @endphp

            <tr>
                <td>{{ $item->product->name ?? 'Product' }}</td>
                <td>₹ {{ $item->product->price }}</td>

                <td>
                    <div class="d-flex justify-content-center">

                        {{-- Minus --}}
                        <form action="{{ url('add_quantity') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <input type="hidden" name="action" value="minus">
                            <button class="btn btn-danger btn-sm">-</button>
                        </form>

                        <span class="mx-2">{{ $item->quantity }}</span>

                        {{-- Plus --}}
                        <form action="{{ url('add_quantity') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <input type="hidden" name="action" value="add">
                            <button class="btn btn-success btn-sm">+</button>
                        </form>

                    </div>
                </td>

                <td>₹ {{ $total }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="4">Cart is empty 😢</td>
            </tr>
        @endforelse

        </tbody>
    </table>

    {{-- Grand Total --}}
    <div class="text-end">
        <h4>Total: ₹ {{ $grandTotal }}</h4>
    </div>

    {{-- Order Button --}}
    @if(count($carts) > 0)
        <div class="text-end mt-3">
            <form action="order" method="POST">
                @csrf
            <button name="submit" class="btn btn-primary">
                Place Order 🚀
            </button>
            </form>
        </div>
    @endif
</div>
</body>
</html>