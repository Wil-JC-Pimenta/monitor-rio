<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\RiverData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('riverData')->get();
        $totalStations = $stations->count();
        $activeStations = $stations->where('status', 'active')->count();
        $totalMeasurements = $stations->sum('river_data_count');
        
        // Dados recentes - sem gráficos
        $recentData = RiverData::with('station')
            ->orderBy('data_medicao', 'desc')
            ->limit(20)
            ->get();
        
        // Estatísticas gerais
        $maxNivel = RiverData::max('nivel') ?: 0;
        $avgNivel = RiverData::avg('nivel') ?: 0;
        $maxVazao = RiverData::max('vazao') ?: 0;
        $totalChuva = RiverData::sum('chuva') ?: 0;
        
        return view('dashboard', compact(
            'stations', 
            'totalStations', 
            'activeStations', 
            'totalMeasurements',
            'recentData',
            'maxNivel',
            'avgNivel',
            'maxVazao',
            'totalChuva'
        ));
    }
}

