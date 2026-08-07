<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order #{{ $order->id }}</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body>

<div class="container py-5">

    {{-- Back Button --}}
    <div class="mb-4">

        <a href="{{ url('/orders') }}"
           class="btn btn-secondary">

            ← Back to Orders

        </a>

    </div>


    {{-- Order Header --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <h3>
                        Order #{{ $order->id }}
                    </h3>

                    <p class="mb-1">
                        <strong>Customer:</strong>
                        {{ $order->user->name }}
                    </p>

                    <p class="mb-1">
                        <strong>Email:</strong>
                        {{ $order->user->email }}
                    </p>

                </div>

                <div class="col-md-6 text-md-end">

                    <p class="mb-1">

                        <strong>Status:</strong>

                        <span class="badge bg-success">
                            {{ ucfirst($order->status) }}
                        </span>

                    </p>

                    <p class="mb-1">

                        <strong>Date:</strong>

                        {{ $order->created_at->format('d-m-Y H:i') }}

                    </p>

                    <h4 class="text-success mt-3">

                        ₹ {{ number_format($order->total_price, 2) }}

                    </h4>

                </div>

            </div>

        </div>

    </div>


    {{-- Products --}}
    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">
                Order Products
            </h5>

        </div>


        <div class="card-body">

            @foreach($order->items as $item)

                <div class="row align-items-center border-bottom py-4">

                    {{-- Image --}}
                    <div class="col-md-3 text-center">

                        @if($item->custom_image)

                            {{-- Customized Image --}}
                            <img
                                src="{{ asset('uploads/customizations/'.$item->custom_image) }}"
                                class="img-fluid rounded border"
                                style="max-height:250px; object-fit:contain;"
                                alt="Customized Product">

                            <div class="mt-2">

                                <span class="badge bg-primary">
                                    Customized
                                </span>

                            </div>

                        @elseif($item->product && $item->product->image)

                            {{-- Normal Product Image --}}
                            <img
                                src="{{ asset('images/'.$item->product->image) }}"
                                class="img-fluid rounded border"
                                style="max-height:250px; object-fit:contain;"
                                alt="{{ $item->product->name }}">

                        @else

                            <div class="text-muted">
                                No Image
                            </div>

                        @endif

                    </div>


                    {{-- Product Details --}}
                    <div class="col-md-6">

                        <h4>

                            {{ $item->product->name ?? 'Product Deleted' }}

                        </h4>


                        @if($item->custom_image)

                            <p class="text-primary">

                                <strong>
                                    Customized Product
                                </strong>

                            </p>

                            <p class="text-muted">

                                This is the customized image uploaded
                                by the customer.

                            </p>

                        @endif


                        <p class="mb-1">

                            <strong>
                                Quantity:
                            </strong>

                            {{ $item->quantity }}

                        </p>


                        <p class="mb-1">

                            <strong>
                                Price:
                            </strong>

                            ₹ {{ number_format($item->price, 2) }}

                        </p>


                        <p class="mb-1">

                            <strong>
                                Item Total:
                            </strong>

                            ₹ {{ number_format($item->price * $item->quantity, 2) }}

                        </p>

                    </div>


                    {{-- Total --}}
                    <div class="col-md-3 text-md-end">

                        <h5 class="text-success">

                            ₹ {{ number_format($item->price * $item->quantity, 2) }}

                        </h5>

                    </div>

                </div>

            @endforeach

        </div>

    </div>


    {{-- Grand Total --}}
    <div class="card shadow-sm mt-4">

        <div class="card-body text-end">

            <h3>

                Grand Total:

                <span class="text-success">

                    ₹ {{ number_format($order->total_price, 2) }}

                </span>

            </h3>

        </div>

    </div>

</div>

</body>

</html>