<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BicycleController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');

    // Categories
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Bicycles
    Route::get('/categories/{category}/bicycles', [BicycleController::class, 'index'])->name('bicycles.index');
    Route::get('/categories/{category}/bicycles/create', [BicycleController::class, 'create'])->name('bicycles.create');
    Route::post('/categories/{category}/bicycles', [BicycleController::class, 'store'])->name('bicycles.store');
    Route::get('/bicycles/{bicycle}/edit', [BicycleController::class, 'edit'])->name('bicycles.edit');
    Route::put('/bicycles/{bicycle}', [BicycleController::class, 'update'])->name('bicycles.update');
    Route::patch('/bicycles/{bicycle}/status', [BicycleController::class, 'status'])->name('bicycles.status');
    Route::delete('/bicycles/{bicycle}', [BicycleController::class, 'destroy'])->name('bicycles.destroy');

    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
