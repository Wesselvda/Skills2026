<?php

use App\Http\Controllers\Admin\DesignController;
use App\Http\Controllers\Admin\NavigationController;
use App\Http\Controllers\Admin\PreOrderController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/add-to-cart', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'viewCart'])->name('cart');
Route::get('/checkout', [CartController::class, 'viewCheckout'])->name('checkout');
Route::post('/place-order', [CartController::class, 'placeOrder']);

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::redirect('/admin', '/admin/navigation');

    Route::get('/admin/navigation', [NavigationController::class, 'index'])->name('admin.main-nav');
    Route::post('/admin/navigation', [NavigationController::class, 'reorder'])->name('admin.main-nav.reorder');

    Route::get('/admin/design-symbols', [DesignController::class, 'index'])->name('admin.design-symbols');
    Route::post('/admin/design-symbols', [DesignController::class, 'store'])->name('admin.design-symbols.store');
    Route::post('/admin/design-symbols/{design}/toggle-active', [DesignController::class, 'toggleActive'])->name('admin.design-symbols.toggle-active');
    Route::delete('/admin/design-symbols/{design}', [DesignController::class, 'destroy'])->name('admin.design-symbols.destroy');

    Route::get('/admin/pre-orders', [PreOrderController::class, 'index'])->name('admin.pre-orders');
    Route::get('/admin/pre-orders/{order}', [PreOrderController::class, 'show'])->name('admin.pre-orders.show');
    Route::post('/admin/pre-orders/{order}/status', [PreOrderController::class, 'updateStatus'])->name('admin.pre-orders.status');
});