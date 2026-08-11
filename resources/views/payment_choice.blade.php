<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Choose Payment Method</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-5" style="max-width: 500px;">
    <h3 class="text-center mb-4">Choose Payment Method</h3>

    <div class="d-grid gap-3">
        <a href="{{ url('/checkout') }}" class="btn btn-dark btn-lg">
            Pay with Stripe
        </a>

        <a href="{{ route('checkout.razorpay') }}" class="btn btn-primary btn-lg">
            Pay with Razorpay
        </a>
    </div>
</div>

</body>
</html>