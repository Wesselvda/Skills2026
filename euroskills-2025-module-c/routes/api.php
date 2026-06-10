<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TurbineController;
use App\Http\Middleware\IsAuthenticated;
use Illuminate\Support\Facades\Route;

// Public

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);

// Turbines
Route::get('/turbines', [TurbineController::class, 'turbines']);
Route::get('/turbines/{id}/status', [TurbineController::class, 'status']);
Route::get('/turbines/{id}/actions', [TurbineController::class, 'actions']);

// Authenticated
Route::middleware([IsAuthenticated::class])->group(function () {
    // Auth
    Route::post('/auth/assign-role', [AuthController::class, 'assignRole']);

    // Turbines
    Route::post('/turbines/{id}/control', [TurbineController::class, 'control']);
    Route::post('/turbines/{id}/start', [TurbineController::class, 'start']);
    Route::post('/turbines/{id}/shutdown', [TurbineController::class, 'shutdown']);
    Route::post('/turbines/{id}/maintenance', [TurbineController::class, 'maintenance']);
    Route::get('/turbines/{id}/logs', [TurbineController::class, 'logs']);

    // Alerts
    Route::get('/alerts', [AlertController::class, 'alerts']);
    Route::get('/alerts/{id}/ack', [AlertController::class, 'ack']);
});