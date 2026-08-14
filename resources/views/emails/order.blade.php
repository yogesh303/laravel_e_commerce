<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order #{{ $order->id }}</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222; background:#f4f4f4; padding:20px;">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:650px; margin:auto; background:#fff; border-radius:6px; overflow:hidden;">

        <tr>
            <td style="background:#212529; color:#fff; padding:20px;">
                <h2 style="margin:0;">New Order Received</h2>
            </td>
        </tr>

        <tr>
            <td style="padding:20px;">
                <p style="margin:4px 0;"><strong>Order ID:</strong> #{{ $order->id }}</p>
                <p style="margin:4px 0;"><strong>Customer:</strong> {{ $order->user->name }}</p>
                <p style="margin:4px 0;"><strong>Email:</strong> {{ $order->user->email }}</p>
                <p style="margin:4px 0;"><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
                <p style="margin:4px 0;"><strong>Date:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</p>
            </td>
        </tr>

        <tr><td style="border-top:1px solid #eee;"></td></tr>

        <tr>
            <td style="padding:20px;">
                <h3 style="margin-top:0;">Order Products</h3>

                @foreach($order->items as $item)
                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                        <tr>
                            <td width="140" valign="top">
                                @if(!empty($item->custom_images) && count($item->custom_images))
                                    @foreach($item->custom_images as $img)
                                        @php $imgPath = public_path('uploads/customizations/'.$img); @endphp
                                        @if(file_exists($imgPath))
                                            <img src="{{ $message->embed($imgPath) }}"
                                                 style="max-width:120px; max-height:120px; object-fit:contain; border:1px solid #ddd; border-radius:4px; margin:2px;">
                                        @endif
                                    @endforeach
                                @elseif($item->custom_image)
                                    @php $imgPath = public_path('uploads/customizations/'.$item->custom_image); @endphp
                                    @if(file_exists($imgPath))
                                        <img src="{{ $message->embed($imgPath) }}"
                                             style="max-width:130px; border:1px solid #ddd; border-radius:4px;">
                                    @endif
                                @elseif($item->product && $item->product->image)
                                    @php $imgPath = public_path('images/'.$item->product->image); @endphp
                                    @if(file_exists($imgPath))
                                        <img src="{{ $message->embed($imgPath) }}"
                                             style="max-width:130px; border:1px solid #ddd; border-radius:4px;">
                                    @endif
                                @else
                                    <span style="color:#999;">No Image</span>
                                @endif
                                @if(!empty($item->logo_images) && count(array_filter($item->logo_images)))
                                    <p style="margin:8px 0 4px; font-size:13px; font-weight:bold;">Uploaded Logo(s):</p>
                                    <div>
                                        @foreach($item->logo_images as $logo)
                                            @if($logo)
                                                @php $logoPath = public_path('uploads/logos/'.$logo); @endphp
                                                @if(file_exists($logoPath))
                                                    <img src="{{ $message->embed($logoPath) }}"
                                                        style="max-width:90px; max-height:90px; object-fit:contain; border:1px solid #ddd; border-radius:4px; margin:2px; background:#fff;">
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            <td valign="top" style="padding-left:15px;">
                                <h4 style="margin:0 0 8px;">{{ $item->product->name ?? 'Product Deleted' }}</h4>

                                @if(!empty($item->selected_options) && count($item->selected_options))
                                    <p style="margin:2px 0; font-size:14px;">
                                        @foreach($item->selected_options as $optName => $optValue)
                                            <strong>{{ $optName }}:</strong> {{ $optValue }}&nbsp;&nbsp;
                                        @endforeach
                                    </p>
                                @endif

                                @if(!empty($item->size_breakdown) && count($item->size_breakdown))
                                    <p style="margin:2px 0; font-size:14px;">
                                        <strong>Size-wise Quantity:</strong><br>
                                        @foreach($item->size_breakdown as $size => $qty)
                                            {{ $size }}: {{ $qty }}&nbsp;&nbsp;
                                        @endforeach
                                    </p>
                                @endif

                                @if($item->custom_image || (!empty($item->custom_images) && count($item->custom_images)))
                                    <p style="margin:6px 0; color:#0d6efd; font-weight:bold;">Customized Product</p>
                                @endif

                                <p style="margin:2px 0;">
                                    <strong>Quantity:</strong> {{ $item->quantity }}
                                    @if($item->tier_qty)
                                        <span style="color:#777;">
                                            ({{ $item->order_quantity ?? ($item->quantity * $item->tier_qty) }} pcs total —
                                            {{ $item->tier_qty }} pcs/batch)
                                        </span>
                                    @elseif($item->order_quantity)
                                        <span style="color:#777;">
                                            ({{ $item->order_quantity }} pcs total)
                                        </span>
                                    @endif
                                </p>

                                <p style="margin:2px 0;"><strong>Price:</strong> ₹ {{ number_format($item->price, 2) }}</p>
                                <p style="margin:2px 0;"><strong>Item Total:</strong> ₹ {{ number_format($item->price * $item->quantity, 2) }}</p>
                            </td>
                        </tr>
                    </table>
                @endforeach
            </td>
        </tr>

        <tr><td style="border-top:1px solid #eee;"></td></tr>

        @if($order->shipping_name)
        <tr>
            <td style="padding:20px;">
                <h3 style="margin-top:0;">Shipping Address</h3>
                @if($order->shipping_company)
                    <p style="margin:2px 0;"><strong>Company:</strong> {{ $order->shipping_company }}</p>
                @endif
                @if($order->shipping_gst_no)
                    <p style="margin:2px 0;"><strong>GST No:</strong> {{ $order->shipping_gst_no }}</p>
                @endif
                <p style="margin:2px 0;"><strong>Name:</strong> {{ $order->shipping_name }}</p>
                <p style="margin:2px 0;"><strong>Phone:</strong> {{ $order->shipping_phone }}</p>
                <p style="margin:2px 0;">
                    <strong>Address:</strong>
                    {{ $order->shipping_address_line1 }}
                    @if($order->shipping_address_line2), {{ $order->shipping_address_line2 }}@endif,
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}, {{ $order->shipping_country }}
                </p>
            </td>
        </tr>
        <tr><td style="border-top:1px solid #eee;"></td></tr>
        @endif

        <tr>
            <td style="padding:20px; text-align:right;">
                <h3 style="margin:0; color:#198754;">
                    Grand Total: ₹ {{ number_format($order->total_price, 2) }}
                </h3>
            </td>
        </tr>

    </table>

</body>
</html>