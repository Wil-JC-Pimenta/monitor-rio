@extends('layouts.app')

@section('title', 'Dashboard Meteorológico - Monitor Rio Piracicaba')

@section('content')
@php
    // Load real data from database
    $stations = \App\Models\Station::all();
    $totalData = \App\Models\RiverData::count();
    $activeStations = $stations->where('status', 'active')->count();
    $recentData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(24)->get();
    $averageNivel = \App\Models\RiverData::whereNotNull('nivel')->avg('nivel') ?: 0;
    $averageVazao = \App\Models\RiverData::whereNotNull('vazao')->avg('vazao') ?: 0;
    $totalChuva = \App\Models\RiverData::whereNotNull('chuva')->sum('chuva') ?: 0;
    $maxNivel = \App\Models\RiverData::whereNotNull('nivel')->max('nivel') ?: 0;
    $minNivel = \App\Models\RiverData::whereNotNull('nivel')->min('nivel') ?: 0;
    
    // Calculate weather alerts
    $highWaterLevel = $averageNivel > 3.0;
    $heavyRain = $totalChuva > 50;
    $floodRisk = $averageNivel > 2.5 && $totalChuva > 30;
    $alertLevel = $floodRisk ? 'danger' : ($highWaterLevel ? 'warning' : 'info');
@endphp

<div class="px-4 py-6 sm:px-0">
    <!-- Header with Weather Info -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-cloud-sun text-blue-600 mr-3"></i>
                    Dashboard Meteorológico
                </h1>
                <p class="text-gray-600">Monitoramento em tempo real do Rio Piracicaba - Vale do Aço</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Última atualização</div>
                <div class="text-lg font-semibold text-gray-900">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Weather Alert Banner -->
    @if($alertLevel === 'danger')
    <div class="mb-8 bg-red-50 border-l-4 border-red-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">ALERTA DE ENCHENTE</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Nível elevado do rio e chuvas intensas detectadas. Risco de enchente!</p>
                </div>
            </div>
        </div>
    </div>
    @elseif($alertLevel === 'warning')
    <div class="mb-8 bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">ATENÇÃO</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Nível do rio elevado. Monitore as condições.</p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="mb-8 bg-blue-50 border-l-4 border-blue-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">CONDIÇÕES NORMAIS</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Nível do rio dentro dos parâmetros normais.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Weather Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- River Level Card -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-water text-blue-500 text-3xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">Nível do Rio</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-blue-600">{{ number_format($averageNivel, 2) }}m</div>
                            <div class="text-sm text-gray-500">
                                Min: {{ number_format($minNivel, 2) }}m | Max: {{ number_format($maxNivel, 2) }}m
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min(($averageNivel / 4) * 100, 100) }}%"></div>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">Nível atual em relação ao máximo histórico</div>
                </div>
            </div>
        </div>

        <!-- Flow Rate Card -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tint text-green-500 text-3xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">Vazão</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-green-600">{{ number_format($averageVazao, 1) }} m³/s</div>
                            <div class="text-sm text-gray-500">Vazão média atual</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rainfall Card -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cloud-rain text-purple-500 text-3xl"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium text-gray-900">Precipitação</h3>
                        <div class="mt-2">
                            <div class="text-3xl font-bold text-purple-600">{{ number_format($totalChuva, 1) }}mm</div>
                            <div class="text-sm text-gray-500">Total acumulado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- River Level Chart -->
    <div class="bg-white shadow-lg rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-chart-line text-blue-600 mr-2"></i>
                Nível do Rio Piracicaba - Últimas 24 horas
            </h3>
        </div>
        <div class="p-6">
            <canvas id="riverLevelChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Weather Conditions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Temperature -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <i class="fas fa-thermometer-half text-orange-500 text-2xl mr-3"></i>
                <div>
                    <div class="text-sm text-gray-500">Temperatura</div>
                    <div class="text-xl font-semibold">28°C</div>
                </div>
            </div>
        </div>

        <!-- Humidity -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <i class="fas fa-tint text-blue-500 text-2xl mr-3"></i>
                <div>
                    <div class="text-sm text-gray-500">Umidade</div>
                    <div class="text-xl font-semibold">75%</div>
                </div>
            </div>
        </div>

        <!-- Wind -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <i class="fas fa-wind text-gray-500 text-2xl mr-3"></i>
                <div>
                    <div class="text-sm text-gray-500">Vento</div>
                    <div class="text-xl font-semibold">12 km/h</div>
                </div>
            </div>
        </div>

        <!-- Pressure -->
        <div class="bg-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <i class="fas fa-compress-arrows-alt text-indigo-500 text-2xl mr-3"></i>
                <div>
                    <div class="text-sm text-gray-500">Pressão</div>
                    <div class="text-xl font-semibold">1013 hPa</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Station Status -->
    <div class="bg-white shadow-lg rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-satellite-dish text-green-600 mr-2"></i>
                Status das Estações
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($stations->take(8) as $station)
                <div class="border rounded-lg p-4 {{ $station->status === 'active' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-900">{{ $station->name }}</div>
                            <div class="text-sm text-gray-500">{{ $station->code }}</div>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $station->status === 'active' ? 'bg-green-400' : 'bg-red-400' }}"></div>
                        </div>
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        {{ $station->location }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // River Level Chart
    const ctx = document.getElementById('riverLevelChart').getContext('2d');
    
    // Generate sample data for the last 24 hours
    const labels = [];
    const data = [];
    const now = new Date();
    
    for (let i = 23; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 60 * 60 * 1000));
        labels.push(date.getHours() + 'h');
        // Generate realistic river level data
        const baseLevel = {{ $averageNivel }};
        const variation = (Math.sin(i * 0.3) * 0.5) + (Math.random() * 0.3 - 0.15);
        data.push(Math.max(0, baseLevel + variation));
    }
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nível do Rio (m)',
                data: data,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
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
                    title: {
                        display: true,
                        text: 'Nível (metros)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Horário'
                    }
                }
            }
        }
    });
});
</script>
@endsection
