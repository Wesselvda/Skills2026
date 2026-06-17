<?php

use App\Http\Controllers\DiscountController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RngSeedController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

/**
 * @group Health
 *
 * Health Check
 */
Route::get('/health', function () {
    return response()->json(['status' => 'up']);
});

Route::prefix('translation')->group(function () {
    Route::post('/translate', [TranslationController::class, 'translate']);
    Route::post('/batch', [TranslationController::class, 'batch']);
});

Route::prefix('inventory')->group(function () {
    Route::get('/stores/for-book/{bookId}', [InventoryController::class, 'getStores']);
    Route::get('/stores/{storeId}/books/{bookId}', [InventoryController::class, 'getAvailability']);
});

Route::prefix('moderation')->group(function () {
    Route::post('/check', [ModerationController::class, 'check']);
});

Route::prefix('payment')->group(function () {
    Route::post('/session', [PaymentController::class, 'createSession']);
    Route::get('/session/{sessionId}', [PaymentController::class, 'getSession']);
    Route::delete('/session/{sessionId}', [PaymentController::class, 'deleteSession']);
});

Route::get('/discounts', [DiscountController::class, 'getRules']);

Route::prefix('hidden')->group(function () {
    Route::post('/seed', RngSeedController::class);
});
