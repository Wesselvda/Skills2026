<?php

use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/navigation-items', [HomeController::class, 'navigationItems']);
Route::get('/customizer-options', [HomeController::class, 'customizerOptions']);
