<!DOCTYPE html>
<html>
<head>
    <title>Signup Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<h2>sign up</h2>
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
<form action="signup_user" method="POST">
    @csrf
    <input type="text" name="name" placeholder="name" class="form-control">
    <br>
    <input type="email" name="email" placeholder="email" class="form-control">
    <br>
    <input type="password" name="password" placeholder="password" class="form-control">
    <br>

    <input type="password" name="password_confirmation" placeholder="confirm password" class="form-control">
    <br>
    <button class="btn btn-success">sign up</button>
</form>
</body>
</html>