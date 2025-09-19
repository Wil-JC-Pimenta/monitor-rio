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
        
        // Dados específicos do Rio Piracicaba
        $piracicabaStations = Station::where('name', 'like', '%Piracicaba%')->get();
        $piracicabaData = RiverData::whereHas('station', function($query) {
            $query->where('name', 'like', '%Piracicaba%');
        })->with('station')->orderBy('data_medicao', 'desc')->get();
        
        // Dados para gráfico linear - últimas 24 horas agrupadas por hora
        $chartData = RiverData::whereHas('station', function($query) {
            $query->where('name', 'like', '%Piracicaba%');
        })
        ->where('data_medicao', '>=', now()->subHours(24))
        ->orderBy('data_medicao')
        ->get(['data_medicao', 'nivel', 'vazao'])
        ->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->data_medicao)->format('Y-m-d H:00:00');
        })
        ->map(function($group) {
            return [
                'data_medicao' => $group->first()->data_medicao,
                'nivel' => round($group->avg('nivel'), 2),
                'vazao' => round($group->avg('vazao'), 1)
            ];
        })
        ->values()
        ->take(24); // Últimas 24 horas
        
        return view('dashboard', compact(
            'stations', 
            'totalStations', 
            'activeStations', 
            'totalMeasurements',
            'recentData',
            'maxNivel',
            'avgNivel',
            'maxVazao',
            'totalChuva',
            'piracicabaStations',
            'piracicabaData',
            'chartData'
        ));
    }
}

