
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
@php
    $user_role = Auth::user()->role;
    $user_name = Auth::user()->name;
    $user_email = Auth::user()->email;
@endphp
    <x-layout></x-layout>
<h2 class="mb-4 mt-2 text-center">Welcome {{$user_name}}</h2>
<div class="container">
    <div class="row text-center">
        <div class="col-md-4 mb-3">
            <div class="card p-4 shadow">
                <h4>Total Products</h4>
                <h2>{{ $totalProducts }}</h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4 shadow">
                <h4>Total Orders</h4>
                <h2>{{ $totalOrders }}</h2>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <div class="card p-4 shadow">
                <h4>Total Revenue</h4>
                <h2>₹ {{ $totalPrice }}</h2>
            </div>
        </div>
    </div>
</div>
<x-footer></x-footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>