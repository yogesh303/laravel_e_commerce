<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customize {{ $product->name }}</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>

<style>
body { background: #f5f6f8; }
.customizer-container { max-width: 1200px; margin: 40px auto; }
.customizer-card { background: #fff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 25px rgba(0,0,0,.08); }
.product-title { font-weight: 600; }
.product-price { font-size: 20px; font-weight: 600; }

.preview-area {
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    background:
        linear-gradient(45deg, #eeeeee 25%, transparent 25%),
        linear-gradient(-45deg, #eeeeee 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #eeeeee 75%),
        linear-gradient(-45deg, transparent 75%, #eeeeee 75%);
    background-size: 30px 30px;
    background-position: 0 0, 0 15px, 15px -15px, -15px 0;
    border-radius: 12px;
    min-height: 500px;
    overflow: hidden;
    cursor: grab;
}
.preview-area:active { cursor: grabbing; }
.canvas-wrapper { position: relative; display: inline-block; box-shadow: 0 10px 35px rgba(0,0,0,.15); }

.zoom-controls {
    position: absolute; right: 15px; top: 15px;
    display: flex; flex-direction: column; gap: 5px; z-index: 100;
}
.zoom-controls button {
    width: 38px; height: 38px; border: none; border-radius: 8px;
    background: rgba(255,255,255,.95); font-size: 18px; font-weight: bold;
    box-shadow: 0 3px 10px rgba(0,0,0,.15);
}
.zoom-controls button:hover { background: #0d6efd; color: white; }
.zoom-level { background: rgba(255,255,255,.95); padding: 4px 6px; border-radius: 6px; text-align: center; font-size: 11px; }

.upload-area {
    border: 2px dashed #adb5bd; border-radius: 12px; padding: 20px;
    text-align: center; cursor: pointer; background: #fafafa; transition: .2s;
}
.upload-area:hover { border-color: #0d6efd; background: #f4f8ff; }
.upload-icon { font-size: 32px; margin-bottom: 8px; }
.upload-area strong { display: block; font-size: 15px; }
.upload-area small { color: #777; }

.customize-status {
    font-size: 12px;
    padding: 3px 8px;
    border-radius: 10px;
}
.status-empty { background: #f1f1f1; color: #888; }
.status-done { background: #d1e7dd; color: #0a3622; }

.nav-tabs .nav-link.active { font-weight: 600; }

.select-value-prompt {
    padding: 60px 20px;
    text-align: center;
    color: #888;
    background: #fafafa;
    border-radius: 12px;
}
</style>
</head>
<body>

<div class="container customizer-container">

    <div class="customizer-card">

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h3 class="product-title mb-1">{{ $product->name }}</h3>
                <div class="product-price text-success">
                    ₹ {{ number_format($product->quantities->first()->price ?? $product->price) }}
                </div>
            </div>
            <a href="{{ url('/product/' . $product->id) }}" class="btn btn-outline-secondary btn-sm">← Back to Product</a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following before saving:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-muted">
            This product has {{ count($customizableImages) }} image(s) you can customize.
            Switch tabs to place your logo on each one, then save all at once.
        </p>

        <form method="POST"
              action="{{ route('product.customize.save', $product->id) }}"
              id="customizeForm">
            @csrf

            @if($product->quantities->count())
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Quantity</label>
                        <select class="form-select" name="quantity_id" id="quantitySelect" required>
                            <option value="">Select Quantity</option>
                            @foreach($product->quantities as $q)
                                <option value="{{ $q->id }}" data-qty="{{ $q->quantity }}" {{ old('quantity_id') == $q->id ? 'selected' : '' }}>
                                    {{ $q->quantity }} pcs — ₹{{ number_format($q->price) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            @if($product->is_cloth)
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <label class="form-label fw-bold mb-2">Size-wise Quantity</label>

                            @php
                                $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
                            @endphp

                            <div class="row g-2">
                                @foreach($sizes as $size)
                                    <div class="col-4 col-md-2">
                                        <label class="form-label small mb-1">{{ $size }}</label>
                                        <input type="number"
                                               name="sizes[{{ $size }}]"
                                               class="form-control size-input"
                                               min="0"
                                               value="{{ old('sizes.' . $size, 0) }}">
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-2 small">
                                Selected: <span id="sizeTotal">0</span> /
                                Required: <span id="requiredTotal">0</span>
                            </div>

                            <div id="sizeMismatchWarning" class="text-danger small mt-1" style="display:none;">
                                Quantity not match — size quantities must add up to the selected total quantity.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Product options: Size, Color, Position, etc. — MUST be inside the form to submit -->
            @if($product->options->count())
                <div class="row g-3 mb-4">
                    @foreach($product->options as $option)
                        <div class="col-md-4">
                            <label class="form-label fw-bold">{{ $option->name }}</label>
                            <select class="form-select option-trigger-select" name="options[{{ $option->name }}]" required>
                                <option value="">Select {{ $option->name }}</option>
                                @foreach($option->values_array as $val)
                                    <option value="{{ $val }}"
                                        {{ old('options.' . $option->name) === $val ? 'selected' : '' }}>
                                        {{ $val }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="imageTabs" role="tablist">
                @foreach($customizableImages as $i => $img)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $i === 0 ? 'active' : '' }}"
                                id="tab-btn-{{ $img->id }}"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-{{ $img->id }}"
                                data-trigger-values='@json($img->trigger_values ?? [])'
                                type="button">
                            Image {{ $i + 1 }}
                            <span class="customize-status status-empty ms-1" id="status-{{ $img->id }}">not customized</span>
                        </button>
                    </li>
                @endforeach
            </ul>

            <!-- Shown when no tagged image matches the current option selection -->
            <div id="noImageMatch" class="select-value-prompt mb-3" style="display:none;">
                Select an option above to see the matching image to customize.
            </div>

            <div class="tab-content" id="imageTabsContent">

                @foreach($customizableImages as $i => $img)
                    <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}"
                         id="tab-{{ $img->id }}"
                         data-trigger-values='@json($img->trigger_values ?? [])'
                         role="tabpanel">

                        <div class="row g-4">

                            <!-- Canvas side -->
                            <div class="col-lg-7">
                                <div class="preview-area">

                                    <div class="zoom-controls">
                                        <button type="button" class="zoomIn" data-img="{{ $img->id }}">+</button>
                                        <div class="zoom-level" id="zoomLevel-{{ $img->id }}">100%</div>
                                        <button type="button" class="zoomOut" data-img="{{ $img->id }}">−</button>
                                        <button type="button" class="zoomReset" data-img="{{ $img->id }}">↺</button>
                                    </div>

                                    <div class="canvas-wrapper">
                                        <canvas id="canvas-{{ $img->id }}"></canvas>
                                    </div>

                                </div>
                            </div>

                            <!-- Controls side -->
                            <div class="col-lg-5">

                                <label class="upload-area w-100" for="logoInput-{{ $img->id }}">
                                    <div class="upload-icon">📁</div>
                                    <strong>Upload Your Logo</strong>
                                    <small>PNG, JPG or WEBP</small>
                                </label>

                                <input type="file" id="logoInput-{{ $img->id }}"
                                       class="logoInput d-none"
                                       data-img="{{ $img->id }}"
                                       accept="image/png,image/jpeg,image/webp">

                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-danger w-100 mb-2 removeLogo" data-img="{{ $img->id }}">
                                        🗑 Remove Logo
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary w-100 mb-3 resetLogo" data-img="{{ $img->id }}">
                                        ↩ Reset Position
                                    </button>
                                </div>

                                <div class="alert alert-light border small">
                                    Drag the logo onto the product, use corner handles to resize,
                                    top handle to rotate, and +/− or scroll to zoom this image.
                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

            <!-- Hidden inputs populated on submit -->
            <div id="hiddenInputs"></div>

            <hr>

            <button type="submit" id="addToCartBtn" class="btn btn-success btn-lg w-100">
                🛒 Save All Customizations & Add to Cart
            </button>

        </form>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | List of images to customize, passed from the server
    |--------------------------------------------------------------------------
    */

    const images = @json($customizableImages->map(function ($img) {
        return [
            'id'  => $img->id,
            'url' => asset('images/' . $img->image),
        ];
    }));

    // Holds one fabric canvas + state per image id
    const customizers = {};

    function initCustomizer(imgId, imageUrl) {

        const canvas = new fabric.Canvas('canvas-' + imgId, {
            preserveObjectStacking: true,
            selection: false,
            fireRightClick: true,
            stopContextMenu: true
        });

        const state = {
            canvas: canvas,
            logoObject: null,
            logoDataUrl: null, // holds the original uploaded logo file
            zoom: 1
        };

        customizers[imgId] = state;

        fabric.Image.fromURL(imageUrl, function (img) {

            const maxWidth = 550;
            const maxHeight = 480;

            let scale = 1;
            if (img.width > maxWidth) scale = maxWidth / img.width;
            if (img.height * scale > maxHeight) scale = maxHeight / img.height;

            const width = img.width * scale;
            const height = img.height * scale;

            canvas.setWidth(width);
            canvas.setHeight(height);

            img.set({
                scaleX: scale,
                scaleY: scale,
                originX: 'left',
                originY: 'top',
                selectable: false,
                evented: false
            });

            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));

        }, { crossOrigin: 'anonymous' });

        /* Pan */
        let isDragging = false, lastPosX, lastPosY;

        canvas.on('mouse:down', function (opt) {
            if (opt.target) return;
            isDragging = true;
            lastPosX = opt.e.clientX;
            lastPosY = opt.e.clientY;
        });

        canvas.on('mouse:move', function (opt) {
            if (!isDragging) return;
            const e = opt.e;
            const vpt = canvas.viewportTransform;
            vpt[4] += e.clientX - lastPosX;
            vpt[5] += e.clientY - lastPosY;
            canvas.requestRenderAll();
            lastPosX = e.clientX;
            lastPosY = e.clientY;
        });

        canvas.on('mouse:up', function () { isDragging = false; });

        /* Mouse wheel zoom */
        canvas.on('mouse:wheel', function (opt) {
            let zoomValue = canvas.getZoom();
            zoomValue *= Math.pow(0.999, opt.e.deltaY);
            zoomValue = Math.max(0.5, Math.min(3, zoomValue));

            canvas.zoomToPoint({ x: opt.e.offsetX, y: opt.e.offsetY }, zoomValue);

            state.zoom = zoomValue;
            document.getElementById('zoomLevel-' + imgId).innerText = Math.round(zoomValue * 100) + '%';

            opt.e.preventDefault();
            opt.e.stopPropagation();
        });
    }

    // Init a canvas per image
    images.forEach(function (img) {
        initCustomizer(img.id, img.url);
    });

    /*
    |--------------------------------------------------------------------------
    | Logo upload (delegated per image via data-img attribute)
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.logoInput').forEach(function (input) {
        input.addEventListener('change', function (e) {

            const imgId = this.dataset.img;
            const state = customizers[imgId];
            const file = e.target.files[0];

            if (!file) return;

            // ==========================================
            // LOGO FILE SIZE VALIDATION
            // Minimum: 1 MB
            // Maximum: 4 MB
            // ==========================================
            const minSize = 1 * 1024 * 1024;
            const maxSize = 4 * 1024 * 1024;

            if (file.size < minSize) {
                alert('Logo size must be at least 1 MB.');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('Logo size must not exceed 4 MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {

                // keep the original uploaded logo file separate from the composited canvas export
                state.logoDataUrl = event.target.result;

                fabric.Image.fromURL(event.target.result, function (logo) {

                    if (state.logoObject) {
                        state.canvas.remove(state.logoObject);
                    }

                    const maxLogoWidth = state.canvas.width * 0.25;
                    const scale = maxLogoWidth / logo.width;

                    logo.set({
                        left: state.canvas.width / 2,
                        top: state.canvas.height / 2,
                        originX: 'center',
                        originY: 'center',
                        scaleX: scale,
                        scaleY: scale,
                        cornerStyle: 'circle',
                        cornerColor: '#0d6efd',
                        cornerStrokeColor: '#fff',
                        borderColor: '#0d6efd',
                        transparentCorners: false,
                        padding: 8,
                        hasRotatingPoint: true,
                        selectable: true,
                        evented: true
                    });

                    state.canvas.add(logo);
                    state.canvas.bringToFront(logo);
                    state.canvas.setActiveObject(logo);
                    state.logoObject = logo;
                    state.canvas.renderAll();

                    // Mark this tab as customized
                    const statusEl = document.getElementById('status-' + imgId);
                    statusEl.textContent = 'customized';
                    statusEl.classList.remove('status-empty');
                    statusEl.classList.add('status-done');

                }, {
                    crossOrigin: 'anonymous'
                });
            };

            reader.readAsDataURL(file);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Remove / Reset logo (per image)
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll('.removeLogo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imgId = this.dataset.img;
            const state = customizers[imgId];

            if (!state.logoObject) return;

            state.canvas.remove(state.logoObject);
            state.logoObject = null;
            state.logoDataUrl = null;
            state.canvas.renderAll();

            const statusEl = document.getElementById('status-' + imgId);
            statusEl.textContent = 'not customized';
            statusEl.classList.remove('status-done');
            statusEl.classList.add('status-empty');
        });
    });

    document.querySelectorAll('.resetLogo').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imgId = this.dataset.img;
            const state = customizers[imgId];

            if (!state.logoObject) return;

            state.logoObject.set({
                left: state.canvas.width / 2,
                top: state.canvas.height / 2,
                angle: 0
            });

            state.canvas.setActiveObject(state.logoObject);
            state.canvas.renderAll();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Zoom buttons (per image)
    |--------------------------------------------------------------------------
    */

    function setZoom(imgId, newZoom) {
        const state = customizers[imgId];
        newZoom = Math.max(0.5, Math.min(3, newZoom));
        state.zoom = newZoom;
        state.canvas.setZoom(newZoom);
        document.getElementById('zoomLevel-' + imgId).innerText = Math.round(newZoom * 100) + '%';
        state.canvas.renderAll();
    }

    document.querySelectorAll('.zoomIn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imgId = this.dataset.img;
            setZoom(imgId, customizers[imgId].zoom + 0.1);
        });
    });

    document.querySelectorAll('.zoomOut').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imgId = this.dataset.img;
            setZoom(imgId, customizers[imgId].zoom - 0.1);
        });
    });

    document.querySelectorAll('.zoomReset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const imgId = this.dataset.img;
            const state = customizers[imgId];
            setZoom(imgId, 1);
            state.canvas.viewportTransform = [1, 0, 0, 1, 0, 0];
            state.canvas.renderAll();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | DYNAMIC TAB FILTERING — AND logic across option categories.
    |--------------------------------------------------------------------------
    */

    function getSelectedOptionValuesByName() {
        const selected = {};
        document.querySelectorAll('.option-trigger-select').forEach(function (select) {
            const match = select.name.match(/^options\[(.+)\]$/);
            if (match && select.value) {
                selected[match[1]] = select.value;
            }
        });
        return selected;
    }

    function imageMatches(triggers, selected) {
        const optionNames = Object.keys(triggers || {});

        if (optionNames.length === 0) {
            return true;
        }

        return optionNames.every(function (optName) {
            const allowedValues = triggers[optName] || [];
            if (allowedValues.length === 0) {
                return true;
            }
            const selectedVal = selected[optName];
            return selectedVal && allowedValues.includes(selectedVal);
        });
    }

    function applyImageFilter() {
        const selected = getSelectedOptionValuesByName();
        const tabButtons = document.querySelectorAll('#imageTabs .nav-link');
        const noMatchBox = document.getElementById('noImageMatch');

        let firstVisibleBtn = null;
        let visibleCount = 0;

        tabButtons.forEach(function (btn) {
            let triggers = {};
            try {
                triggers = JSON.parse(btn.dataset.triggerValues || '{}');
            } catch (e) {}

            const matches = imageMatches(triggers, selected);

            const li = btn.closest('li');
            li.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount++;
                if (!firstVisibleBtn) {
                    firstVisibleBtn = btn;
                }
            }
        });

        const activeBtn = document.querySelector('#imageTabs .nav-link.active');
        const activeIsHidden = activeBtn && activeBtn.closest('li').style.display === 'none';

        if (activeIsHidden && firstVisibleBtn) {
            new bootstrap.Tab(firstVisibleBtn).show();
        }

        const tabsWrapper = document.getElementById('imageTabs');
        const tabContent = document.getElementById('imageTabsContent');

        if (visibleCount === 0) {
            noMatchBox.style.display = '';
            tabsWrapper.style.display = 'none';
            tabContent.style.display = 'none';
        } else {
            noMatchBox.style.display = 'none';
            tabsWrapper.style.display = '';
            tabContent.style.display = '';
        }
    }

    document.querySelectorAll('.option-trigger-select').forEach(function (select) {
        select.addEventListener('change', applyImageFilter);
    });

    applyImageFilter();

    /*
    |--------------------------------------------------------------------------
    | Size-wise quantity total vs selected quantity tier
    |--------------------------------------------------------------------------
    */

    const sizeInputs = document.querySelectorAll('.size-input');
    const sizeTotalEl = document.getElementById('sizeTotal');
    const requiredTotalEl = document.getElementById('requiredTotal');
    const mismatchWarning = document.getElementById('sizeMismatchWarning');
    const qtySelectEl = document.getElementById('quantitySelect');

    function getRequiredQty() {
        if (!qtySelectEl) return null; // product has no quantity tiers — nothing to match against

        const opt = qtySelectEl.options[qtySelectEl.selectedIndex];

        // Nothing selected yet (the blank "Select Quantity" option) — don't validate yet
        if (!opt || !opt.value) return null;

        return parseInt(opt.dataset.qty, 10) || 0;
    }

    function updateSizeTotals() {
        if (!sizeInputs.length) return;

        let total = 0;
        sizeInputs.forEach(function (input) {
            total += parseInt(input.value, 10) || 0;
        });

        const required = getRequiredQty();

        sizeTotalEl.textContent = total;

        if (required === null) {
            // No tier chosen yet (or product has none) — just show the total, no mismatch
            requiredTotalEl.textContent = '—';
            mismatchWarning.style.display = 'none';
        } else {
            requiredTotalEl.textContent = required;
            mismatchWarning.style.display = (total !== required) ? '' : 'none';
        }
    }

    if (qtySelectEl) {
        qtySelectEl.addEventListener('change', updateSizeTotals);
    }

    sizeInputs.forEach(function (input) {
        input.addEventListener('input', updateSizeTotals);
    });

    updateSizeTotals();

    function sizesMatchRequired() {
        if (!sizeInputs.length) return true; // not a cloth product — nothing to check

        let total = 0;
        sizeInputs.forEach(function (input) {
            total += parseInt(input.value, 10) || 0;
        });

        const required = getRequiredQty();

        if (required === null) {
            // No tier — just require at least one size filled in
            return total >= 1;
        }

        return total === required;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE ALL — export every canvas that has a logo placed, plus the raw logo
    |--------------------------------------------------------------------------
    */

    document.getElementById('customizeForm').addEventListener('submit', function (e) {

        e.preventDefault();

        if (!sizesMatchRequired()) {
            mismatchWarning.style.display = '';
            const required = getRequiredQty();
            const msg = required === null
                ? 'Please enter at least 1 in one of the sizes.'
                : 'Quantity not match. Please make sure size quantities add up to ' + required + '.';
            alert(msg);
            return;
        }

        const hiddenWrapper = document.getElementById('hiddenInputs');
        hiddenWrapper.innerHTML = '';

        let customizedCount = 0;

        Object.keys(customizers).forEach(function (imgId) {

            const state = customizers[imgId];

            if (!state.logoObject) {
                return;
            }

            const btn = document.getElementById('tab-btn-' + imgId);
            if (btn && btn.closest('li').style.display === 'none') {
                return;
            }

            state.canvas.discardActiveObject();

            const savedViewportTransform = state.canvas.viewportTransform.slice();
            const savedZoom = state.zoom;

            state.canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
            state.canvas.renderAll();

            const finalImage = state.canvas.toDataURL({
                format: 'png',
                quality: 1,
                multiplier: 2
            });

            state.canvas.setViewportTransform(savedViewportTransform);
            state.zoom = savedZoom;
            state.canvas.renderAll();

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'custom_images[' + imgId + ']';
            input.value = finalImage;
            hiddenWrapper.appendChild(input);

            // send the original uploaded logo file too, same key so it maps 1:1
            if (state.logoDataUrl) {
                const logoInput = document.createElement('input');
                logoInput.type = 'hidden';
                logoInput.name = 'logo_images[' + imgId + ']';
                logoInput.value = state.logoDataUrl;
                hiddenWrapper.appendChild(logoInput);
            }

            customizedCount++;
        });

        if (customizedCount === 0) {
            alert('Please upload and place a logo on at least one image before saving.');
            return;
        }

        this.submit();
    });

});
</script>

</body>
</html>

</body>
</html>