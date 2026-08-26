<?php

use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/add-to-cart', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'viewCart']);
Route::get('/checkout', [CartController::class, 'viewCheckout']);
Route::post('/place-order', [CartController::class, 'placeOrder']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::redirect('/admin', '/admin/navigation');

    Route::get('/admin/navigation', [NavigationController::class, 'index'])->name('admin.main-nav');
    Route::post('/admin/navigation', [NavigationController::class, 'reorder'])->name('admin.main-nav.reorder');

    Route::get('/admin/design-symbols', function () {
        return view('admin.design-symbols');
    })->name('admin.design-symbols');

    Route::get('/admin/pre-orders', function () {
        return view('admin.pre-orders');
    })->name('admin.pre-orders');
});