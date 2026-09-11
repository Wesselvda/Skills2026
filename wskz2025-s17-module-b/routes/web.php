<?php

use App\Http\Controllers\AdvertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index']);
    Route::get('/categories', [AdvertController::class, 'showCategories'])->name('categories.index');
    Route::get('/categories/add', [AdvertController::class, 'showAddCategory'])->name('categories.add');
    Route::post('/categories/add', [AdvertController::class, 'addCategory'])->name('categories.add');
    Route::get('/categories/{id}', [AdvertController::class, 'editCategory'])->name('categories.edit');
    Route::put('/categories/{id}', [AdvertController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{id}', [AdvertController::class, 'destroyCategory'])->name('categories.destroy');

    Route::get('/users', [AuthController::class, 'showUserPage'])->name('users.index');

    Route::get('/logout', [AuthController::class, 'logout']);
});