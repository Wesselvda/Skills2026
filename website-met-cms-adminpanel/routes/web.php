<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildsController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::get('/admin', [DashboardController::class, 'dashboard']);
    Route::get('/admin/builds', [BuildsController::class, 'showAdminBuildPage']);
    Route::get('/admin/builds/add', [BuildsController::class, 'showAddBuild']);
    Route::post('/admin/builds/add', [BuildsController::class, 'addBuild']);
    Route::get('/admin/builds/{build}', [BuildsController::class, 'showEditBuild']);
    Route::post('/admin/builds/{build}', [BuildsController::class, 'editBuild']);
    Route::delete('/admin/builds/{build}', [BuildsController::class, 'deleteBuild']);
    Route::get('/admin/pages', [PageController::class, 'showAdminPages']);
    Route::get('/admin/pages/{page}', [PageController::class, 'showPageEditor']);
    Route::post('/admin/pages/{page}', [PageController::class, 'updatePage']);
    Route::get('/admin/competitions', [CompetitionController::class, 'showAdminCompetitionPage']);
    Route::get('/admin/competitions/add', [CompetitionController::class, 'showAddCompetition']);
    Route::post('/admin/competitions/add', [CompetitionController::class, 'addCompetition']);
    Route::get('/admin/competitions/{competition}', [CompetitionController::class, 'showEditCompetition']);
    Route::post('/admin/competitions/{competition}', [CompetitionController::class, 'editCompetition']);
    Route::delete('/admin/competitions/{competition}', [CompetitionController::class, 'deleteCompetition']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/projects/{slug}', [BuildsController::class, 'showBuild']);
Route::get('/{slug}', [PageController::class, 'viewPage']);
