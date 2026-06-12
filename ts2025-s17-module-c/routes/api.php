<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BicycleController;
use App\Http\Controllers\ExternalServiceController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/sign-in', [AuthController::class, 'signIn']);
    Route::post('/auth/sign-up', [AuthController::class, 'signUp']);
    Route::post('/auth/sign-out', [AuthController::class, 'signOut']);
    Route::get('/auth/oauth', [AuthController::class, 'getOAuthLink']);
    Route::post('/auth/oauth', [AuthController::class, 'loginOAuth']);

    Route::get('/users/me', [UserController::class, 'getUser']);
    Route::get('/users/me/bicycles', [UserController::class, 'getMyBicycles']);
    Route::get('/users/me/transactions', [UserController::class, 'getMyPayments']);
    Route::post('/users/me/transactions', [PayController::class, 'replenishment']);
    Route::get('/users/me/rental', [UserController::class, 'getCurrentRent']);
    Route::post('/users/me/rental', [UserController::class, 'rent']);
    Route::post('/users/me/rental/complete', [UserController::class, 'finish']);
    Route::get('/users/me/work', [UserController::class, 'getWork']);

    Route::get('/bicycles', [BicycleController::class, 'getBicycles']);
    Route::get('/bicycles/{bicycleId}', [BicycleController::class, 'getBicycle']);
    Route::post('/bicycles/{bicycleId}/repair', [BicycleController::class, 'repairBicycle']);
    Route::get('/bicycles/{bicycleId}/rentals', [BicycleController::class, 'getBicycleBookings']);
    Route::post('/bicycles/{bicycleId}/rentals/{rentalId}/rate', [BicycleController::class, 'rateBooking']);
    Route::get('/bicycles/{bicycleId}/tariffs', [TariffController::class, 'getBicycleTariffs']);
    Route::get('/bicycles/{bicycleId}/tariffs/{tariffId}/price', [TariffController::class, 'getCurrentPrice']);

    Route::get('/histories', [HistoryController::class, 'getHistories']);
    Route::get('/external-services/weather', [ExternalServiceController::class, 'getCurrentWeather']);
    Route::get('/external-services/city', [ExternalServiceController::class, 'getCurrentTraffic']);
    Route::get('/works', [WorkController::class, 'getWorks']);
    Route::post('/works/{userId}', [WorkController::class, 'sendRequest']);
});
