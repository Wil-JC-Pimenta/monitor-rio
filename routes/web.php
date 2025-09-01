<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\RiverDataController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Rotas do sistema de monitoramento do rio
    Route::prefix('river')->name('river.')->group(function () {
        Route::get('monitor', [RiverDataController::class, 'monitor'])->name('monitor');
        Route::resource('data', RiverDataController::class);
        Route::get('chart-data', [RiverDataController::class, 'chartData'])->name('chart-data');
    });
});

// API endpoints para estações
Route::prefix('api')->name('api.')->group(function () {
    Route::post('river-data', [RiverDataController::class, 'apiStore'])->name('river-data.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
