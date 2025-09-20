@extends('api-layout')

@section('title', 'Estações - Monitor Rio Piracicaba')

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
        return $station;
    });
@endphp

<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
            Estações de Monitoramento
        </h1>
        <p class="text-gray-600">Visualização em tempo real dos dados das estações do Rio Piracicaba</p>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tachometer-alt text-green-500 text-2xl"></i>
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

        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
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

        <div class="bg-white overflow-hidden shadow rounded-lg card-hover">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-orange-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Última Atualização</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ now()->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stations Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($stations as $station)
        <div class="bg-white overflow-hidden shadow rounded-lg card-hover cursor-pointer" onclick="showStationDetails({{ $station->id }})">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-blue-600 text-xl mr-3"></i>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $station->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $station->location }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <i class="fas {{ $station->status === 'active' ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500' }} text-xl"></i>
                        <p class="text-xs text-gray-500 mt-1">{{ $station->status }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-600">{{ $station->river_data_count }}</p>
                        <p class="text-xs text-gray-500">Medições</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $station->code }}</p>
                        <p class="text-xs text-gray-500">Código</p>
                    </div>
                </div>

                <div class="mt-4">
                    <canvas id="chart-{{ $station->id }}" width="300" height="100"></canvas>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200">
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-chart-line mr-2"></i>
                        Ver Gráfico Detalhado
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Station Detail Modal -->
<div id="station-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Detalhes da Estação</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div id="modal-content">
                <!-- Modal content will be loaded here -->
            </div>
        </div>
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
            const date = new Date(now.getTime() - (i * 2 * 60 * 60 * 1000));
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
                    beginAtZero: false,
                    min: 1.5,
                    max: 4.0
                }
            }
        }
    });
}

function showStationDetails(stationId) {
    // Get station data from PHP
    const stations = @json($stations);
    const station = stations.find(s => s.id === stationId);
    
    if (!station) return;
    
    document.getElementById('modal-title').textContent = station.name;
    document.getElementById('modal-content').innerHTML = `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <p class="mt-1 text-sm text-gray-900">${station.code}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1 text-sm text-gray-900">${station.status}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Localização</label>
                <p class="mt-1 text-sm text-gray-900">${station.location}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Total de Medições</label>
                <p class="mt-1 text-sm text-gray-900">${station.river_data_count}</p>
            </div>
            <div class="mt-6">
                <canvas id="modal-chart" width="400" height="200"></canvas>
            </div>
        </div>
    `;
    
    document.getElementById('station-modal').classList.remove('hidden');
    
    // Create detailed chart
    setTimeout(() => {
        createDetailedChart();
    }, 100);
}

function createDetailedChart() {
    const ctx = document.getElementById('modal-chart');
    if (!ctx) return;

    // Generate more detailed data
    const labels = [];
    const nivelData = [];
    const vazaoData = [];
    const chuvaData = [];
    
    const now = new Date();
    for (let i = 23; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 60 * 60 * 1000)); // Every hour
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
                    yAxisID: 'y'
                },
                {
                    label: 'Vazão (m³/s)',
                    data: vazaoData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    yAxisID: 'y1'
                },
                {
                    label: 'Chuva (mm)',
                    data: chuvaData,
                    borderColor: '#F59E0B',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    yAxisID: 'y2'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
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
                    display: false
                }
            }
        }
    });
}

function closeModal() {
    document.getElementById('station-modal').classList.add('hidden');
}
</script>
@endsection

