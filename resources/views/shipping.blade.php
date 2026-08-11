<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shipping Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>

<div class="container py-4" style="max-width: 600px;">
    <h3 class="mb-4 text-center">Shipping Details</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <p class="mb-1">{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('shipping.save') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="shipping_name" class="form-control" value="{{ old('shipping_name', $saved['shipping_name'] ?? auth()->user()->name) }}" required>
        </div>

        <div class="mb-3">
            <label>Phone Number</label>
            <input type="text" name="shipping_phone" class="form-control" value="{{ old('shipping_phone', $saved['shipping_phone'] ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label>Address Line 1</label>
            <input type="text" name="shipping_address_line1" class="form-control" value="{{ old('shipping_address_line1', $saved['shipping_address_line1'] ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label>Address Line 2 (optional)</label>
            <input type="text" name="shipping_address_line2" class="form-control" value="{{ old('shipping_address_line2', $saved['shipping_address_line2'] ?? '') }}">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>City</label>
                <input type="text" name="shipping_city" class="form-control" value="{{ old('shipping_city', $saved['shipping_city'] ?? '') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>State</label>
                <input type="text" name="shipping_state" class="form-control" value="{{ old('shipping_state', $saved['shipping_state'] ?? '') }}" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Pincode</label>
                <input type="text" name="shipping_pincode" class="form-control" value="{{ old('shipping_pincode', $saved['shipping_pincode'] ?? '') }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Country</label>
                <input type="text" name="shipping_country" class="form-control" value="{{ old('shipping_country', $saved['shipping_country'] ?? 'India') }}" required>
            </div>
        </div>

        <div class="d-grid mt-4">
            <button class="btn btn-success">Continue to Payment</button>
        </div>
    </form>
</div>

</body>
</html>