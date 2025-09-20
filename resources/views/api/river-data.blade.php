@extends('api-layout')

@section('title', 'Dados do Rio - Monitor Rio Piracicaba')

@section('content')
@php
    // Load real data from database
    $stations = \App\Models\Station::all();
    $totalData = \App\Models\RiverData::count();
    $averageNivel = \App\Models\RiverData::whereNotNull('nivel')->avg('nivel') ?: 0;
    $averageVazao = \App\Models\RiverData::whereNotNull('vazao')->avg('vazao') ?: 0;
    $totalChuva = \App\Models\RiverData::whereNotNull('chuva')->sum('chuva') ?: 0;
    
    // Load river data with station info
    $riverData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(100)->get();
    $riverData = $riverData->map(function($item) use ($stations) {
        $station = $stations->find($item->station_id);
        $item->station = $station ? (object)['name' => $station->name, 'code' => $station->code] : (object)['name' => 'N/A', 'code' => 'N/A'];
        return $item;
    });
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

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estação</label>
                <select id="station-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as estações</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select id="period-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="24h">Últimas 24 horas</option>
                    <option value="7d" selected>Últimos 7 dias</option>
                    <option value="30d">Últimos 30 dias</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Dados</label>
                <select id="data-type-filter" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Todos</option>
                    <option value="nivel">Nível</option>
                    <option value="vazao">Vazão</option>
                    <option value="chuva">Chuva</option>
                </select>
            </div>
            <div class="flex items-end space-x-2">
                <button onclick="applyFilters()" id="filter-btn" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-filter mr-2"></i>
                    <span id="filter-text">Aplicar Filtros</span>
                </button>
                <button onclick="clearFilters()" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Limpar
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg" id="summary-total">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total de Registros</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-records">{{ number_format($totalData) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg" id="summary-nivel">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-water text-blue-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Nível Médio</dt>
                            <dd class="text-lg font-medium text-gray-900" id="average-nivel">{{ $averageNivel ? number_format($averageNivel, 2) . 'm' : 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg" id="summary-vazao">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tint text-green-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Vazão Média</dt>
                            <dd class="text-lg font-medium text-gray-900" id="average-vazao">{{ $averageVazao ? number_format($averageVazao, 1) . 'm³/s' : 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow rounded-lg" id="summary-chuva">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-cloud-rain text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Chuva Total</dt>
                            <dd class="text-lg font-medium text-gray-900" id="total-chuva">{{ $totalChuva ? number_format($totalChuva, 1) . 'mm' : 'N/A' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Chart -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Gráfico de Dados Hidrológicos</h2>
            <div class="flex space-x-2">
                <button onclick="toggleDataSeries('nivel')" class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm hover:bg-blue-200">
                    <i class="fas fa-water mr-1"></i> Nível
                </button>
                <button onclick="toggleDataSeries('vazao')" class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm hover:bg-green-200">
                    <i class="fas fa-tint mr-1"></i> Vazão
                </button>
                <button onclick="toggleDataSeries('chuva')" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm hover:bg-yellow-200">
                    <i class="fas fa-cloud-rain mr-1"></i> Chuva
                </button>
            </div>
        </div>
        <div class="relative h-96">
            <canvas id="main-chart"></canvas>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Tabela de Dados</h3>
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
                    @foreach($riverData as $data)
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
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Mostrando <span id="showing-count">0</span> de <span id="total-count">0</span> registros
                </div>
                <div class="flex space-x-2">
                    <button onclick="previousPage()" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50" id="prev-btn">
                        <i class="fas fa-chevron-left mr-1"></i> Anterior
                    </button>
                    <button onclick="nextPage()" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50" id="next-btn">
                        Próximo <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let riverData = @json($riverData);
let stations = @json($stations);
let mainChart = null;
let visibleSeries = {
    nivel: true,
    vazao: true,
    chuva: true
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    createMainChart();
    loadStationsForFilter();
});

function createMainChart() {
    const ctx = document.getElementById('main-chart');
    if (!ctx) return;

    // Prepare data for chart - use last 20 records
    const chartData = riverData.slice(0, 20).reverse();
    const labels = [];
    const nivelData = [];
    const vazaoData = [];
    const chuvaData = [];

    chartData.forEach(item => {
        const date = new Date(item.data_medicao);
        labels.push(date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }));
        nivelData.push(item.nivel || 0);
        vazaoData.push(item.vazao || 0);
        chuvaData.push(item.chuva || 0);
    });

    const datasets = [];
    
    if (visibleSeries.nivel) {
        datasets.push({
            label: 'Nível (m)',
            data: nivelData,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            yAxisID: 'y',
            tension: 0.4
        });
    }
    
    if (visibleSeries.vazao) {
        datasets.push({
            label: 'Vazão (m³/s)',
            data: vazaoData,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            yAxisID: 'y1',
            tension: 0.4
        });
    }
    
    if (visibleSeries.chuva) {
        datasets.push({
            label: 'Chuva (mm)',
            data: chuvaData,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            yAxisID: 'y2',
            tension: 0.4
        });
    }

    if (mainChart) {
        mainChart.destroy();
    }

    mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: datasets
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
                    display: visibleSeries.nivel,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Nível (m)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: visibleSeries.vazao,
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
                    display: visibleSeries.chuva,
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

function toggleDataSeries(series) {
    visibleSeries[series] = !visibleSeries[series];
    createMainChart();
    
    // Update button appearance
    const button = event.target.closest('button');
    if (visibleSeries[series]) {
        button.classList.remove('opacity-50');
    } else {
        button.classList.add('opacity-50');
    }
}

function loadStationsForFilter() {
    const select = document.getElementById('station-filter');
    select.innerHTML = '<option value="">Todas as estações</option>';
    
    stations.forEach(station => {
        const option = document.createElement('option');
        option.value = station.id;
        option.textContent = station.name;
        select.appendChild(option);
    });
}

function applyFilters() {
    const filterBtn = document.getElementById('filter-btn');
    const filterText = document.getElementById('filter-text');
    
    // Show loading state
    filterBtn.disabled = true;
    filterText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Aplicando...';
    
    const stationId = document.getElementById('station-filter').value;
    const period = document.getElementById('period-filter').value;
    const dataType = document.getElementById('data-type-filter').value;
    
    console.log('Aplicando filtros:', { stationId, period, dataType });
    
    // Simulate processing time for better UX
    setTimeout(() => {
        // Filter the data based on selected criteria
        let filteredData = [...riverData];
    
    // Filter by station
    if (stationId) {
        filteredData = filteredData.filter(item => item.station_id == stationId);
    }
    
    // Filter by period
    if (period) {
        const now = new Date();
        let startDate;
        
        switch(period) {
            case '24h':
                startDate = new Date(now.getTime() - 24 * 60 * 60 * 1000);
                break;
            case '7d':
                startDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                break;
            case '30d':
                startDate = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                break;
        }
        
        if (startDate) {
            filteredData = filteredData.filter(item => {
                const itemDate = new Date(item.data_medicao);
                return itemDate >= startDate;
            });
        }
    }
    
    // Filter by data type
    if (dataType !== 'all') {
        filteredData = filteredData.filter(item => {
            switch(dataType) {
                case 'nivel':
                    return item.nivel !== null && item.nivel !== undefined;
                case 'vazao':
                    return item.vazao !== null && item.vazao !== undefined;
                case 'chuva':
                    return item.chuva !== null && item.chuva !== undefined;
                default:
                    return true;
            }
        });
    }
    
        // Update the display
        updateFilteredData(filteredData);
        
        // Reset button state
        filterBtn.disabled = false;
        filterText.innerHTML = '<i class="fas fa-filter mr-2"></i>Aplicar Filtros';
    }, 500); // 500ms delay for better UX
}

function clearFilters() {
    // Reset all filter values
    document.getElementById('station-filter').value = '';
    document.getElementById('period-filter').value = '7d';
    document.getElementById('data-type-filter').value = 'all';
    
    // Apply filters with default values (show all data)
    applyFilters();
}

function updateFilteredData(filteredData) {
    // Update summary cards
    updateSummaryCardsFromData(filteredData);
    
    // Update data table
    updateDataTable(filteredData);
    
    // Update main chart
    updateMainChart(filteredData);
}

function updateSummaryCardsFromData(data) {
    const totalRecords = data.length;
    const validNivel = data.filter(item => item.nivel !== null && item.nivel !== undefined);
    const validVazao = data.filter(item => item.vazao !== null && item.vazao !== undefined);
    const validChuva = data.filter(item => item.chuva !== null && item.chuva !== undefined);
    
    const averageNivel = validNivel.length > 0 ? 
        validNivel.reduce((sum, item) => sum + item.nivel, 0) / validNivel.length : 0;
    const averageVazao = validVazao.length > 0 ? 
        validVazao.reduce((sum, item) => sum + item.vazao, 0) / validVazao.length : 0;
    const totalChuva = validChuva.reduce((sum, item) => sum + (item.chuva || 0), 0);
    
    // Update the summary cards using specific IDs
    const totalElement = document.getElementById('total-records');
    const nivelElement = document.getElementById('average-nivel');
    const vazaoElement = document.getElementById('average-vazao');
    const chuvaElement = document.getElementById('total-chuva');
    
    if (totalElement) totalElement.textContent = totalRecords.toLocaleString();
    if (nivelElement) nivelElement.textContent = averageNivel > 0 ? averageNivel.toFixed(2) + 'm' : 'N/A';
    if (vazaoElement) vazaoElement.textContent = averageVazao > 0 ? averageVazao.toFixed(1) + 'm³/s' : 'N/A';
    if (chuvaElement) chuvaElement.textContent = totalChuva > 0 ? totalChuva.toFixed(1) + 'mm' : 'N/A';
}

function updateDataTable(data) {
    const tbody = document.querySelector('tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    data.slice(0, 20).forEach(item => {
        const row = document.createElement('tr');
        const date = new Date(item.data_medicao);
        
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${date.toLocaleString('pt-BR')}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${item.station?.name || 'N/A'}
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
    
    // Update pagination info
    const showingCount = document.getElementById('showing-count');
    const totalCount = document.getElementById('total-count');
    if (showingCount) showingCount.textContent = Math.min(20, data.length);
    if (totalCount) totalCount.textContent = data.length;
}

function updateMainChart(data) {
    if (!mainChart) return;
    
    // Prepare data for chart - use last 20 records
    const chartData = data.slice(0, 20).reverse();
    const labels = [];
    const nivelData = [];
    const vazaoData = [];
    const chuvaData = [];

    chartData.forEach(item => {
        const date = new Date(item.data_medicao);
        labels.push(date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }));
        nivelData.push(item.nivel || 0);
        vazaoData.push(item.vazao || 0);
        chuvaData.push(item.chuva || 0);
    });

    const datasets = [];
    
    if (visibleSeries.nivel) {
        datasets.push({
            label: 'Nível (m)',
            data: nivelData,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            yAxisID: 'y',
            tension: 0.4
        });
    }
    
    if (visibleSeries.vazao) {
        datasets.push({
            label: 'Vazão (m³/s)',
            data: vazaoData,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            yAxisID: 'y1',
            tension: 0.4
        });
    }
    
    if (visibleSeries.chuva) {
        datasets.push({
            label: 'Chuva (mm)',
            data: chuvaData,
            borderColor: '#F59E0B',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            yAxisID: 'y2',
            tension: 0.4
        });
    }

    mainChart.data.labels = labels;
    mainChart.data.datasets = datasets;
    mainChart.update();
}
</script>
@endsection

