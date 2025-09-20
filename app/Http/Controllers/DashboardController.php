<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\RiverData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Dados mock para demonstração (sem banco de dados)
        $stations = collect([
            (object)['id' => 1, 'name' => 'Rio Piracicaba - Estação 1', 'code' => 'PIR001', 'location' => 'Vale do Aço', 'status' => 'active', 'river_data_count' => 150],
            (object)['id' => 2, 'name' => 'Rio Piracicaba - Estação 2', 'code' => 'PIR002', 'location' => 'Centro', 'status' => 'active', 'river_data_count' => 200],
            (object)['id' => 3, 'name' => 'Rio Piracicaba - Estação 3', 'code' => 'PIR003', 'location' => 'Zona Rural', 'status' => 'inactive', 'river_data_count' => 75],
        ]);
        
        $totalStations = $stations->count();
        $activeStations = $stations->where('status', 'active')->count();
        $totalMeasurements = $stations->sum('river_data_count');
        
        // Dados recentes mock
        $recentData = collect([
            (object)['id' => 1, 'data_medicao' => now()->subHours(1), 'nivel' => 2.5, 'vazao' => 15.2, 'chuva' => 0.5, 'station' => (object)['name' => 'Rio Piracicaba - Estação 1']],
            (object)['id' => 2, 'data_medicao' => now()->subHours(2), 'nivel' => 2.3, 'vazao' => 14.8, 'chuva' => 0.2, 'station' => (object)['name' => 'Rio Piracicaba - Estação 2']],
            (object)['id' => 3, 'data_medicao' => now()->subHours(3), 'nivel' => 2.7, 'vazao' => 16.1, 'chuva' => 1.2, 'station' => (object)['name' => 'Rio Piracicaba - Estação 1']],
        ]);
        
        // Estatísticas mock
        $maxNivel = 3.2;
        $avgNivel = 2.5;
        $maxVazao = 18.5;
        $totalChuva = 15.8;
        
        // Dados específicos do Rio Piracicaba
        $piracicabaStations = $stations->where('name', 'like', '%Piracicaba%');
        $piracicabaData = $recentData;
        
        // Dados para gráfico linear mock - últimas 24 horas
        $chartData = collect();
        for ($i = 23; $i >= 0; $i--) {
            $chartData->push((object)[
                'data_medicao' => now()->subHours($i),
                'nivel' => round(2.0 + (rand(0, 20) / 10), 2),
                'vazao' => round(10 + (rand(0, 15)), 1)
            ]);
        }
        
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

