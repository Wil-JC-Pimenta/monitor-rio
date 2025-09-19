@extends('layouts.app')

@section('title', 'Estações de Monitoramento - Monitor Rio Piracicaba')

@section('content')
@php
    // Load real data from database
    $stations = \App\Models\Station::withCount('riverData')->get();
    $totalData = \App\Models\RiverData::count();
    $activeStations = $stations->where('status', 'active')->count();
    
    // Get recent data for each station
    $stationsWithRecentData = $stations->map(function($station) {
        $recentData = \App\Models\RiverData::where('station_id', $station->id)
            ->orderBy('data_medicao', 'desc')
            ->limit(12)
            ->get();
        
        $station->recent_data = $recentData;
        $station->last_measurement = $recentData->first();
        return $station;
    });
@endphp

<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-satellite-dish text-green-600 mr-3"></i>
            Estações de Monitoramento
        </h1>
        <p class="text-gray-600">Rede de estações hidrológicas do Rio Piracicaba no Vale do Aço</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-satellite-dish text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Estações</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stations->count() }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Estações Ativas</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $activeStations }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Dados</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($totalData) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-orange-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Última Atualização</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ now()->format('H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Station Status Overview -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                Status das Estações
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stationsWithRecentData as $station)
                <div class="border rounded-lg p-4 {{ $station->status === 'active' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <div class="font-medium text-gray-900">{{ $station->name }}</div>
                            <div class="text-sm text-gray-500">{{ $station->code }}</div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $station->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mb-2">{{ $station->location }}</div>
                    <div class="text-xs text-gray-600">
                        <div>Medições: {{ $station->river_data_count }}</div>
                        @if($station->last_measurement)
                        <div>Última: {{ $station->last_measurement->data_medicao->format('d/m H:i') }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Station Details with Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        @foreach($stationsWithRecentData->take(6) as $station)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">{{ $station->name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $station->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $station->status === 'active' ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ $station->location }}</p>
            </div>
            <div class="p-6">
                <!-- Mini Chart -->
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Nível do Rio - Últimas 12 horas</h4>
                    <div class="h-32">
                        <canvas id="chart-{{ $station->id }}"></canvas>
                    </div>
                </div>
                
                <!-- Station Info -->
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Código:</span>
                        <span class="font-medium">{{ $station->code }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Medições:</span>
                        <span class="font-medium">{{ $station->river_data_count }}</span>
                    </div>
                    @if($station->last_measurement)
                    <div>
                        <span class="text-gray-500">Último Nível:</span>
                        <span class="font-medium">{{ number_format($station->last_measurement->nivel, 2) }}m</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Última Vazão:</span>
                        <span class="font-medium">{{ number_format($station->last_measurement->vazao, 1) }}m³/s</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
<script>
// Create mini charts for all stations
document.addEventListener('DOMContentLoaded', function() {
    @foreach($stationsWithRecentData as $station)
    createMiniChart({{ $station->id }}, @json($station->recent_data));
    @endforeach
});

function createMiniChart(stationId, recentData) {
    const ctx = document.getElementById(`chart-${stationId}`);
    if (!ctx) return;

    // Use real data from the station
    const labels = [];
    const data = [];
    
    if (recentData && recentData.length > 0) {
        recentData.forEach(item => {
            const date = new Date(item.data_medicao);
            labels.push(date.getHours() + 'h');
            data.push(item.nivel || 0);
        });
    } else {
        // Fallback to sample data if no real data
        const now = new Date();
        for (let i = 11; i >= 0; i--) {
            const date = new Date(now.getTime() - (i * 60 * 60 * 1000));
            labels.push(date.getHours() + 'h');
            data.push(2.0 + Math.random() * 1.5);
        }
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nível (m)',
                data: data,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    display: false
                },
                x: {
                    display: false
                }
            },
            elements: {
                point: {
                    radius: 0
                }
            }
        }
    });
}
</script>
@endsection

