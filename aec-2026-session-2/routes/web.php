<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GeneralController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::group(['middleware' => 'is_authenticated'], function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});