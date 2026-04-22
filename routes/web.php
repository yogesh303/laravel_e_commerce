<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Products;
use App\Http\Controllers\UserControl;
use Illuminate\Support\Facades\Route;


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
    Route::view('/product_form','productform');
    Route::get('/product_list',[Products::class,'show']);
    Route::post('/add_product',[Products::class,'store']);
    Route::get('/edit_product/{id}',[Products::class,'edit']);
    Route::put('/update_product',[Products::class,'update']);
    Route::get('/delete_product/{id}',[Products::class,'delete']);
});

Route::middleware('auth')->group(function () {
    Route::get('/products',[Products::class,'products_card']);
});

Route::middleware('auth')->group(function () {
    Route::post('/add_cart',[CartController::class,'add_cart']); // set cart of user product add
    Route::get('/cart_items',[CartController::class,'cart_items']); // display user cart
    Route::post('/add_quantity',[CartController::class,'add_quantity']); // improve quantity
    Route::post('/order',[CartController::class,'order']); // sussec order and reduce product quantity
    Route::get('/orders',[CartController::class,'order_list']); 
});
Route::post('/logout', [UserControl::class, 'logout'])->name('logout');
