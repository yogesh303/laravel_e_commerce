<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Products;
use App\Http\Controllers\UserControl;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CategoryController;


// Default landing page — show the product catalog to everyone (guest or logged-in)
Route::get('/', [Products::class, 'products_card'])->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login_user',[UserControl::class,'login_user']);
Route::view('/signup','signup');
Route::post('/send-otp', [UserControl::class, 'send_otp'])->name('send.otp');
Route::post('/signup_user', [UserControl::class, 'signup_user'])->name('signup.user');

Route::middleware(['auth','admin'])->group(function () {
    Route::get('/product_form', [Products::class, 'create']);
    Route::get('/product_list',[Products::class,'show']);
    Route::post('/add_product',[Products::class,'store']);
    Route::get('/edit_product/{id}',[Products::class,'edit']);
    Route::put('/update_product',[Products::class,'update']);
    Route::get('/delete_product/{id}',[Products::class,'delete']);

    // Category management
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    Route::post('/subcategories', [CategoryController::class, 'storeSubcategory']);
    Route::delete('/subcategories/{id}', [CategoryController::class, 'destroySubcategory']);
});

// Public: anyone (guest or logged-in) can browse products and view product details
Route::get('/products', [Products::class, 'products_card'])->name('products.index');
Route::get('/product/{id}', [Products::class, 'productDetails'])->name('product.details');

// Customizing a product still needs an account (it saves to the user's cart)
Route::middleware('auth')->group(function () {
    Route::get('/customize/{id}', [CartController::class, 'customize'])->name('product.customize');
    Route::post('/customize/{id}/save', [CartController::class, 'saveCustomization'])->name('product.customize.save');
});


// AJAX: fetch subcategories for a given category (used by the product form dropdown)
Route::get('/get-subcategories/{category_id}', [CategoryController::class, 'getSubcategories']);

Route::middleware('auth')->group(function () {
    Route::post('/add_cart',[CartController::class,'add_cart']); // set cart of user product add
    Route::get('/cart',[CartController::class,'cart']); // display user cart
    Route::post('/add_quantity',[CartController::class,'add_quantity']); // improve quantity
    Route::post('/order',[CartController::class,'order']); // sussec order and reduce product quantity
    Route::get('/orders',[CartController::class,'order_list']); 
    Route::get('/payment-success', [CartController::class, 'payment_success']);
    Route::get('/checkout', [CartController::class, 'checkout']);
    Route::get('/dashboard', [UserControl::class, 'dashboard']);
    Route::get('/checkout/choose', [CartController::class, 'payment_choice'])->name('payment.choice');
    Route::get('/orders/{id}/invoice', [CartController::class, 'invoice'])->name('order.invoice');
    Route::post('/orders/{id}/invoice-number', [CartController::class, 'set_invoice_number'])
    ->name('order.invoice.setnumber');
    Route::post('/order-item/{id}/update', [CartController::class, 'update_order_item'])
    ->name('order.item.update');
});
Route::post('/logout', [UserControl::class, 'logout'])->name('logout');

Route::get('/order/{id}', [CartController::class, 'order_view'])
    ->name('order.view');

Route::get('/checkout/address', [CartController::class, 'shipping_form'])->name('shipping.form');
Route::post('/checkout/address', [CartController::class, 'save_shipping'])->name('shipping.save');

Route::get('/checkout/razorpay', [CartController::class, 'checkout_razorpay'])->name('checkout.razorpay');
Route::post('/razorpay/verify', [CartController::class, 'razorpay_verify'])->name('razorpay.verify');

Route::get('/test-mail', function () {

    Mail::raw(
        'Laravel mail test is working!',
        function ($message) {

            $message
                ->to('yogeshkanzariya5@mail.com')
                ->subject('Laravel Test Email');

        }
    );

    return 'Mail sent';
});