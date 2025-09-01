<?php
use App\Http\Controllers\RiverDataController;

Route::get('/river-data', [RiverDataController::class, 'index']);
