<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <x-layout></x-layout>

    <div class="container py-4">
        <h2 class="mb-4">Application Settings</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}">
            @csrf

            {{-- Stripe --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">Stripe</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Stripe Key</label>
                            <input type="text" name="stripe_key" class="form-control"
                                value="{{ old('stripe_key', $settings['stripe_key']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stripe Secret</label>
                            <input type="password" name="stripe_secret" class="form-control"
                                placeholder="{{ $settings['stripe_secret'] ? '••••••••••••' : '' }}">
                            <div class="form-text">Leave blank to keep current secret.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Razorpay --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">Razorpay</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Razorpay Key</label>
                            <input type="text" name="razorpay_key" class="form-control"
                                value="{{ old('razorpay_key', $settings['razorpay_key']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Razorpay Secret</label>
                            <input type="password" name="razorpay_secret" class="form-control"
                                placeholder="{{ $settings['razorpay_secret'] ? '••••••••••••' : '' }}">
                            <div class="form-text">Leave blank to keep current secret.</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mail / SMTP --}}
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">SMTP / Mail</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Mail Host</label>
                            <input type="text" name="mail_host" class="form-control" placeholder="smtp.gmail.com"
                                value="{{ old('mail_host', $settings['mail_host']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Port</label>
                            <input type="number" name="mail_port" class="form-control" placeholder="587"
                                value="{{ old('mail_port', $settings['mail_port']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Username</label>
                            <input type="text" name="mail_username" class="form-control"
                                value="{{ old('mail_username', $settings['mail_username']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mail Password</label>
                            <input type="password" name="mail_password" class="form-control"
                                placeholder="{{ $settings['mail_password'] ? '••••••••••••' : '' }}">
                            <div class="form-text">Leave blank to keep current password.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Address</label>
                            <input type="email" name="mail_from_address" class="form-control"
                                value="{{ old('mail_from_address', $settings['mail_from_address']) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">From Name</label>
                            <input type="text" name="mail_from_name" class="form-control"
                                value="{{ old('mail_from_name', $settings['mail_from_name']) }}">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>