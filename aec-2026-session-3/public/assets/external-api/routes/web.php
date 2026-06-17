<?php

use App\Http\Controllers\PaymentPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->redirectTo('/docs');
});

Route::prefix('payment')->name('payment.')->group(function () {
    Route::get('{sessionId}', [PaymentPageController::class, 'show'])->name('show');
    Route::post('{sessionId}/process', [PaymentPageController::class, 'process'])->name('process');
    Route::post('{sessionId}/fail', [PaymentPageController::class, 'fail'])->name('fail');
});
