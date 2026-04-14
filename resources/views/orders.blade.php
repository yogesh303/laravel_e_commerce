
<!DOCTYPE html>
<html>
<head>
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>
<h2 class="mb-4">📦 Orders List</h2>

<div class="container">

<form action="delete_all" method="POST">
    @csrf

    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>Total</th>
                <th>Date</th>
                <th>Items</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        @foreach($orders as $order)
        <tr>
            <td>
                <input type="checkbox" name="id[]" value="{{ $order->id }}">
            </td>

            <td>{{ $order->id }}</td>

            <td>₹ {{ $order->total_price }}</td>

            <td>{{ $order->created_at }}</td>

            <td>
                {{-- Order Items --}}
                <table class="table table-sm table-bordered">
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                    </tr>

                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>₹ {{ $item->price }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>

            <td>
                <a href="delete_order/{{ $order->id }}" class="btn btn-danger btn-sm">Delete</a>
            </td>
        </tr>
        @endforeach

        </tbody>
    </table>

</form>

</div>
</body>
</html>