<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Products;
use App\Http\Controllers\UserControl;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('login');
})->name('login');
Route::post('/login_user',[UserControl::class,'login_user']);
Route::view('/signup','signup');
Route::post('/signup_user',[UserControl::class,'signup_user']);

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

Route::get('/product/{id}', [Products::class, 'productDetails'])->name('product.details');
Route::get('/customize/{id}', [CartController::class, 'customize'])->name('product.customize');
Route::post('/customize/{id}/save', [CartController::class, 'saveCustomization'])->name('product.customize.save');

Route::middleware('auth')->group(function () {
    Route::get('/products',[Products::class,'products_card']);
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
});
Route::post('/logout', [UserControl::class, 'logout'])->name('logout');

Route::get('/product/{id}/customize', [CartController::class, 'customize'])
    ->name('product.customize');

Route::post('/product/{id}/customize', [CartController::class, 'saveCustomization'])
    ->name('product.customize.save');

Route::get('/order/{id}', [CartController::class, 'order_view'])
    ->name('order.view');

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