<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\RiverData;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        try {
            // Métricas principais - otimizadas
            $maxNivel = RiverData::max('nivel') ?: 0;
            $minNivel = RiverData::min('nivel') ?: 0;
            $maxVazao = RiverData::max('vazao') ?: 0;
            $totalChuva = RiverData::sum('chuva') ?: 0;
            
            // Dados simplificados - sem gráficos
            
            // Estações com estatísticas - otimizado
            $stations = Station::withCount('riverData')->get()->map(function($station) {
                $avgNivel = RiverData::where('station_id', $station->id)
                    ->whereNotNull('nivel')
                    ->avg('nivel') ?: 0;
                $avgVazao = RiverData::where('station_id', $station->id)
                    ->whereNotNull('vazao')
                    ->avg('vazao') ?: 0;
                $totalChuva = RiverData::where('station_id', $station->id)
                    ->whereNotNull('chuva')
                    ->sum('chuva') ?: 0;
                    
                return [
                    'id' => $station->id,
                    'name' => $station->name,
                    'code' => $station->code,
                    'location' => $station->location,
                    'status' => $station->status,
                    'river_data_count' => $station->river_data_count,
                    'avg_nivel' => round($avgNivel, 2),
                    'avg_vazao' => round($avgVazao, 1),
                    'total_chuva' => round($totalChuva, 1),
                ];
            });
            
            // Alertas
            $avgNivel = RiverData::avg('nivel') ?: 0;
            
            return view('analytics', compact(
                'maxNivel',
                'minNivel', 
                'maxVazao',
                'totalChuva',
                'stations',
                'avgNivel'
            ));
        } catch (\Exception $e) {
            \Log::error('Erro no AnalyticsController: ' . $e->getMessage());
            return view('analytics-simple', [
                'maxNivel' => 0,
                'minNivel' => 0,
                'maxVazao' => 0,
                'totalChuva' => 0,
                'stations' => []
            ]);
        }
    }
}
