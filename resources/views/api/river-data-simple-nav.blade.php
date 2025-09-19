<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados Hidrológicos - Monitor Rio Piracicaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <i class="fas fa-water text-blue-600 text-2xl"></i>
                        <span class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-home mr-2"></i>Início
                    </a>
                    <a href="/api/stations" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-satellite-dish mr-2"></i>Estações
                    </a>
                    <a href="/api/river-data" class="text-blue-600 px-3 py-2 rounded-md text-sm font-medium bg-blue-50">
                        <i class="fas fa-chart-line mr-2"></i>Dados
                    </a>
                    <a href="/api/dashboard" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-cloud-sun mr-2"></i>Dashboard
                    </a>
                    <a href="/api/river-data/stats" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium">
                        <i class="fas fa-chart-bar mr-2"></i>Estatísticas
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @php
            // Load real data from database
            $totalData = \App\Models\RiverData::count();
            $stations = \App\Models\Station::all();
            $riverData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(50)->get();
            $averageNivel = \App\Models\RiverData::whereNotNull('nivel')->avg('nivel') ?: 0;
            $averageVazao = \App\Models\RiverData::whereNotNull('vazao')->avg('vazao') ?: 0;
            $totalChuva = \App\Models\RiverData::whereNotNull('chuva')->sum('chuva') ?: 0;
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
                        <tbody class="bg-white divide-y divide-gray-200">
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
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Monitor Rio Piracicaba</h3>
                    <p class="text-gray-300">Sistema de monitoramento hidrológico em tempo real do Rio Piracicaba no Vale do Aço.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Dados</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-check mr-2"></i>Nível do rio</li>
                        <li><i class="fas fa-check mr-2"></i>Vazão</li>
                        <li><i class="fas fa-check mr-2"></i>Precipitação</li>
                        <li><i class="fas fa-check mr-2"></i>Alertas meteorológicos</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Fonte</h3>
                    <p class="text-gray-300">Dados fornecidos pela ANA (Agência Nacional de Águas e Saneamento Básico)</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Monitor Rio Piracicaba. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Initialize chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('mainChart').getContext('2d');
            
            // Sample data for demonstration
            const labels = [];
            const nivelData = [];
            const vazaoData = [];
            const chuvaData = [];
            
            for (let i = 23; i >= 0; i--) {
                const date = new Date(Date.now() - (i * 60 * 60 * 1000));
                labels.push(date.getHours() + 'h');
                nivelData.push(2.0 + Math.random() * 1.5);
                vazaoData.push(100 + Math.random() * 200);
                chuvaData.push(Math.random() * 10);
            }
            
            new Chart(ctx, {
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
                    }
                }
            });
        });

        function applyFilters() {
            alert('Filtros aplicados! (Funcionalidade em desenvolvimento)');
        }

        function clearFilters() {
            document.getElementById('stationFilter').value = '';
            document.getElementById('periodFilter').value = '24';
        }
    </script>
</body>
</html>

