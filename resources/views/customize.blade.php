<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customize {{ $product->name }}</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>

<style>

body {
    background: #f5f6f8;
}

.customizer-container {
    max-width: 1200px;
    margin: 40px auto;
}

.customizer-card {
    background: #fff;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 25px rgba(0,0,0,.08);
}

.product-title {
    font-weight: 600;
}

.product-price {
    font-size: 20px;
    font-weight: 600;
}

/* =========================================================
   PREVIEW
========================================================= */

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

    min-height: 600px;
    overflow: hidden;

    cursor: grab;
}

.preview-area:active {
    cursor: grabbing;
}

/* Fabric canvas */

.canvas-wrapper {
    position: relative;
    display: inline-block;
    box-shadow: 0 10px 35px rgba(0,0,0,.15);
}

/* Zoom controls */

.zoom-controls {
    position: absolute;
    right: 15px;
    top: 15px;

    display: flex;
    flex-direction: column;
    gap: 5px;

    z-index: 100;
}

.zoom-controls button {
    width: 42px;
    height: 42px;

    border: none;
    border-radius: 8px;

    background: rgba(255,255,255,.95);

    font-size: 20px;
    font-weight: bold;

    box-shadow: 0 3px 10px rgba(0,0,0,.15);
}

.zoom-controls button:hover {
    background: #0d6efd;
    color: white;
}

.zoom-level {
    background: rgba(255,255,255,.95);
    padding: 5px 8px;
    border-radius: 6px;
    text-align: center;
    font-size: 12px;
}

/* =========================================================
   UPLOAD
========================================================= */

.upload-area {
    border: 2px dashed #adb5bd;
    border-radius: 12px;
    padding: 30px 20px;

    text-align: center;
    cursor: pointer;

    background: #fafafa;

    transition: .2s;
}

.upload-area:hover {
    border-color: #0d6efd;
    background: #f4f8ff;
}

.upload-icon {
    font-size: 40px;
    margin-bottom: 10px;
}

.upload-area strong {
    display: block;
    font-size: 17px;
}

.upload-area small {
    color: #777;
}

#logoInput {
    display: none;
}

/* =========================================================
   INSTRUCTIONS
========================================================= */

.instructions {
    background: #f8f9fa;
    border-radius: 10px;

    padding: 15px;
    margin-top: 20px;
}

.instructions p {
    margin-bottom: 5px;
}

/* =========================================================
   MOBILE
========================================================= */

@media(max-width:768px) {

    .customizer-container {
        margin: 20px auto;
    }

    .customizer-card {
        padding: 15px;
    }

    .preview-area {
        min-height: 450px;
    }

}

</style>


<div class="container customizer-container">

<div class="row g-4">

    {{-- =====================================================
         LEFT SIDE
    ====================================================== --}}

    <div class="col-lg-7">

        <div class="customizer-card">

            <h3 class="product-title">
                {{ $product->name }}
            </h3>

            <div class="product-price text-success mb-4">
                ₹ {{ number_format($product->price) }}
            </div>


            <div class="preview-area" id="previewArea">

                {{-- Zoom controls --}}

                <div class="zoom-controls">

                    <button type="button" id="zoomIn">
                        +
                    </button>

                    <div class="zoom-level" id="zoomLevel">
                        100%
                    </div>

                    <button type="button" id="zoomOut">
                        −
                    </button>

                    <button type="button" id="zoomReset">
                        ↺
                    </button>

                </div>


                <div class="canvas-wrapper">

                    <canvas id="canvas"></canvas>

                </div>

            </div>


            <div class="instructions">

                <p>
                    <strong>How to customize:</strong>
                </p>

                <p>
                    1. Upload your logo.
                </p>

                <p>
                    2. Drag the logo onto the product.
                </p>

                <p>
                    3. Use the corner handles to resize.
                </p>

                <p>
                    4. Use the top handle to rotate.
                </p>

                <p>
                    5. Use + / − or mouse wheel to zoom.
                </p>

                <p>
                    6. Drag empty space to move around the product.
                </p>

            </div>

        </div>

    </div>


    {{-- =====================================================
         RIGHT SIDE
    ====================================================== --}}

    <div class="col-lg-5">

        <div class="customizer-card">

            <h4 class="mb-2">
                Customize Your Product
            </h4>

            <p class="text-muted mb-4">
                Upload your logo and place it on the product.
            </p>


            {{-- Upload --}}

            <label
                for="logoInput"
                class="upload-area w-100"
            >

                <div class="upload-icon">
                    📁
                </div>

                <strong>
                    Upload Your Logo
                </strong>

                <small>
                    PNG, JPG or WEBP
                </small>

            </label>


            <input
                type="file"
                id="logoInput"
                accept="image/png,image/jpeg,image/webp"
            >


            {{-- Logo controls --}}

            <div class="mt-3">

                <button
                    type="button"
                    id="removeLogo"
                    class="btn btn-outline-danger w-100 mb-2"
                >
                    🗑 Remove Logo
                </button>


                <button
                    type="button"
                    id="resetLogo"
                    class="btn btn-outline-secondary w-100 mb-3"
                >
                    ↩ Reset Logo
                </button>

            </div>


            {{-- Add cart --}}

            <form
                method="POST"
                action="{{ route('product.customize.save', $product->id) }}"
                id="customizeForm"
            >

                @csrf

                <input
                    type="hidden"
                    name="custom_image"
                    id="customImage"
                >


                <button
                    type="submit"
                    id="addToCartBtn"
                    class="btn btn-success btn-lg w-100"
                >
                    🛒 Add Customized Product to Cart
                </button>

            </form>

        </div>

    </div>

</div>

</div>


<script>

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | PRODUCT IMAGE
    |--------------------------------------------------------------------------
    */

    const productImage =
        "{{ asset('images/' . $product->image) }}";


    /*
    |--------------------------------------------------------------------------
    | CANVAS
    |--------------------------------------------------------------------------
    */

    const canvas = new fabric.Canvas('canvas', {

        preserveObjectStacking: true,

        selection: false,

        fireRightClick: true,

        stopContextMenu: true

    });


    /*
    |--------------------------------------------------------------------------
    | PRODUCT IMAGE AS BACKGROUND
    |--------------------------------------------------------------------------
    */

    fabric.Image.fromURL(productImage, function(img) {

        const maxWidth = 650;
        const maxHeight = 550;

        let scale = 1;

        if (img.width > maxWidth) {
            scale = maxWidth / img.width;
        }

        if (img.height * scale > maxHeight) {
            scale = maxHeight / img.height;
        }

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

        canvas.setBackgroundImage(
            img,
            canvas.renderAll.bind(canvas)
        );

    }, {
        crossOrigin: 'anonymous'
    });


    /*
    |--------------------------------------------------------------------------
    | LOGO UPLOAD
    |--------------------------------------------------------------------------
    */

    const logoInput =
        document.getElementById('logoInput');

    let logoObject = null;


    logoInput.addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (!file) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function(event) {

            fabric.Image.fromURL(
                event.target.result,
                function(img) {

                    /*
                    |------------------------------------------
                    | Remove previous logo
                    |------------------------------------------
                    */

                    if (logoObject) {
                        canvas.remove(logoObject);
                    }


                    /*
                    |------------------------------------------
                    | Initial logo size
                    |------------------------------------------
                    */

                    const maxLogoWidth = canvas.width * 0.25;

                    const scale =
                        maxLogoWidth / img.width;


                    img.set({

                        left: canvas.width / 2,

                        top: canvas.height / 2,

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


                    /*
                    |------------------------------------------
                    | Keep logo above product
                    |------------------------------------------
                    */

                    canvas.add(img);

                    canvas.bringToFront(img);

                    canvas.setActiveObject(img);

                    logoObject = img;

                    canvas.renderAll();

                },
                {
                    crossOrigin: 'anonymous'
                }
            );

        };

        reader.readAsDataURL(file);

    });


    /*
    |--------------------------------------------------------------------------
    | REMOVE LOGO
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('removeLogo')
        .addEventListener('click', function() {

            if (!logoObject) {
                return;
            }

            canvas.remove(logoObject);

            logoObject = null;

            canvas.renderAll();

        });


    /*
    |--------------------------------------------------------------------------
    | RESET LOGO
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('resetLogo')
        .addEventListener('click', function() {

            if (!logoObject) {
                return;
            }

            logoObject.set({

                left: canvas.width / 2,

                top: canvas.height / 2,

                scaleX: logoObject.scaleX,

                scaleY: logoObject.scaleY,

                angle: 0

            });

            canvas.setActiveObject(logoObject);

            canvas.renderAll();

        });


    /*
    |--------------------------------------------------------------------------
    | ZOOM
    |--------------------------------------------------------------------------
    */

    let zoom = 1;

    const zoomLevel =
        document.getElementById('zoomLevel');


    function setZoom(newZoom) {

        newZoom = Math.max(
            0.5,
            Math.min(3, newZoom)
        );

        zoom = newZoom;

        canvas.setZoom(zoom);

        zoomLevel.innerText =
            Math.round(zoom * 100) + "%";

        canvas.renderAll();

    }


    /*
    |--------------------------------------------------------------------------
    | ZOOM IN
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('zoomIn')
        .addEventListener('click', function() {

            setZoom(zoom + 0.1);

        });


    /*
    |--------------------------------------------------------------------------
    | ZOOM OUT
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('zoomOut')
        .addEventListener('click', function() {

            setZoom(zoom - 0.1);

        });


    /*
    |--------------------------------------------------------------------------
    | RESET ZOOM
    |--------------------------------------------------------------------------
    */

    document
        .getElementById('zoomReset')
        .addEventListener('click', function() {

            setZoom(1);

            canvas.viewportTransform = [1, 0, 0, 1, 0, 0];

            canvas.renderAll();

        });


    /*
    |--------------------------------------------------------------------------
    | MOUSE WHEEL ZOOM
    |--------------------------------------------------------------------------
    */

    canvas.on('mouse:wheel', function(opt) {

        let delta = opt.e.deltaY;

        let zoomValue =
            canvas.getZoom();

        zoomValue *=
            Math.pow(0.999, delta);

        zoomValue =
            Math.max(
                0.5,
                Math.min(3, zoomValue)
            );


        canvas.zoomToPoint(
            {
                x: opt.e.offsetX,
                y: opt.e.offsetY
            },
            zoomValue
        );


        zoom = zoomValue;

        zoomLevel.innerText =
            Math.round(zoom * 100) + "%";


        opt.e.preventDefault();

        opt.e.stopPropagation();

    });


    /*
    |--------------------------------------------------------------------------
    | PAN CANVAS
    |--------------------------------------------------------------------------
    */

    let isDragging = false;

    let lastPosX;

    let lastPosY;


    canvas.on('mouse:down', function(opt) {

        /*
        |------------------------------------------
        | Only pan when clicking empty canvas
        |------------------------------------------
        */

        if (opt.target) {
            return;
        }

        isDragging = true;

        lastPosX = opt.e.clientX;

        lastPosY = opt.e.clientY;

    });


    canvas.on('mouse:move', function(opt) {

        if (!isDragging) {
            return;
        }

        const e = opt.e;

        const vpt =
            canvas.viewportTransform;


        vpt[4] +=
            e.clientX - lastPosX;

        vpt[5] +=
            e.clientY - lastPosY;


        canvas.requestRenderAll();


        lastPosX = e.clientX;

        lastPosY = e.clientY;

    });


    canvas.on('mouse:up', function() {

        isDragging = false;

    });


    /*
    |--------------------------------------------------------------------------
    | SAVE FINAL IMAGE
    |--------------------------------------------------------------------------
    */

   document
    .getElementById('customizeForm')
    .addEventListener('submit', function(e) {

        if (!logoObject) {
            e.preventDefault();
            alert("Please upload and place your logo first.");
            return;
        }

        canvas.discardActiveObject();

        /* Save current zoom/pan so we can restore it after export */
        const savedViewportTransform = canvas.viewportTransform.slice();
        const savedZoom = zoom;

        /* Reset to identity — export always captures the FULL
           original product + logo, ignoring whatever zoom/pan
           the user was looking at on screen */
        canvas.setViewportTransform([1, 0, 0, 1, 0, 0]);
        canvas.renderAll();

        const finalImage = canvas.toDataURL({
            format: 'png',
            quality: 1,
            multiplier: 2   // this is just export resolution, not a crop
        });

        document.getElementById('customImage').value = finalImage;

        /* Restore the user's view */
        canvas.setViewportTransform(savedViewportTransform);
        zoom = savedZoom;
        canvas.renderAll();

    });

});

</script>

</body>

</html>
