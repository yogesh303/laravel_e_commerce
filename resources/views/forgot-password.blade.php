<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); height: 100vh; }
        .login-card { border-radius: 15px; padding: 30px; background: #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .btn-custom { background: #667eea; color: #fff; border-radius: 8px; }
        .btn-custom:hover { background: #5a67d8; color: #fff; }
    </style>
</head>
<body>
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-4">
        <div class="login-card">
            <h3 class="text-center mb-2 fw-bold">Forgot Password</h3>
            <p class="text-center text-muted">Enter your email and we'll send you a reset link.</p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Enter email" required>
                </div>
                <div class="d-grid">
                    <button class="btn btn-custom">Send Reset Link</button>
                </div>
                <div class="text-center mt-2">
                    <a href="{{ url('login') }}">Back to login</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>