<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('riverData')
            ->with(['riverData' => function($query) {
                $query->latest('data_medicao')->limit(1);
            }])
            ->get()
            ->map(function($station) {
                $lastData = $station->riverData->first();
                $avgNivel = $station->riverData()->whereNotNull('nivel')->avg('nivel') ?: 0;
                $avgVazao = $station->riverData()->whereNotNull('vazao')->avg('vazao') ?: 0;
                
                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'code' => $station->code,
                    'location' => $station->location,
                    'status' => $station->status,
                    'measurements_count' => $station->river_data_count,
                    'avg_nivel' => round($avgNivel, 2),
                    'avg_vazao' => round($avgVazao, 1),
                    'last_measurement' => $lastData ? $lastData->data_medicao : null,
                ];
            });

        $totalStations = $stations->count();
        $activeStations = $stations->where('status', 'active')->count();
        $totalMeasurements = $stations->sum('measurements_count');

        return view('stations', compact('stations', 'totalStations', 'activeStations', 'totalMeasurements'));
    }
}

