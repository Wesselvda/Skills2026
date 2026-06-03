<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Middleware\isAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'handleLogin']);
Route::get('/login-code', [AuthController::class, 'handleLoginCode'])->name('logincode');
Route::get('/mock-emails', [GeneralController::class, 'mockEmails']);

Route::middleware([isAuthenticated::class])->group(function () {
    Route::get('/', [GeneralController::class, 'home']);
    // Route::get('/investors', [GeneralController::class, 'investors']);
    // Route::get('/visitor-tours', [GeneralController::class, 'visitorTours']);
});
