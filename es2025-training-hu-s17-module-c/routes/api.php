<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\MentorController;
use App\Http\Middleware\ApiAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // User Management
    Route::post('/users/register', [AuthController::class, 'register']);
    Route::post('/users/login', [AuthController::class, 'login']);

    Route::middleware([ApiAuthenticated::class])->group(function () {
        // User Management
        Route::post('/users/logout', [AuthController::class, 'logout']);
        Route::get('/users/me', [AuthController::class, 'me']);

        // Course Management
        Route::get('/courses', [CourseController::class, 'index']);
        Route::get('/courses/{id}', [CourseController::class, 'show']);
        Route::post('/courses/{id}/enroll', [CourseController::class, 'enroll']);
        Route::post('/courses/{id}/chapters/{chapterId}/complete', [CourseController::class, 'complete']);

        // Mentor Sessions
        Route::get('/mentors/sessions', [MentorController::class, 'index']);
        Route::post('/mentors/sessions/{id}/book', [MentorController::class, 'book']);
    });
});
