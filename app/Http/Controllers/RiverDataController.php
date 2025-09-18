<?php

namespace App\Http\Controllers;

use App\Models\RiverData;
use App\Models\Station;
use App\Services\AnaApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RiverDataController extends Controller
{
    private AnaApiService $anaService;

    public function __construct(AnaApiService $anaService)
    {
        $this->anaService = $anaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $riverData = RiverData::with('station')
            ->orderBy('data_medicao', 'desc')
            ->paginate(20);

        return Inertia::render('river-data/index', [
            'riverData' => $riverData,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('river-data/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'station_id' => 'required|string|max:255',
            'nivel' => 'nullable|numeric|min:0',
            'vazao' => 'nullable|numeric|min:0',
            'chuva' => 'nullable|numeric|min:0',
            'data_medicao' => 'required|date',
        ]);

        $riverData = RiverData::create($validated);

        return response()->json([
            'message' => 'Dados do rio salvos com sucesso!',
            'data' => $riverData,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RiverData $riverData): Response
    {
        return Inertia::render('river-data/show', [
            'riverData' => $riverData,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RiverData $riverData): Response
    {
        return Inertia::render('river-data/edit', [
            'riverData' => $riverData,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RiverData $riverData): JsonResponse
    {
        $validated = $request->validate([
            'station_id' => 'required|string|max:255',
            'nivel' => 'nullable|numeric|min:0',
            'vazao' => 'nullable|numeric|min:0',
            'chuva' => 'nullable|numeric|min:0',
            'data_medicao' => 'required|date',
        ]);

        $riverData->update($validated);

        return response()->json([
            'message' => 'Dados do rio atualizados com sucesso!',
            'data' => $riverData,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RiverData $riverData): JsonResponse
    {
        $riverData->delete();

        return response()->json([
            'message' => 'Dados do rio removidos com sucesso!',
        ]);
    }

    /**
     * Display the monitoring dashboard.
     */
    public function monitor(): Response
    {
        // Dados das últimas 24 horas
        $recentData = RiverData::where('data_medicao', '>=', now()->subDay())
            ->orderBy('data_medicao', 'asc')
            ->get();

        // Estatísticas gerais
        $stats = [
            'total_stations' => RiverData::distinct('station_id')->count(),
            'latest_measurement' => RiverData::latest('data_medicao')->first(),
            'max_nivel' => RiverData::max('nivel'),
            'max_vazao' => RiverData::max('vazao'),
            'total_measurements' => RiverData::count(),
        ];

        return Inertia::render('river-data/monitor', [
            'recentData' => $recentData,
            'stats' => $stats,
        ]);
    }

    /**
     * API endpoint for receiving data from stations.
     */
    public function apiStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'station_id' => 'required|string|max:255',
            'nivel' => 'nullable|numeric|min:0',
            'vazao' => 'nullable|numeric|min:0',
            'chuva' => 'nullable|numeric|min:0',
            'data_medicao' => 'required|date',
        ]);

        $riverData = RiverData::create($validated);

        return response()->json([
            'message' => 'Dados recebidos com sucesso',
            'data' => $riverData,
        ], 201);
    }

    /**
     * Get data for charts.
     */
    public function chartData(Request $request): JsonResponse
    {
        $request->validate([
            'station_id' => 'nullable|string',
            'days' => 'nullable|integer|min:1|max:30',
        ]);

        $query = RiverData::query();
        
        if ($request->station_id) {
            $query->where('station_id', $request->station_id);
        }

        $days = $request->days ?? 7;
        $data = $query->where('data_medicao', '>=', now()->subDays($days))
            ->orderBy('data_medicao', 'asc')
            ->get(['nivel', 'vazao', 'chuva', 'data_medicao']);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * API REST - Lista dados hidrológicos
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $request->validate([
            'station_id' => 'nullable|integer',
            'station_code' => 'nullable|string',
            'days' => 'nullable|integer|min:1|max:365',
            'type' => 'nullable|in:nivel,vazao,chuva,all',
            'limit' => 'nullable|integer|min:1|max:1000',
        ]);

        $query = RiverData::with('station');

        // Filtro por estação
        if ($request->station_id) {
            $query->where('station_id', $request->station_id);
        } elseif ($request->station_code) {
            $station = Station::where('code', $request->station_code)->first();
            if ($station) {
                $query->where('station_id', $station->id);
            } else {
                return response()->json(['error' => 'Estação não encontrada'], 404);
            }
        }

        // Filtro por período
        $days = $request->days ?? 7;
        $query->where('data_medicao', '>=', now()->subDays($days));

        // Filtro por tipo de dados
        if ($request->type && $request->type !== 'all') {
            $query->whereNotNull($request->type);
        }

        $limit = $request->limit ?? 100;
        $data = $query->orderBy('data_medicao', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'total' => $data->count(),
                'period' => "Últimos {$days} dias",
                'filters' => $request->only(['station_id', 'station_code', 'days', 'type']),
            ],
        ]);
    }

    /**
     * API REST - Busca dados em tempo real da ANA
     */
    public function apiFetchFromAna(Request $request): JsonResponse
    {
        $request->validate([
            'station_code' => 'required|string',
            'days' => 'nullable|integer|min:1|max:30',
            'type' => 'nullable|in:niveis,vazoes,chuvas',
        ]);

        try {
            $stationCode = $request->station_code;
            $days = $request->days ?? 7;
            $type = $request->type ?? 'niveis';

            $startDate = now()->subDays($days);
            $endDate = now();

            // Busca dados da ANA
            $data = $this->anaService->fetchStationData($stationCode, $startDate, $endDate, $type);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado encontrado na ANA para esta estação',
                ], 404);
            }

            // Salva no banco de dados
            $savedCount = $this->anaService->saveDataToDatabase($data, $stationCode);

            return response()->json([
                'success' => true,
                'message' => "Dados atualizados com sucesso",
                'data' => [
                    'station_code' => $stationCode,
                    'records_saved' => $savedCount,
                    'period' => "{$startDate->format('d/m/Y')} a {$endDate->format('d/m/Y')}",
                    'type' => $type,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao buscar dados da ANA via API: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados da ANA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API REST - Lista estações disponíveis
     */
    public function apiStations(): JsonResponse
    {
        $stations = Station::active()
            ->withCount('riverData')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stations,
            'meta' => [
                'total' => $stations->count(),
                'active_stations' => $stations->where('status', 'active')->count(),
            ],
        ]);
    }

    /**
     * API REST - Busca estações do Rio Piracicaba na ANA
     */
    public function apiDiscoverPiracicabaStations(): JsonResponse
    {
        try {
            $stations = $this->anaService->fetchPiracicabaStations();

            return response()->json([
                'success' => true,
                'data' => $stations,
                'message' => 'Estações do Rio Piracicaba encontradas na ANA',
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao buscar estações do Piracicaba: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estações na ANA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API REST - Estatísticas dos dados
     */
    public function apiStats(): JsonResponse
    {
        $stats = [
            'total_measurements' => RiverData::count(),
            'total_stations' => Station::count(),
            'active_stations' => Station::active()->count(),
            'latest_measurement' => RiverData::latest('data_medicao')->first(),
            'measurements_today' => RiverData::whereDate('data_medicao', today())->count(),
            'measurements_this_week' => RiverData::where('data_medicao', '>=', now()->subWeek())->count(),
            'max_nivel' => RiverData::max('nivel'),
            'max_vazao' => RiverData::max('vazao'),
            'max_chuva' => RiverData::max('chuva'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * API REST - Força atualização de dados de uma estação
     */
    public function apiRefreshStation(Request $request): JsonResponse
    {
        $request->validate([
            'station_code' => 'required|string',
            'clear_cache' => 'nullable|boolean',
        ]);

        try {
            $stationCode = $request->station_code;
            $clearCache = $request->boolean('clear_cache', false);

            if ($clearCache) {
                $this->anaService->clearStationCache($stationCode);
            }

            // Busca dados dos últimos 7 dias
            $data = $this->anaService->fetchStationData($stationCode, now()->subDays(7), now());
            
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum dado encontrado na ANA',
                ], 404);
            }

            $savedCount = $this->anaService->saveDataToDatabase($data, $stationCode);

            return response()->json([
                'success' => true,
                'message' => "Estação atualizada com sucesso",
                'data' => [
                    'station_code' => $stationCode,
                    'records_saved' => $savedCount,
                    'cache_cleared' => $clearCache,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar estação {$request->station_code}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar estação',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
