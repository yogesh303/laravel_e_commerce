<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<x-layout></x-layout>

<div class="container mt-5 mb-5">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">

        <!-- Add Category -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Add Category</div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/categories') }}">
                        @csrf
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control" placeholder="e.g. T-Shirt" required>
                        </div>
                        <button class="btn btn-success">Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Add Subcategory -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">Add Subcategory</div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/subcategories') }}">
                        @csrf
                        <div class="mb-3">
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="name" class="form-control" placeholder="e.g. Collar" required>
                        </div>
                        <button class="btn btn-success">Add Subcategory</button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- List -->
    <div class="card shadow mt-4">
        <div class="card-header bg-dark text-white">Categories & Subcategories</div>
        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Subcategories</th>
                        <th width="120">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td class="fw-bold">{{ $cat->name }}</td>
                            <td>
                                @forelse($cat->subcategories as $sub)
                                    <span class="badge bg-secondary me-1 mb-1">
                                        {{ $sub->name }}
                                        <form method="POST" action="{{ url('/subcategories/'.$sub->id) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm p-0 text-white border-0 bg-transparent" style="font-size:11px;" onclick="return confirm('Delete this subcategory?')">✕</button>
                                        </form>
                                    </span>
                                @empty
                                    <span class="text-muted">No subcategories</span>
                                @endforelse
                            </td>
                            <td>
                                <form method="POST" action="{{ url('/categories/'.$cat->id) }}" onsubmit="return confirm('This will delete all its subcategories too. Continue?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">No categories yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
<x-footer></x-footer>
</body>
</html>