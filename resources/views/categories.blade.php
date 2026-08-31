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
                        <th width="140">Action</th>
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
                                        <a href="#" class="text-white text-decoration-none" style="font-size:11px;"
                                           data-bs-toggle="modal" data-bs-target="#editSubModal{{ $sub->id }}" title="Edit">✎</a>
                                        <form method="POST" action="{{ url('/subcategories/'.$sub->id) }}" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm p-0 text-white border-0 bg-transparent" style="font-size:11px;" onclick="return confirm('Delete this subcategory?')">✕</button>
                                        </form>
                                    </span>

                                    <!-- Edit Subcategory Modal -->
                                    <div class="modal fade" id="editSubModal{{ $sub->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ url('/subcategories/'.$sub->id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Subcategory</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="text" name="name" class="form-control"
                                                               value="{{ $sub->name }}" required>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-muted">No subcategories</span>
                                @endforelse
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal{{ $cat->id }}">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ url('/categories/'.$cat->id) }}" onsubmit="return confirm('This will delete all its subcategories too. Continue?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Category Modal -->
                        <div class="modal fade" id="editModal{{ $cat->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ url('/categories/'.$cat->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Category</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="text" name="name" class="form-control"
                                                value="{{ $cat->name }}" required>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</html>