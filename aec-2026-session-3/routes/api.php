<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'show']);

Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/authors/{id}', [AuthorController::class, 'show']);

Route::get('/books', [BookController::class, 'index']);
Route::get('/books/latest', [BookController::class, 'latest']);
Route::get('/books/{id}', [BookController::class, 'show']);
Route::patch('/books/{id}/stock', [BookController::class, 'updateStock']);
Route::get('/books/{id}/availability', [BookController::class, 'availability']);
Route::post('/books/{id}/review', [BookController::class, 'storeReview']);

Route::post('/cart', [CartController::class, 'store']);
Route::get('/cart/{id}', [CartController::class, 'show']);
Route::post('/cart/{id}/items', [CartController::class, 'storeItem']);
Route::patch('/cart/{id}/items/{itemId}', [CartController::class, 'updateItem']);
Route::delete('/cart/{id}/items/{itemId}', [CartController::class, 'destroyItem']);
Route::post('/cart/{id}/checkout/start', [CartController::class, 'startCheckout']);
Route::post('/cart/{id}/checkout/complete', [CartController::class, 'completeCheckout']);

Route::get('/payment/{sessionId}', function (string $sessionId) {
    $response = Http::get("http://localhost:3000/payment/".$sessionId);

    return response($response->body(), $response->status())
        ->header('Content-Type', $response->header('Content-Type'));
});

Route::post('/payment/{sessionId}/process', function (Request $request, string $sessionId) {
    Http::asForm()->withoutRedirecting()->post("http://localhost:3000/payment/".$sessionId.'/process', $request->all());

    return response('', 302);
});

Route::post('/payment/{sessionId}/fail', function (Request $request, string $sessionId) {
    Http::withoutRedirecting()->post("http://localhost:3000/payment/".$sessionId.'/fail', $request->query());

    return response('', 302);
});
