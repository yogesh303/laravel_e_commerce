<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Products;
use App\Http\Controllers\UserControl;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SearchController;


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
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::put('/subcategories/{id}', [CategoryController::class, 'updateSubcategory']);
});

// Public: anyone (guest or logged-in) can browse products and view product details
Route::get('/products', [Products::class, 'products_card'])->name('products.index');
Route::get('/product/{id}', [Products::class, 'productDetails'])->name('product.details');
Route::get('/customize/{id}', [CartController::class, 'customize'])->name('product.customize');
Route::post('/customize/{id}/save', [CartController::class, 'saveCustomization'])->name('product.customize.save');
Route::post('/product/{id}/customize/finalize', [CartController::class, 'finalize_customization'])
        ->name('product.customize.finalize');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
// Static pages — no controller needed since these don't process any input
Route::view('/about', 'about')->name('about');
Route::view('/privacy-policy', 'privacy')->name('privacy');
Route::view('/terms-and-conditions', 'terms')->name('terms');
// Live search suggestions (used by the header search bar's dropdown)
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');

// Customizing a product still needs an account (it saves to the user's cart)
Route::middleware('auth')->group(function () {
    Route::post('/cart/update-remarks', [CartController::class, 'update_remarks'])->name('cart.updateRemarks');
    Route::post('/order-item/{id}/delete-files', [CartController::class, 'delete_order_item_files'])
    ->name('orderitem.deleteFiles');
});


// AJAX: fetch subcategories for a given category (used by the product form dropdown)
Route::get('/get-subcategories/{category_id}', [CategoryController::class, 'getSubcategories']);
Route::post('/add_cart',[CartController::class,'add_cart']); // set cart of user product add
Route::get('/cart',[CartController::class,'cart']); // display user cart
Route::post('/add_quantity',[CartController::class,'add_quantity']); // improve quantity
Route::get('/cart-remarks/{product}', [CartController::class, 'cart_remarks_form'])->name('cart.remarks.form');

Route::middleware('auth')->group(function () {
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
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('settings.update');
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