<?php

use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/courses', [CourseController::class, 'index']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{id}', [CourseController::class, 'update']);
Route::get('/courses/{slug}', [CourseController::class, 'show']);
Route::get('/courses/{slug}/chapters/{chapterId}', [ChapterController::class, 'show']);
Route::post('/courses/{slug}/chapters/{chapterId}/quiz/validate', [QuizController::class, 'validateAnswers']);
