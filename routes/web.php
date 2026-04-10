<?php

use App\Http\Controllers\Products;
use App\Http\Controllers\UserControl;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::view('/login','login');
Route::post('/login_user',[UserControl::class,'login_user']);
Route::view('/signup','signup');
Route::post('/signup_user',[UserControl::class,'signup_user']);

Route::view('/product_form','productform');
Route::get('/product_list',[Products::class,'show']);
Route::get('/products',[Products::class,'products_card']);
Route::post('/add_product',[Products::class,'store']);
Route::get('/edit_product/{id}',[Products::class,'edit']);
Route::put('/update_product',[Products::class,'update']);
Route::get('/delete_product/{id}',[Products::class,'delete']);

Route::post('/add_cart',[Products::class,'add_cart']); // set cart of user product add
Route::get('/cart_items',[Products::class,'cart_items']); // display user cart
Route::post('/order',[Products::class,'order']); // sussec order and reduce product quantity

Route::get('/order_list',[Products::class,'order_list']);
