<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HeritageController;

Route::get('/', [HeritageController::class, 'index']);
Route::get('/heritages/{slug}', [HeritageController::class, 'show'])->where('slug', '.*')->name('heritage.show');
