<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
