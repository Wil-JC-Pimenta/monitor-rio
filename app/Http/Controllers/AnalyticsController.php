<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\RiverData;
use App\Services\AnaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    protected $anaApiService;

    public function __construct(AnaApiService $anaApiService)
    {
        $this->anaApiService = $anaApiService;
    }

    public function index()
    {
        try {
            // Busca estações do Piracicaba
            $piracicabaStations = $this->anaApiService->fetchPiracicabaStations();
            
            // Processa dados para análise
            $allData = collect();
            $stations = collect();

            foreach ($piracicabaStations as $stationInfo) {
                $stationCode = $stationInfo['Codigo'];
                
                // Cria objeto de estação
                $station = (object)[
                    'id' => $stationCode,
                    'name' => $stationInfo['Nome'],
                    'code' => $stationCode,
                    'location' => $stationInfo['Municipio'] . ', ' . $stationInfo['UF'],
                    'status' => 'active',
                ];
                
                $stations->push($station);

                // Busca dados da estação (últimos 30 dias para análise)
                $stationData = $this->anaApiService->fetchStationData(
                    $stationCode,
                    now()->subDays(30),
                    now()
                );

                if ($stationData && !empty($stationData)) {
                    // Processa dados da estação
                    foreach ($stationData as $record) {
                        $allData->push((object)[
                            'nivel' => $record['nivel'] ?? null,
                            'vazao' => $record['vazao'] ?? null,
                            'chuva' => $record['chuva'] ?? null,
                            'station_code' => $stationCode,
                        ]);
                    }
                }
            }

            // Calcula métricas principais
            $nivelData = $allData->where('nivel', '!=', null)->where('nivel', '>', 0);
            $vazaoData = $allData->where('vazao', '!=', null)->where('vazao', '>', 0);
            $chuvaData = $allData->where('chuva', '!=', null);
            
            $maxNivel = $nivelData->count() > 0 ? $nivelData->max('nivel') : 0;
            $minNivel = $nivelData->count() > 0 ? $nivelData->min('nivel') : 0;
            $maxVazao = $vazaoData->count() > 0 ? $vazaoData->max('vazao') : 0;
            $totalChuva = $chuvaData->count() > 0 ? $chuvaData->sum('chuva') : 0;
            $avgNivel = $nivelData->count() > 0 ? $nivelData->avg('nivel') : 0;

            // Calcula estatísticas por estação
            $stationsWithStats = $stations->map(function($station) use ($allData) {
                $stationData = $allData->where('station_code', $station->code);
                $nivelData = $stationData->where('nivel', '!=', null)->where('nivel', '>', 0);
                $vazaoData = $stationData->where('vazao', '!=', null)->where('vazao', '>', 0);
                $chuvaData = $stationData->where('chuva', '!=', null);
                
                return (object)[
                    'id' => $station->id,
                    'name' => $station->name,
                    'code' => $station->code,
                    'location' => $station->location,
                    'status' => $station->status,
                    'river_data_count' => $stationData->count(),
                    'avg_nivel' => round($nivelData->count() > 0 ? $nivelData->avg('nivel') : 0, 2),
                    'avg_vazao' => round($vazaoData->count() > 0 ? $vazaoData->avg('vazao') : 0, 1),
                    'total_chuva' => round($chuvaData->count() > 0 ? $chuvaData->sum('chuva') : 0, 1),
                ];
            });

            return view('analytics', compact(
                'maxNivel',
                'minNivel', 
                'maxVazao',
                'totalChuva',
                'avgNivel'
            ))->with('stations', $stationsWithStats);

        } catch (\Exception $e) {
            Log::error('Erro no AnalyticsController: ' . $e->getMessage());
            
            // Fallback para dados mock
            return $this->getMockAnalytics();
        }
    }

    private function getMockAnalytics()
    {
        $stations = collect([
            (object)[
                'id' => 1,
                'name' => 'Rio Piracicaba - Estação Vale do Aço',
                'code' => 'PIR001',
                'location' => 'Vale do Aço, MG',
                'status' => 'active',
                'river_data_count' => 150,
                'avg_nivel' => 2.45,
                'avg_vazao' => 15.2,
                'total_chuva' => 25.8,
            ],
            (object)[
                'id' => 2,
                'name' => 'Rio Piracicaba - Estação Centro',
                'code' => 'PIR002',
                'location' => 'Centro, MG',
                'status' => 'active',
                'river_data_count' => 200,
                'avg_nivel' => 2.38,
                'avg_vazao' => 14.8,
                'total_chuva' => 18.5,
            ],
        ]);

        return view('analytics-simple', [
            'maxNivel' => 3.2,
            'minNivel' => 1.8,
            'maxVazao' => 18.5,
            'totalChuva' => 44.3,
            'stations' => $stations,
            'avgNivel' => 2.42
        ]);
    }
}
