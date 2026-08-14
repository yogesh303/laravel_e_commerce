<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Order #{{ $order->id }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .zoomable-img {
            cursor: zoom-in;
            transition: transform 0.15s ease;
        }
        .zoomable-img:hover {
            transform: scale(1.03);
        }
    </style>

</head>

<body>

<div class="container py-5">

    {{-- Back Button + Invoice Number trigger --}}
    <div class="mb-4 d-flex justify-content-between align-items-center">

        <a href="{{ url('/orders') }}"
           class="btn btn-secondary">

            ← Back to Orders

        </a>

        <div>
            @if($order->invoice_no)
                <span class="badge bg-dark me-2 align-middle" style="font-size: 14px;">
                    Invoice No: {{ $order->invoice_no }}
                </span>
            @endif

            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#invoiceNoModal">
                ✏️ {{ $order->invoice_no ? 'Edit' : 'Set' }} Invoice Number
            </button>
        </div>

    </div>

    {{-- Set / Edit Invoice Number Modal --}}
    <div class="modal fade" id="invoiceNoModal" tabindex="-1" aria-labelledby="invoiceNoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('order.invoice.setnumber', $order->id) }}" method="POST">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="invoiceNoModalLabel">Set Invoice Number</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Invoice Number</label>
                        <input
                            type="text"
                            name="invoice_no"
                            class="form-control"
                            value="{{ $order->invoice_no }}"
                            placeholder="e.g. INV-2026-0001"
                            maxlength="50"
                            required
                            autofocus>
                        <div class="form-text">This number will appear on the printed invoice for this order.</div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- Full-Image Preview Modal (shared by every clickable image) --}}
    <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark">
                <div class="modal-header border-0">
                    <h6 class="modal-title text-white" id="imagePreviewLabel">Preview</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="imagePreviewSrc" src="" class="img-fluid rounded" style="max-height:75vh;">
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <a id="imagePreviewDownload" href="" download class="btn btn-success">
                        ⬇ Download Image
                    </a>
                </div>
            </div>
        </div>
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

                        @if(!empty($item->custom_images) && count($item->custom_images))

                            {{-- All Customized Images (front, back, etc.) --}}
                            <div class="d-flex flex-wrap gap-2 justify-content-center">
                                @foreach($item->custom_images as $img)
                                    <img
                                        src="{{ asset('uploads/customizations/'.$img) }}"
                                        class="img-fluid rounded border zoomable-img"
                                        style="max-height:120px; max-width:120px; object-fit:contain;"
                                        alt="Customized Product"
                                        onclick="showImagePreview(this.src, 'Customized Product')">
                                @endforeach
                            </div>

                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    Customized ({{ count($item->custom_images) }})
                                </span>
                            </div>

                        @elseif($item->custom_image)

                            {{-- Customized Image (legacy single-image rows) --}}
                            <img
                                src="{{ asset('uploads/customizations/'.$item->custom_image) }}"
                                class="img-fluid rounded border zoomable-img"
                                style="max-height:250px; object-fit:contain;"
                                alt="Customized Product"
                                onclick="showImagePreview(this.src, 'Customized Product')">

                            <div class="mt-2">
                                <span class="badge bg-primary">
                                    Customized
                                </span>
                            </div>

                        @elseif($item->product && $item->product->image)

                            {{-- Normal Product Image --}}
                            <img
                                src="{{ asset('images/'.$item->product->image) }}"
                                class="img-fluid rounded border zoomable-img"
                                style="max-height:250px; object-fit:contain;"
                                alt="{{ $item->product->name }}"
                                onclick="showImagePreview(this.src, '{{ $item->product->name }}')">

                        @else

                            <div class="text-muted">
                                No Image
                            </div>

                        @endif

                        {{-- Uploaded Logo(s) --}}
                        @if(!empty($item->logo_images) && count(array_filter($item->logo_images)))
                            <div class="mt-3">
                                <strong class="d-block mb-1 small text-muted">Uploaded Logo(s):</strong>
                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    @foreach($item->logo_images as $logo)
                                        @if($logo)
                                            <img
                                                src="{{ asset('uploads/logos/'.$logo) }}"
                                                class="img-fluid rounded border zoomable-img"
                                                style="max-height:90px; max-width:90px; object-fit:contain; background:#fff;"
                                                alt="Uploaded Logo"
                                                onclick="showImagePreview(this.src, 'Uploaded Logo')">
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>


                    {{-- Product Details --}}
                    <div class="col-md-6">

                        <h4>
                            {{ $item->product->name ?? 'Product Deleted' }}
                        </h4>

                        @if(!empty($item->selected_options) && count($item->selected_options))
                            <div class="mb-2">
                                @foreach($item->selected_options as $optName => $optValue)
                                    <span class="badge bg-light text-dark border me-1">{{ $optName }}: {{ $optValue }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($item->size_breakdown) && count($item->size_breakdown))
                            <div class="mb-2">
                                <strong>Size-wise Quantity:</strong><br>
                                @foreach($item->size_breakdown as $size => $qty)
                                    <span class="badge bg-secondary me-1">{{ $size }}: {{ $qty }}</span>
                                @endforeach
                            </div>
                        @endif


                        @if($item->custom_image || (!empty($item->custom_images) && count($item->custom_images)))

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

                            @if($item->tier_qty)
                                <span class="text-muted">
                                    ({{ $item->quantity * $item->tier_qty }} pcs total —
                                    {{ $item->tier_qty }} pcs/batch)
                                </span>
                            @endif

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

    {{-- Print Invoice --}}
    <div class="text-end mt-3">
        <a href="{{ route('order.invoice', $order->id) }}"
           target="_blank"
           class="btn btn-outline-dark">
            🧾 Print Invoice
        </a>
    </div>


    {{-- Shipping Address --}}
    <div class="card shadow-sm mb-4 mt-3">

        <div class="card-header bg-dark text-white">

            <h5 class="mb-0">
                Shipping Address
            </h5>

        </div>

        <div class="card-body">

            @if($order->shipping_name)

                <div class="row">

                    <div class="col-md-6">
                        @if($order->shipping_gst_no)
                            <p class="mb-1">
                                <strong>GST No:</strong>
                                {{ $order->shipping_gst_no }}
                            </p>
                        @endif
                        @if($order->shipping_company)
                            <p class="mb-1">
                                <strong>Company Name:</strong>
                                {{ $order->shipping_company }}
                            </p>
                        @endif

                        <p class="mb-1">
                            <strong>Name:</strong>
                            {{ $order->shipping_name }}
                        </p>

                        <p class="mb-1">
                            <strong>Phone:</strong>
                            {{ $order->shipping_phone }}
                        </p>

                        <p class="mb-1">
                            <strong>Address:</strong>
                            {{ $order->shipping_address_line1 }}
                            @if($order->shipping_address_line2)
                                , {{ $order->shipping_address_line2 }}
                            @endif
                        </p>

                    </div>

                    <div class="col-md-6">

                        <p class="mb-1">
                            <strong>City:</strong>
                            {{ $order->shipping_city }}
                        </p>

                        <p class="mb-1">
                            <strong>State:</strong>
                            {{ $order->shipping_state }}
                        </p>

                        <p class="mb-1">
                            <strong>Pincode:</strong>
                            {{ $order->shipping_pincode }}
                        </p>

                        <p class="mb-1">
                            <strong>Country:</strong>
                            {{ $order->shipping_country }}
                        </p>

                    </div>

                </div>

            @else

                <p class="text-muted mb-0">No shipping address on file for this order.</p>

            @endif

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function showImagePreview(src, label) {
        document.getElementById('imagePreviewSrc').src = src;
        document.getElementById('imagePreviewLabel').textContent = label || 'Preview';

        const downloadBtn = document.getElementById('imagePreviewDownload');
        downloadBtn.href = src;

        // Give the downloaded file a sensible name based on the label
        const safeName = (label || 'image').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        const extMatch = src.match(/\.(jpe?g|png|webp|gif)(\?.*)?$/i);
        const ext = extMatch ? extMatch[1] : 'jpg';
        downloadBtn.download = safeName + '.' + ext;

        const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        modal.show();
    }
</script>

</body>

</html>