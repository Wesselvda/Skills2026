<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\TourController;
use App\Http\Middleware\isAuthenticated;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login']);
Route::post('/login', [AuthController::class, 'handleLogin']);
Route::get('/login-code', [AuthController::class, 'handleLoginCode'])->name('logincode');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/mock-emails', [GeneralController::class, 'mockEmails']);

Route::middleware([isAuthenticated::class])->group(function () {
    Route::get('/', [GeneralController::class, 'home']);
    Route::get('/investors', [InvestmentController::class, 'index']);
    Route::post('/investors', [InvestmentController::class, 'handleInvest']);
    Route::get('/sponsors', [InvestmentController::class, 'sponsorPage']);

    Route::get('/visitor-tours', [TourController::class, 'index']);
    Route::get('/visitor-tours/{tour}', [TourController::class, 'show'])->name('tours.show');

    Route::get('/admin', [GeneralController::class, 'admin']);
    Route::get('/admin/investments/{id}/approve', [InvestmentController::class, 'approveInvestment'])->name('admin.investments.approve');
    Route::get('/admin/investments/{id}/reject', [InvestmentController::class, 'rejectInvestment'])->name('admin.investments.reject');
    // Route::get('/investors', [GeneralController::class, 'investors']);
    // Route::get('/visitor-tours', [GeneralController::class, 'visitorTours']);
});
