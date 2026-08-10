<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>

<h2 class="mb-4 mt-2 text-center">Cart List</h2>

<div class="container">

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>Image</th>
                <th>Product</th>
                <th>Options</th>
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
            <td>
                @if(!empty($item->custom_images) && count($item->custom_images))
                    <div class="d-flex gap-1 flex-wrap justify-content-center">
                        @foreach($item->custom_images as $img)
                            <img src="{{ asset('uploads/customizations/' . $img) }}"
                                style="width:55px;height:55px;object-fit:cover;"
                                class="rounded border">
                        @endforeach
                    </div>
                    <div><span class="badge bg-info text-dark mt-1">Customized ({{ count($item->custom_images) }})</span></div>

                @elseif($item->custom_image)
                    <img src="{{ asset('uploads/customizations/' . $item->custom_image) }}"
                        style="width:70px;height:70px;object-fit:cover;"
                        class="rounded border">
                    <div><span class="badge bg-info text-dark mt-1">Customized</span></div>

                @else
                    <img src="{{ asset('images/' . $item->product->image) }}"
                        style="width:70px;height:70px;object-fit:cover;"
                        class="rounded border">
                @endif
            </td>

                <td>{{ $item->product->name ?? 'Product' }}</td>

                <td>
                    @if($item->selected_options && count($item->selected_options))
                        @foreach($item->selected_options as $optName => $optValue)
                            <div><small><strong>{{ $optName }}:</strong> {{ $optValue }}</small></div>
                        @endforeach
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </td>

                <td>₹ {{ $item->product->price }}</td>

                <td>
                    <div class="d-flex justify-content-center align-items-center">

                        <form action="{{ url('add_quantity') }}" method="POST">
                            @csrf
                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                            <input type="hidden" name="action" value="minus">
                            <button class="btn btn-danger btn-sm">-</button>
                        </form>

                        <span class="mx-2">{{ $item->quantity }}</span>

                        <form action="{{ url('add_quantity') }}" method="POST">
                            @csrf
                            <input type="hidden" name="cart_item_id" value="{{ $item->id }}">
                            <input type="hidden" name="action" value="add">
                            <button class="btn btn-success btn-sm">+</button>
                        </form>

                    </div>
                </td>

                <td>₹ {{ $total }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="6">Cart is empty 😢</td>
            </tr>
        @endforelse

        </tbody>
    </table>

    <div class="text-end">
        <h4>Total: ₹ {{ $grandTotal }}</h4>
    </div>

    @if(count($carts) > 0)
        <div class="text-end mt-3">
            <a href="{{ url('checkout') }}" class="btn btn-success">
                Pay with Stripe
            </a>
        </div>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>