 <!DOCTYPE html>
<html>
<head>
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>
 
 <h2>Product list</h2>
 <div class="container">
<form action="delete_all" method="POST">
    @csrf
    <table class="table table-bordered text-center">
        <thead class="table-dark">
            <th>#</th>
            <th>Name</th>
            <th>class</th>
            <th>roll_no</th>
            <th>address</th>
            <th>Action</th>
        </thead>
        @foreach($products as $row)
        <tr>
            <td><input type="checkbox" name="id[]" value="{{$row->id}}" /></td>
            <td>{{$row->name}}</td>
            <td>${{$row->price}}</td>
            <td>{{$row->description}}</td>
            <td>{{$row->stock}}</td>
            <td><a href='delete_product/{{$row->id}}'>Delete</a> <a href='edit_product/{{$row->id}}'>Edit</a></td>
        </tr>
        @endforeach
    </table>
</form>
</div>
</body>
</html>