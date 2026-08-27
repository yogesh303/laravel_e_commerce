<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Remarks — {{ $product->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        .guideline-card {
            background: #f8f9fb;
            border: 1px solid #e9ecef;
            border-radius: .5rem;
        }
        .guideline-card h6 {
            font-weight: 600;
        }
        .guideline-card ul li {
            margin-bottom: .5rem;
        }
        .file-row {
            display: flex;
            align-items: center;
            gap: .5rem;
            margin-bottom: .5rem;
        }
        .file-row .form-control {
            flex: 1;
        }
        .remove-file-btn {
            flex-shrink: 0;
        }
    </style>
</head>
<body>
<x-layout></x-layout>

<div class="container mt-5 mb-5" style="max-width:1000px;">

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- LEFT SIDE: Remarks + File Upload --}}
        <div class="col-lg-7">
            <div class="card shadow p-4 h-100">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('images/' . $product->image) }}"
                         style="width:70px;height:70px;object-fit:cover;" class="rounded border">
                    <div>
                        <h5 class="mb-0">{{ $product->name }}</h5>
                        <small class="text-muted">Add any remarks or reference file before adding to cart</small>
                    </div>
                </div>

                <form id="remarksForm"
                      action="{{ $isCustomization ? route('product.customize.finalize', $product->id) : url('/add_cart') }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf

                    @unless($isCustomization)
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        @if($prefill['quantity_id'])
                            <input type="hidden" name="quantity_id" value="{{ $prefill['quantity_id'] }}">
                        @endif
                        <input type="hidden" name="quantity" value="{{ $prefill['quantity'] ?? 0 }}">

                        @if(!empty($prefill['options']))
                            @foreach($prefill['options'] as $optName => $optValue)
                                <input type="hidden" name="options[{{ $optName }}]" value="{{ $optValue }}">
                            @endforeach
                        @endif

                        @if(!empty($prefill['sizes']))
                            @foreach($prefill['sizes'] as $size => $qty)
                                <input type="hidden" name="sizes[{{ $size }}]" value="{{ $qty }}">
                            @endforeach
                        @endif
                    @endunless

                    <div class="mb-4">
                        <label class="form-label fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"
                            placeholder="Any special instructions...">{{ old('remarks') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Attach Original File</label>

                        <div id="fileRowsContainer">
                            {{-- first row --}}
                            <div class="file-row">
                                <input type="file" name="additional_files[]" class="form-control">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-file-btn" onclick="removeFileRow(this)" style="visibility:hidden;">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" id="addFileBtn" class="btn btn-outline-primary btn-sm mt-1">
                            <i class="bi bi-plus-lg"></i> Add another file
                        </button>
                        <div class="form-text">Select and add files one at a time.</div>
                    </div>

                    <button class="btn btn-success w-100 btn-lg mt-3">Save & Add to Cart</button>
                </form>
            </div>
        </div>

        {{-- RIGHT SIDE: Guideline Notes --}}
        <div class="col-lg-5">
            <div class="guideline-card p-4 h-100">
                <h6 class="mb-3"><i class="bi bi-info-circle me-1"></i> Guidelines</h6>
                <ul class="ps-3 mb-4">
                    <li>Accepted formats: <strong>JPG, PNG, PDF, AI, PSD, EPS, ZIP</strong></li>
                    <li>You can upload up to <strong>5 files</strong> total</li>
                    <li>Use high-resolution images for best print quality</li>
                    <li>For logo files, vector formats (AI/EPS) are preferred</li>
                </ul>

                <h6 class="mb-2"><i class="bi bi-chat-left-text me-1"></i> Writing good remarks</h6>
                <ul class="ps-3 mb-4">
                    <li>Mention exact colors, positioning, or sizing if relevant</li>
                    <li>Reference the attached file by name if you upload more than one</li>
                    <li>Include delivery or urgency notes here if applicable</li>
                </ul>

                <div class="alert alert-warning mb-0 py-2 px-3 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Files without clear instructions may delay processing.
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('addFileBtn').addEventListener('click', function () {
    const container = document.getElementById('fileRowsContainer');

    const row = document.createElement('div');
    row.className = 'file-row';
    row.innerHTML = `
        <input type="file" name="additional_files[]" class="form-control">
        <button type="button" class="btn btn-outline-danger btn-sm remove-file-btn" onclick="removeFileRow(this)">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    container.appendChild(row);
    updateRemoveButtons();
});

function removeFileRow(btn) {
    const row = btn.closest('.file-row');
    row.remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#fileRowsContainer .file-row');
    rows.forEach((row, index) => {
        const removeBtn = row.querySelector('.remove-file-btn');
        removeBtn.style.visibility = rows.length > 1 ? 'visible' : 'hidden';
    });
}
</script>
<x-footer></x-footer>
</body>
</html>