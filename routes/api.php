<?php

use App\Http\Controllers\RiverDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aqui estão definidas as rotas da API para o Monitor Rio Piracicaba
|
*/

// Rotas básicas de dados hidrológicos
Route::get('/river-data', [RiverDataController::class, 'apiIndex']);
Route::get('/river-data/chart', [RiverDataController::class, 'chartData']);
Route::get('/river-data/stats', [RiverDataController::class, 'apiStats']);

// Rotas de estações
Route::get('/stations', [RiverDataController::class, 'apiStations']);
Route::get('/stations/discover-piracicaba', [RiverDataController::class, 'apiDiscoverPiracicabaStations']);

// Rotas de integração com ANA
Route::post('/ana/fetch', [RiverDataController::class, 'apiFetchFromAna']);
Route::post('/ana/refresh-station', [RiverDataController::class, 'apiRefreshStation']);

// Rotas CRUD básicas (se necessário)
Route::apiResource('river-data', RiverDataController::class)->except(['index']);
