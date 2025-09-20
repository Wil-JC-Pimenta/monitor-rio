@extends('api-layout')

@section('title', 'Dashboard - Monitor Rio Piracicaba')

@section('content')
@php
    $stations = \App\Models\Station::all();
    $totalData = \App\Models\RiverData::count();
    $activeStations = $stations->where('status', 'active')->count();
    $recentData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(10)->get();
    $averageNivel = 2.5; // Mock data
    $averageVazao = 150.0; // Mock data
    $totalChuva = 45.2; // Mock data
@endphp

<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-tachometer-alt text-blue-600 mr-3"></i>
            Dashboard - Monitor Rio Piracicaba
        </h1>
        <p class="text-gray-600">Visão geral do sistema de monitoramento hidrológico em tempo real</p>
    </div>

    <!-- Main Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-map-marker-alt text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium">Estações Ativas</h3>
                    <p class="text-3xl font-bold">{{ $activeStations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-database text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium">Total de Dados</h3>
                    <p class="text-3xl font-bold">{{ number_format($totalData) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-water text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium">Nível Médio</h3>
                    <p class="text-3xl font-bold">{{ $averageNivel ? number_format($averageNivel, 2) . 'm' : 'N/A' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-cloud-rain text-3xl"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium">Chuva Total</h3>
                    <p class="text-3xl font-bold">{{ $totalChuva ? number_format($totalChuva, 1) . 'mm' : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Real-time Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                Dados em Tempo Real
            </h3>
            <div class="relative h-80">
                <canvas id="realtime-chart"></canvas>
            </div>
        </div>

        <!-- Station Status -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>
                Status das Estações
            </h3>
            <div class="space-y-4">
                @foreach($stations as $station)
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-circle {{ $station->status === 'active' ? 'text-green-500' : 'text-red-500' }} mr-3"></i>
                        <div>
                            <p class="font-medium text-gray-900">{{ $station->name }}</p>
                            <p class="text-sm text-gray-500">{{ $station->location }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900">{{ $station->river_data_count }} dados</p>
                        <p class="text-xs text-gray-500">{{ $station->code }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Data Table -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-clock text-indigo-500 mr-2"></i>
                Dados Recentes
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível (m)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vazão (m³/s)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chuva (mm)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentData as $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->data_medicao->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->station->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->nivel ? number_format($data->nivel, 2) . 'm' : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->vazao ? number_format($data->vazao, 1) . 'm³/s' : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->chuva ? number_format($data->chuva, 1) . 'mm' : 'N/A' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/api/stations" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <i class="fas fa-map-marker-alt text-blue-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Ver Estações</h3>
                    <p class="text-sm text-gray-500">Visualizar todas as estações de monitoramento</p>
                </div>
            </div>
        </a>

        <a href="/api/river-data" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-green-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Dados Hidrológicos</h3>
                    <p class="text-sm text-gray-500">Analisar dados de nível, vazão e chuva</p>
                </div>
            </div>
        </a>

        <a href="/api/river-data/stats" class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
            <div class="flex items-center">
                <i class="fas fa-chart-bar text-purple-500 text-2xl mr-4"></i>
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Estatísticas</h3>
                    <p class="text-sm text-gray-500">Ver análises e métricas do sistema</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Initialize charts on page load
document.addEventListener('DOMContentLoaded', function() {
    createRealtimeChart();
});

function createRealtimeChart() {
    const ctx = document.getElementById('realtime-chart');
    if (!ctx) return;

    // Generate sample real-time data
    const labels = [];
    const nivelData = [];
    const vazaoData = [];
    const chuvaData = [];
    const now = new Date();
    
    for (let i = 11; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 2 * 60 * 60 * 1000)); // Every 2 hours
        labels.push(date.getHours() + 'h');
        nivelData.push(2.0 + Math.random() * 1.5);
        vazaoData.push(100 + Math.random() * 100);
        chuvaData.push(Math.random() * 5);
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nível (m)',
                    data: nivelData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4
                },
                {
                    label: 'Vazão (m³/s)',
                    data: vazaoData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4
                },
                {
                    label: 'Chuva (mm)',
                    data: chuvaData,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    yAxisID: 'y2',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Horário'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nível (m)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Vazão (m³/s)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
                y2: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Chuva (mm)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Auto-refresh every 5 minutes
setInterval(function() {
    // This would refresh the data
    console.log('Refreshing dashboard data...');
}, 300000);
</script>
@endsection
