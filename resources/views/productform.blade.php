<!DOCTYPE html>
<html>
<head>
    <title>Product Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<form action="{{ route('logout') }}" method="POST">
    @csrf
    <button class="btn btn-danger">Logout</button>
</form>
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4>Add Product</h4>
        </div>
        <div class="card-body">
           @if(isset($products))
                <form action="{{ url('/update_product') }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
            @else
                <form action="{{ url('/add_product') }}" method="POST" enctype="multipart/form-data">
            @endif
                @csrf
                <input type="hidden" name="id" value="{{$products->id ?? ''}}" class="form-control">
                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{$products->name ?? ''}}" class="form-control" placeholder="Enter product name" required>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" value="{{$products->price ?? ''}}" class="form-control" placeholder="Enter price" required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter description">{{ $products->description ?? '' }}</textarea>
                </div>

                <!-- Stock -->
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" value="{{$products->stock ?? ''}}" class="form-control" placeholder="Enter stock quantity" required>
                </div>

                <!-- Image -->
                <div class="mb-3">
                    <label class="form-label">Product Image</label>
                     @if(isset($products) && $products->image)
                        <img src="{{ asset('images/' . $products->image) }}" 
                            width="100" height="100" style="object-fit:cover; margin-bottom:8px;"><br>
                        <small>Upload new image to replace existing</small><br>
                    @endif
                    <input type="file" name="image" class="form-control">
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-success">Save Product</button>
                <a href="{{ url('/') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>