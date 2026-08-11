<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Razorpay Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="text-center">
        <h4 class="mb-4">Redirecting to Razorpay Checkout...</h4>
        <p class="text-muted">Please wait, do not close this window.</p>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = {
        "key": "{{ $key }}",
        "amount": "{{ (int) round($amount * 100) }}",
        "currency": "INR",
        "name": "Your Store Name",
        "description": "Order Payment",
        "order_id": "{{ $razorpay_order_id }}",
        "handler": function (response) {
            // Payment succeeded on Razorpay's side, verify server-side
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('razorpay.verify') }}";

            var fields = {
                '_token': '{{ csrf_token() }}',
                'razorpay_payment_id': response.razorpay_payment_id,
                'razorpay_order_id': response.razorpay_order_id,
                'razorpay_signature': response.razorpay_signature
            };

            for (var key in fields) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        },
        "prefill": {
            "name": "{{ $user->name }}",
            "email": "{{ $user->email }}"
        },
        "theme": {
            "color": "#667eea"
        },
        "modal": {
            "ondismiss": function () {
                window.location.href = "{{ url('/cart') }}";
            }
        }
    };

    var rzp = new Razorpay(options);
    rzp.open();
</script>

</body>
</html>