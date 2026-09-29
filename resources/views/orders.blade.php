<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      html, body { max-width: 100%; overflow-x: hidden; }
      .table-responsive table { min-width: 720px; } /* keeps columns from crushing; scrolls inside wrapper instead of page */
      .table-responsive td, .table-responsive th { white-space: nowrap; }
      .table-responsive td:nth-child(6) { white-space: normal; } /* Items column keeps wrapping for nested table */
      .nested-items table { min-width: 0; width: 100%; }
      .nested-items th, .nested-items td { white-space: normal; }
    </style>
</head>
<body>
<x-layout></x-layout>
<h2 class="mb-4 mt-2 text-center">Orders List</h2>

<div class="container">
@if(session('success'))
    <div class="alert alert-success text-center">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger text-center">
        {{ session('error') }}
    </div>
@endif
@php
    $user_role = Auth::user()->role;
@endphp

<form action="delete_all" method="POST">
    @csrf

    <div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Order ID</th>
                <th>user</th>
                <th>Total</th>
                <th>Date</th>
                <th>Items</th>
                @if ($user_role === 'admin')
                    <th>Action</th>
                @else
                    <th>View</th>
                @endif
            </tr>
        </thead>

        <tbody>

        @foreach($orders as $order)
        <tr>
            <td>
                <input type="checkbox" name="id[]" value="{{ $order->id }}">
            </td>

            <td>{{ $order->id }}</td>

            <td>{{ $order->user->name }}</td>

            <td>₹ {{ $order->total_price }}</td>

            <td>{{ $order->created_at->format('d-m-Y') }}</td>

            <td>
                <div class="nested-items">
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
                </div>
            </td>
            <td>
                <a href="{{ route('order.view', $order->id) }}"
                class="btn btn-primary btn-sm">
                    View Order
                </a><br>

                @if ($user_role === 'admin')
                    <a href="{{ url('delete_order/'.$order->id) }}"
                    class="btn btn-danger btn-sm">
                        Delete
                    </a>
                @endif
            </td>
        </tr>
        @endforeach

        </tbody>
    </table>
    </div>

</form>

</div>
<x-footer></x-footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>