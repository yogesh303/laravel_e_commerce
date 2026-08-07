<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>New Order</title>
</head>

<body>

    <h2>New Order Received</h2>

    <p>
        Order ID:
        <strong>#{{ $order->id }}</strong>
    </p>

    <p>
        Customer:
        <strong>{{ $order->user->name }}</strong>
    </p>

    <p>
        Email:
        <strong>{{ $order->user->email }}</strong>
    </p>

    <hr>

    <h3>Order Products</h3>

    @foreach($order->items as $item)

        <div style="margin-bottom:30px;">

            <h4>
                {{ $item->product->name }}
            </h4>

            <p>
                Quantity:
                {{ $item->quantity }}
            </p>

            <p>
                Price:
                ₹ {{ $item->price }}
            </p>


            @if($item->custom_image)

                <p>
                    <strong>Customized Product:</strong>
                </p>

                <img
                    src="{{ asset('uploads/customizations/' . $item->custom_image) }}"
                    style="max-width:400px; border:1px solid #ddd;"
                >

            @else

                <img
                    src="{{ asset('images/' . $item->product->image) }}"
                    style="max-width:400px; border:1px solid #ddd;"
                >

            @endif

        </div>

        <hr>

    @endforeach


    <h3>
        Total:
        ₹ {{ $order->total_price }}
    </h3>

</body>

</html>