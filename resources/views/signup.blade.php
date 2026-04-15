<!DOCTYPE html>
<html>
<head>
    <title>Signup</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            height: 100vh;
        }

        .signup-card {
            border-radius: 15px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .signup-title {
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

        .link {
            text-decoration: none;
            color: #667eea;
        }
    </style>
</head>

<body>

<div class="container d-flex justify-content-center align-items-center vh-100">

    <div class="col-md-4">
        <div class="signup-card">

            <h3 class="text-center mb-4 signup-title">Sign Up</h3>

            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <p class="mb-1">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ url('signup_user') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter name" required>
                </div>

                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter email" required>
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                </div>

                <div class="mb-3">
                    <label>Confirm Password</label>
                    <input type="password" id="confirmPassword" name="password_confirmation" class="form-control" placeholder="Confirm password" required>
                </div>

                <div class="mb-3">
                    <input type="checkbox" onclick="togglePassword()"> Show Password
                </div>

                <div class="d-grid mb-3">
                    <button class="btn btn-custom">Sign Up</button>
                </div>


                <div class="text-center">
                    Already have an account?
                    <a href="{{ url('login') }}" class="link">Login</a>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
function togglePassword() {
    let pass = document.getElementById("password");
    let confirm = document.getElementById("confirmPassword");

    pass.type = pass.type === "password" ? "text" : "password";
    confirm.type = confirm.type === "password" ? "text" : "password";
}
</script>

</body>
</html>