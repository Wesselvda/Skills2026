<?php

use App\Http\Controllers\HeritageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HeritageController::class, 'index'])->name('heritages.index');
Route::get('/tags/{tag}', [HeritageController::class, 'tag'])->name('heritages.tag');
Route::get('/search/{keywords?}', [HeritageController::class, 'search'])
    ->where('keywords', '.*')
    ->name('heritages.search');
Route::get('/heritages-image/{filename}', [HeritageController::class, 'image'])
    ->where('filename', '.*')
    ->name('heritages.image');
Route::get('/heritages/{path?}', [HeritageController::class, 'heritagePath'])
    ->where('path', '.*')
    ->name('heritages.path');
