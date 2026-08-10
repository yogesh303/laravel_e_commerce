<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>

<div class="container mt-5 mb-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="text-center">{{ isset($products) ? 'Edit Product' : 'Add Product' }}</h4>
        </div>
        <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(isset($products))
                <form action="{{ url('/update_product') }}" method="POST" enctype="multipart/form-data">
                    @method('PUT')
            @else
                <form action="{{ url('/add_product') }}" method="POST" enctype="multipart/form-data">
            @endif
                @csrf
                <input type="hidden" name="id" value="{{ $products->id ?? '' }}">

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{ $products->name ?? '' }}" class="form-control" required>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" value="{{ $products->price ?? '' }}" class="form-control" required>
                </div>
                <!-- Category / Subcategory -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (isset($products) && $products->category_id == $cat->id) ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" id="subcategorySelect" class="form-select" required>
                            <option value="">Select Category First</option>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $products->description ?? '' }}</textarea>
                </div>

                <!-- Stock -->
                <div class="mb-3">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" value="{{ $products->stock ?? '' }}" class="form-control" required>
                </div>

                <!-- Main Image -->
                <div class="mb-4">
                    <label class="form-label">Main Product Image</label>
                    @if(isset($products) && $products->image)
                        <div><img src="{{ asset('images/' . $products->image) }}" width="100" height="100" style="object-fit:cover; margin-bottom:8px;"></div>
                        <small class="text-muted">Upload new image to replace existing</small><br>
                    @endif
                    <input type="file" name="image" class="form-control">
                </div>

                <hr>

                <!-- ===================== DYNAMIC OPTIONS (Size, Color, etc.) ===================== -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Product Options (Size, Color, Paper Type, etc.)</label>
                    <div id="optionsWrapper">
                        @if(isset($products) && $products->options->count())
                            @foreach($products->options as $i => $option)
                                <div class="row g-2 mb-2 option-row">
                                    <div class="col-md-4">
                                        <input type="text" name="options[{{ $i }}][name]" value="{{ $option->name }}" class="form-control" placeholder="Field name e.g. Size">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" name="options[{{ $i }}][values]" value="{{ $option->values }}" class="form-control" placeholder="Comma separated values e.g. S,M,L,XL">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-option">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" id="addOption" class="btn btn-sm btn-outline-primary mt-2">+ Add Field</button>
                    <div class="form-text">Example: Name = "Color", Values = "Red,Blue,Black". This renders as a dropdown on the product page.</div>
                </div>

                <hr>

                <!-- ===================== MULTIPLE GALLERY IMAGES ===================== -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Product Gallery Images</label>

                    @if(isset($products) && $products->images->count())
                        <div class="mb-3">
                            <small class="text-muted d-block mb-2">Existing images — uncheck "Remove" to delete on save</small>
                            <div class="row g-2">
                                @foreach($products->images as $img)
                                    <div class="col-md-3 border rounded p-2">
                                        <img src="{{ asset('images/' . $img->image) }}" class="img-fluid mb-2" style="height:90px;object-fit:cover;width:100%;">
                                        <input type="hidden" name="existing_images[]" value="{{ $img->id }}">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="existing_customizable_{{ $img->id }}" {{ $img->is_customizable ? 'checked' : '' }}>
                                            <label class="form-check-label small">Customizable</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="remove_images[]" value="{{ $img->id }}">
                                            <label class="form-check-label small text-danger">Remove image</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div id="imagesWrapper"></div>
                    <button type="button" id="addImage" class="btn btn-sm btn-outline-primary mt-2">+ Add Image</button>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-success">Save Product</button>
                <a href="{{ url('/products') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---------------- Dynamic Options ---------------- */
    let optionIndex = {{ isset($products) ? $products->options->count() : 0 }};
    const optionsWrapper = document.getElementById('optionsWrapper');

    document.getElementById('addOption').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 option-row';
        row.innerHTML = `
            <div class="col-md-4">
                <input type="text" name="options[${optionIndex}][name]" class="form-control" placeholder="Field name e.g. Size">
            </div>
            <div class="col-md-6">
                <input type="text" name="options[${optionIndex}][values]" class="form-control" placeholder="Comma separated values e.g. S,M,L,XL">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-option">Remove</button>
            </div>`;
        optionsWrapper.appendChild(row);
        optionIndex++;
    });

    optionsWrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-option')) {
            e.target.closest('.option-row').remove();
        }
    });

    /* ---------------- Dynamic New Gallery Images ---------------- */
    let imageIndex = 0;
    const imagesWrapper = document.getElementById('imagesWrapper');

    document.getElementById('addImage').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 align-items-center image-row';
        row.innerHTML = `
            <div class="col-md-7">
                <input type="file" name="images[${imageIndex}]" class="form-control" accept="image/*">
            </div>
            <div class="col-md-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="customizable[${imageIndex}]" value="1">
                    <label class="form-check-label">Customizable</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-image">Remove</button>
            </div>`;
        imagesWrapper.appendChild(row);
        imageIndex++;
    });

    imagesWrapper.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-image')) {
            e.target.closest('.image-row').remove();
        }
    });

});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const categorySelect    = document.getElementById('categorySelect');
    const subcategorySelect = document.getElementById('subcategorySelect');

    // When editing, we already know which subcategory is selected
    const currentSubcategoryId = "{{ $products->subcategory_id ?? '' }}";

    function loadSubcategories(categoryId, selectedId = '') {
        subcategorySelect.innerHTML = '<option value="">Loading...</option>';

        if (!categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
            return;
        }

        fetch(`/get-subcategories/${categoryId}`)
            .then(res => res.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';

                data.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    if (String(sub.id) === String(selectedId)) {
                        opt.selected = true;
                    }
                    subcategorySelect.appendChild(opt);
                });
            })
            .catch(() => {
                subcategorySelect.innerHTML = '<option value="">Failed to load</option>';
            });
    }

    categorySelect.addEventListener('change', function () {
        loadSubcategories(this.value);
    });

    // On page load: if editing a product, auto-load its subcategories
    if (categorySelect.value) {
        loadSubcategories(categorySelect.value, currentSubcategoryId);
    }

});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>

</body>
</html>