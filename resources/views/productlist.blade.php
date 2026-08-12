 <!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>
 
 <h2 class="mt-2 text-center">Product list</h2>
 <div class="container">
<form action="delete_all" method="POST">
    @csrf
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <th>#</th>
            <th>Product</th>
            <th>Price</th>
            <th>description</th>
            <th>Action</th>
        </thead>
        @foreach($products as $row)
        <tr>
            <td><input type="checkbox" name="id[]" value="{{$row->id}}" /></td>
            <td>{{$row->name}}</td>
            <td>${{$row->price}}</td>
            <td>{{$row->description}}</td>
            <td><a href='delete_product/{{$row->id}}'>Delete</a> <a href='edit_product/{{$row->id}}'>Edit</a></td>
        </tr>
        @endforeach
    </table>
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
</body>
</html>