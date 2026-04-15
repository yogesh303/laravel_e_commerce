<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
        }

        .login-card {
            border-radius: 15px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .login-title {
            font-weight: bold;
            color: #333;
        }

        .btn-custom {
            background: #667eea;
            color: #fff;
            border-radius: 8px;
        }

        .btn-custom:hover {
            background: #5a67d8;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="col-md-4">
        <div class="login-card">

            <h3 class="text-center mb-4 login-title">Login</h3>

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ url('login_user') }}">
                @csrf

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <div class="d-grid">
                    <button class="btn btn-custom">Login</button>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>