<?php

namespace App\Http\Controllers;

use App\Models\RiverData;
use App\Models\Station;
use App\Services\AnaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    protected $anaApiService;

    public function __construct(AnaApiService $anaApiService)
    {
        $this->anaApiService = $anaApiService;
    }

    public function index(Request $request)
    {
        try {
            // Busca estações do Piracicaba primeiro
            $piracicabaStations = $this->anaApiService->fetchPiracicabaStations();
            
            $data = collect();
            $stations = collect();

            // Processa cada estação
            foreach ($piracicabaStations as $stationInfo) {
                $stationCode = $stationInfo['Codigo'];
                
                // Cria objeto de estação
                $station = (object)[
                    'id' => $stationCode,
                    'name' => $stationInfo['Nome'],
                    'code' => $stationCode,
                    'location' => $stationInfo['Municipio'] . ', ' . $stationInfo['UF'],
                ];
                
                $stations->push($station);

                // Busca dados da estação
                $stationData = $this->anaApiService->fetchStationData(
                    $stationCode,
                    $request->filled('date_from') ? \Carbon\Carbon::parse($request->date_from) : now()->subDays(7),
                    $request->filled('date_to') ? \Carbon\Carbon::parse($request->date_to) : now()
                );

                if ($stationData && !empty($stationData)) {
                    // Processa dados da estação
                    foreach ($stationData as $record) {
                        $data->push((object)[
                            'id' => uniqid(),
                            'station' => $station,
                            'nivel' => $record['nivel'] ?? null,
                            'vazao' => $record['vazao'] ?? null,
                            'chuva' => $record['chuva'] ?? null,
                            'data_medicao' => isset($record['data_medicao']) ? \Carbon\Carbon::parse($record['data_medicao']) : now(),
                        ]);
                    }
                }
            }

            // Ordena por data mais recente
            $data = $data->sortByDesc('data_medicao')->values();

            // Aplica filtros se necessário
            if ($request->filled('station_id')) {
                $data = $data->where('station.code', $request->station_id);
            }

            // Paginação simples (primeiros 100 registros)
            $data = $data->take(100);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados da API ANA: ' . $e->getMessage());
            
            // Fallback para dados mock
            $data = $this->getMockData();
            $stations = $this->getMockStations();
        }

        return view('data', compact('data', 'stations'));
    }

    private function getMockData()
    {
        return collect([
            (object)[
                'id' => 1,
                'station' => (object)['name' => 'Rio Piracicaba - Estação 1', 'code' => 'PIR001'],
                'nivel' => 2.45,
                'vazao' => 15.2,
                'chuva' => 0.5,
                'data_medicao' => now()->subHours(1),
            ],
            (object)[
                'id' => 2,
                'station' => (object)['name' => 'Rio Piracicaba - Estação 2', 'code' => 'PIR002'],
                'nivel' => 2.38,
                'vazao' => 14.8,
                'chuva' => 0.2,
                'data_medicao' => now()->subHours(2),
            ],
            (object)[
                'id' => 3,
                'station' => (object)['name' => 'Rio Piracicaba - Estação 1', 'code' => 'PIR001'],
                'nivel' => 2.67,
                'vazao' => 16.1,
                'chuva' => 1.2,
                'data_medicao' => now()->subHours(3),
            ],
        ]);
    }

    private function getMockStations()
    {
        return collect([
            (object)['id' => 'PIR001', 'name' => 'Rio Piracicaba - Estação Vale do Aço', 'code' => 'PIR001'],
            (object)['id' => 'PIR002', 'name' => 'Rio Piracicaba - Estação Centro', 'code' => 'PIR002'],
            (object)['id' => 'PIR003', 'name' => 'Rio Piracicaba - Estação Zona Rural', 'code' => 'PIR003'],
        ]);
    }
}

