<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProductApiController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLogin']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
Route::post('/login', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

Route::get('/products.json', [ProductApiController::class, 'index'])->name('api.products.index');
Route::get('/products/{gtin}.json', [ProductApiController::class, 'show'])->name('api.products.show');

Route::get('/gtin-verification', [PublicController::class, 'showVerification'])->name('public.verify.form');
Route::post('/gtin-verification', [PublicController::class, 'verify'])->name('public.verify.submit');
Route::get('/01/{gtin}', [PublicController::class, 'product'])->name('public.product');

Route::middleware('admin.passphrase')->group(function (): void {
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/companies/deactivated', [CompanyController::class, 'deactivated'])->name('companies.deactivated');
    Route::get('/companies/new', [CompanyController::class, 'create'])->name('companies.create');
    Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::patch('/companies/{company}/deactivate', [CompanyController::class, 'deactivate'])->name('companies.deactivate');

    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/new', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::patch('/products/{product}/hide', [ProductController::class, 'hide'])->name('products.hide');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/products/{product}/image', [ProductController::class, 'removeImage'])->name('products.image.remove');
});
