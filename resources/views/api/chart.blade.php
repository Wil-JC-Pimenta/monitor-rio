@extends('api-layout')

@section('title', 'Gráficos Interativos - Monitor Rio Piracicaba')

@section('content')
<div class="px-4 py-6 sm:px-0">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">
            <i class="fas fa-chart-line text-blue-600 mr-3"></i>
            Gráficos Interativos
        </h1>
        <p class="text-gray-600">Visualização avançada dos dados hidrológicos com gráficos interativos</p>
    </div>

    <!-- Controls -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estação</label>
                <select id="station-select" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todas as estações</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
                <select id="period-select" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="24h">Últimas 24h</option>
                    <option value="7d" selected>Últimos 7 dias</option>
                    <option value="30d">Últimos 30 dias</option>
                    <option value="90d">Últimos 90 dias</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Gráfico</label>
                <select id="chart-type-select" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="line">Linha</option>
                    <option value="bar">Barras</option>
                    <option value="area">Área</option>
                    <option value="scatter">Dispersão</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dados</label>
                <select id="data-type-select" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Todos</option>
                    <option value="nivel">Nível</option>
                    <option value="vazao">Vazão</option>
                    <option value="chuva">Chuva</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="updateChart()" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Atualizar
                </button>
            </div>
        </div>
    </div>

    <!-- Main Interactive Chart -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Gráfico Principal</h2>
            <div class="flex space-x-2">
                <button onclick="toggleFullscreen()" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                    <i class="fas fa-expand mr-1"></i>
                    Tela Cheia
                </button>
                <button onclick="downloadChart()" class="px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200">
                    <i class="fas fa-download mr-1"></i>
                    Download
                </button>
            </div>
        </div>
        <div class="relative h-96" id="main-chart-container">
            <canvas id="main-chart"></canvas>
        </div>
    </div>

    <!-- Additional Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Level Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-water text-blue-500 mr-2"></i>
                Nível do Rio
            </h3>
            <div class="relative h-80">
                <canvas id="level-chart"></canvas>
            </div>
        </div>

        <!-- Flow Chart -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-tint text-green-500 mr-2"></i>
                Vazão
            </h3>
            <div class="relative h-80">
                <canvas id="flow-chart"></canvas>
            </div>
        </div>
    </div>

    <!-- Rain Chart -->
    <div class="bg-white shadow rounded-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-cloud-rain text-yellow-500 mr-2"></i>
            Precipitação
        </h3>
        <div class="relative h-80">
            <canvas id="rain-chart"></canvas>
        </div>
    </div>

    <!-- Correlation Matrix -->
    <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-project-diagram text-purple-500 mr-2"></i>
            Matriz de Correlação
        </h3>
        <div class="relative h-80">
            <canvas id="correlation-chart"></canvas>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let mainChart = null;
let levelChart = null;
let flowChart = null;
let rainChart = null;
let correlationChart = null;
let chartData = {};

// Load chart data
async function loadChartData() {
    try {
        const response = await fetch('/api/river-data/chart');
        const data = await response.json();
        
        if (data.success) {
            chartData = data.data;
            loadStationsForSelect();
            createAllCharts();
        }
    } catch (error) {
        console.error('Erro ao carregar dados do gráfico:', error);
    }
}

function loadStationsForSelect() {
    // This would load stations from the API
    const select = document.getElementById('station-select');
    select.innerHTML = '<option value="">Todas as estações</option>';
    
    // Add sample stations
    const stations = [
        { id: 1, name: 'Rio Piracicaba - Ipatinga' },
        { id: 2, name: 'Rio Piracicaba - Timóteo' },
        { id: 3, name: 'Rio Piracicaba - Coronel Fabriciano' }
    ];
    
    stations.forEach(station => {
        const option = document.createElement('option');
        option.value = station.id;
        option.textContent = station.name;
        select.appendChild(option);
    });
}

function createAllCharts() {
    createMainChart();
    createLevelChart();
    createFlowChart();
    createRainChart();
    createCorrelationChart();
}

function createMainChart() {
    const ctx = document.getElementById('main-chart');
    if (!ctx) return;

    // Generate sample data
    const labels = [];
    const nivelData = [];
    const vazaoData = [];
    const chuvaData = [];
    
    const now = new Date();
    const period = document.getElementById('period-select').value;
    let hours = 24;
    
    switch(period) {
        case '24h': hours = 24; break;
        case '7d': hours = 168; break;
        case '30d': hours = 720; break;
        case '90d': hours = 2160; break;
    }
    
    for (let i = hours; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 60 * 60 * 1000));
        labels.push(date.toLocaleString('pt-BR', { 
            day: '2-digit', 
            month: '2-digit', 
            hour: '2-digit', 
            minute: '2-digit' 
        }));
        nivelData.push(2.0 + Math.random() * 1.5);
        vazaoData.push(100 + Math.random() * 100);
        chuvaData.push(Math.random() * 5);
    }

    const chartType = document.getElementById('chart-type-select').value;
    const dataType = document.getElementById('data-type-select').value;

    const datasets = [];
    
    if (dataType === 'all' || dataType === 'nivel') {
        datasets.push({
            label: 'Nível (m)',
            data: nivelData,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            yAxisID: 'y',
            tension: 0.4
        });
    }
    
    if (dataType === 'all' || dataType === 'vazao') {
        datasets.push({
            label: 'Vazão (m³/s)',
            data: vazaoData,
            borderColor: '#10B981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            yAxisID: 'y1',
            tension: 0.4
        });
    }
    
    if (dataType === 'all' || dataType === 'chuva') {
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
        type: chartType,
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
            plugins: {
                zoom: {
                    zoom: {
                        wheel: {
                            enabled: true,
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: 'x',
                    },
                    pan: {
                        enabled: true,
                        mode: 'x',
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Data/Hora'
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

function createLevelChart() {
    const ctx = document.getElementById('level-chart');
    if (!ctx) return;

    // Generate level data
    const labels = [];
    const data = [];
    const now = new Date();
    
    for (let i = 23; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 60 * 60 * 1000));
        labels.push(date.getHours() + 'h');
        data.push(2.0 + Math.random() * 1.5);
    }

    if (levelChart) {
        levelChart.destroy();
    }

    levelChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nível (m)',
                data: data,
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
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

function createFlowChart() {
    const ctx = document.getElementById('flow-chart');
    if (!ctx) return;

    // Generate flow data
    const labels = [];
    const data = [];
    const now = new Date();
    
    for (let i = 23; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 60 * 60 * 1000));
        labels.push(date.getHours() + 'h');
        data.push(100 + Math.random() * 100);
    }

    if (flowChart) {
        flowChart.destroy();
    }

    flowChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Vazão (m³/s)',
                data: data,
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: '#10B981',
                borderWidth: 1
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
                    beginAtZero: true
                }
            }
        }
    });
}

function createRainChart() {
    const ctx = document.getElementById('rain-chart');
    if (!ctx) return;

    // Generate rain data
    const labels = [];
    const data = [];
    const now = new Date();
    
    for (let i = 6; i >= 0; i--) {
        const date = new Date(now.getTime() - (i * 24 * 60 * 60 * 1000));
        labels.push(date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }));
        data.push(Math.random() * 20);
    }

    if (rainChart) {
        rainChart.destroy();
    }

    rainChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Chuva (mm)',
                data: data,
                backgroundColor: 'rgba(245, 158, 11, 0.8)',
                borderColor: '#F59E0B',
                borderWidth: 1
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
                    beginAtZero: true
                }
            }
        }
    });
}

function createCorrelationChart() {
    const ctx = document.getElementById('correlation-chart');
    if (!ctx) return;

    // Generate correlation data
    const labels = ['Nível', 'Vazão', 'Chuva'];
    const data = [
        [1.0, 0.85, 0.3],    // Nível correlations
        [0.85, 1.0, 0.2],    // Vazão correlations
        [0.3, 0.2, 1.0]      // Chuva correlations
    ];

    if (correlationChart) {
        correlationChart.destroy();
    }

    correlationChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nível',
                    data: data[0],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)'
                },
                {
                    label: 'Vazão',
                    data: data[1],
                    backgroundColor: 'rgba(16, 185, 129, 0.8)'
                },
                {
                    label: 'Chuva',
                    data: data[2],
                    backgroundColor: 'rgba(245, 158, 11, 0.8)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1.0
                }
            }
        }
    });
}

function updateChart() {
    createMainChart();
}

function toggleFullscreen() {
    const container = document.getElementById('main-chart-container');
    if (container.requestFullscreen) {
        container.requestFullscreen();
    }
}

function downloadChart() {
    if (mainChart) {
        const url = mainChart.toBase64Image();
        const link = document.createElement('a');
        link.download = 'grafico-rio-piracicaba.png';
        link.href = url;
        link.click();
    }
}

// Load data on page load
document.addEventListener('DOMContentLoaded', loadChartData);

// Auto-refresh every 5 minutes
setInterval(loadChartData, 300000);
</script>
@endsection
