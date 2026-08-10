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

            @if(!empty($item->selected_options) && count($item->selected_options))
                <p>
                    @foreach($item->selected_options as $optName => $optValue)
                        <strong>{{ $optName }}:</strong> {{ $optValue }}&nbsp;&nbsp;
                    @endforeach
                </p>
            @endif

            @if(!empty($item->custom_images) && count($item->custom_images))

                <p>
                    <strong>Customized Product ({{ count($item->custom_images) }} image(s)):</strong>
                </p>

                @foreach($item->custom_images as $img)
                    <img
                        src="{{ asset('uploads/customizations/' . $img) }}"
                        style="max-width:300px; border:1px solid #ddd; margin:5px;"
                    >
                @endforeach

            @elseif($item->custom_image)

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