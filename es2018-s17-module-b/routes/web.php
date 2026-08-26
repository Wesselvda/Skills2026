<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/add-to-cart', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'viewCart']);
Route::get('/checkout', [CartController::class, 'viewCheckout']);
Route::post('/place-order', [CartController::class, 'placeOrder']);