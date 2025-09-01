<?php

namespace App\Http\Controllers;

use App\Models\RiverData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class RiverDataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $riverData = RiverData::orderBy('data_medicao', 'desc')
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
}
