<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RiverDataController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/stations', [StationController::class, 'index'])->name('stations');
Route::get('/data', [DataController::class, 'index'])->name('data');
Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
Route::get('/analytics-simple', function () {
    return view('analytics-simple');
})->name('analytics-simple');
Route::get('/analytics-test', [AnalyticsController::class, 'index'])->name('analytics-test');

// API endpoints para estações
Route::prefix('api')->name('api.')->group(function () {
    Route::post('river-data', [RiverDataController::class, 'apiStore'])->name('river-data.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
