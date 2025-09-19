@extends('layouts.app')

@section('title', 'Dados Hidrológicos - Monitor Rio Piracicaba')

@section('content')
@php
    // Load real data from database
    $totalData = \App\Models\RiverData::count();
    $stations = \App\Models\Station::all();
    $riverData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(50)->get();
    $averageNivel = \App\Models\RiverData::whereNotNull('nivel')->avg('nivel') ?: 0;
    $averageVazao = \App\Models\RiverData::whereNotNull('vazao')->avg('vazao') ?: 0;
    $totalChuva = \App\Models\RiverData::whereNotNull('chuva')->sum('chuva') ?: 0;
    $maxNivel = \App\Models\RiverData::whereNotNull('nivel')->max('nivel') ?: 0;
    $minNivel = \App\Models\RiverData::whereNotNull('nivel')->min('nivel') ?: 0;
@endphp

<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-chart-line text-blue-600 mr-3"></i>
            Dados Hidrológicos
        </h1>
        <p class="text-gray-600">Visualização completa dos dados de nível, vazão e chuva do Rio Piracicaba</p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Registros</dt>
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
                        <i class="fas fa-tint text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Nível Médio</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($averageNivel, 2) }}m</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-water text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vazão Média</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($averageVazao, 1) }} m³/s</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cloud-rain text-purple-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Chuva Total</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($totalChuva, 1) }}mm</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                Filtros
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Estação</label>
                    <select id="stationFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Todas as estações</option>
                        @foreach($stations as $station)
                        <option value="{{ $station->id }}">{{ $station->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                    <select id="periodFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="24">Últimas 24 horas</option>
                        <option value="72">Últimos 3 dias</option>
                        <option value="168">Última semana</option>
                        <option value="720">Último mês</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Dados</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" class="dataTypeFilter" value="nivel" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Nível do Rio</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="dataTypeFilter" value="vazao" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Vazão</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="dataTypeFilter" value="chuva" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Chuva</span>
                        </label>
                    </div>
                </div>
                
                <button onclick="applyFilters()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>Aplicar Filtros
                </button>
                
                <button onclick="clearFilters()" class="w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-times mr-2"></i>Limpar
                </button>
            </div>
        </div>

        <!-- Chart -->
        <div class="lg:col-span-2 bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-chart-area text-blue-600 mr-2"></i>
                Gráfico Interativo
            </h3>
            <div class="h-80">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                <i class="fas fa-table text-blue-600 mr-2"></i>
                Dados Recentes (Últimos 50 registros)
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
                <tbody id="dataTableBody" class="bg-white divide-y divide-gray-200">
                    @foreach($riverData as $data)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $data->data_medicao->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $stations->find($data->station_id)->name ?? 'Estação ' . $data->station_id }}
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
</div>
@endsection

@section('scripts')
<script>
// Global variables
let mainChart;
const riverData = @json($riverData);
const stations = @json($stations);

// Initialize chart
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
});

function initializeChart() {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    // Prepare data for chart
    const labels = riverData.map(item => {
        const date = new Date(item.data_medicao);
        return date.getHours() + 'h';
    }).reverse();
    
    const nivelData = riverData.map(item => item.nivel || 0).reverse();
    const vazaoData = riverData.map(item => item.vazao || 0).reverse();
    const chuvaData = riverData.map(item => item.chuva || 0).reverse();
    
    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nível do Rio (m)',
                    data: nivelData,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'Vazão (m³/s)',
                    data: vazaoData,
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1'
                },
                {
                    label: 'Chuva (mm)',
                    data: chuvaData,
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y2'
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
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nível (metros)'
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
                    display: false,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Chuva (mm)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
}

function applyFilters() {
    const stationId = document.getElementById('stationFilter').value;
    const period = document.getElementById('periodFilter').value;
    const dataTypes = Array.from(document.querySelectorAll('.dataTypeFilter:checked')).map(cb => cb.value);
    
    // Filter data based on selections
    let filteredData = riverData;
    
    if (stationId) {
        filteredData = filteredData.filter(item => item.station_id == stationId);
    }
    
    // Filter by period (simplified - in real app would filter by date)
    const hours = parseInt(period);
    const cutoffTime = new Date(Date.now() - (hours * 60 * 60 * 1000));
    filteredData = filteredData.filter(item => new Date(item.data_medicao) >= cutoffTime);
    
    // Update chart
    updateChart(filteredData, dataTypes);
    
    // Update table
    updateTable(filteredData);
}

function updateChart(data, dataTypes) {
    const labels = data.map(item => {
        const date = new Date(item.data_medicao);
        return date.getHours() + 'h';
    }).reverse();
    
    const datasets = [];
    
    if (dataTypes.includes('nivel')) {
        datasets.push({
            label: 'Nível do Rio (m)',
            data: data.map(item => item.nivel || 0).reverse(),
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4
        });
    }
    
    if (dataTypes.includes('vazao')) {
        datasets.push({
            label: 'Vazão (m³/s)',
            data: data.map(item => item.vazao || 0).reverse(),
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            yAxisID: 'y1'
        });
    }
    
    if (dataTypes.includes('chuva')) {
        datasets.push({
            label: 'Chuva (mm)',
            data: data.map(item => item.chuva || 0).reverse(),
            borderColor: '#8B5CF6',
            backgroundColor: 'rgba(139, 92, 246, 0.1)',
            borderWidth: 2,
            fill: false,
            tension: 0.4,
            yAxisID: 'y2'
        });
    }
    
    mainChart.data.labels = labels;
    mainChart.data.datasets = datasets;
    mainChart.update();
}

function updateTable(data) {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    
    data.forEach(item => {
        const station = stations.find(s => s.id == item.station_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${new Date(item.data_medicao).toLocaleString('pt-BR')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${station ? station.name : 'Estação ' + item.station_id}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${item.nivel ? item.nivel.toFixed(2) + 'm' : 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${item.vazao ? item.vazao.toFixed(1) + 'm³/s' : 'N/A'}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${item.chuva ? item.chuva.toFixed(1) + 'mm' : 'N/A'}
            </td>
        `;
        tbody.appendChild(row);
    });
}

function clearFilters() {
    document.getElementById('stationFilter').value = '';
    document.getElementById('periodFilter').value = '24';
    document.querySelectorAll('.dataTypeFilter').forEach(cb => cb.checked = true);
    
    // Reset to original data
    updateChart(riverData, ['nivel', 'vazao', 'chuva']);
    updateTable(riverData);
}
</script>
@endsection

