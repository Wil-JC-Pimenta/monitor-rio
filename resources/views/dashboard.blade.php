<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitor Rio Piracicaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/" class="text-blue-600 font-medium">Dashboard</a>
                        <a href="/stations" class="text-gray-700 hover:text-blue-600">Estações</a>
                        <a href="/data" class="text-gray-700 hover:text-blue-600">Dados</a>
                        <a href="/analytics" class="text-gray-700 hover:text-blue-600">Análises</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard - Monitoramento Hidrológico</h1>
            
            <!-- Métricas Principais -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Estações</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ $totalStations }}</p>
                    <p class="text-sm text-gray-500">Ativas: {{ $activeStations }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Medições</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($totalMeasurements) }}</p>
                    <p class="text-sm text-gray-500">Últimos 30 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nível Máximo</h3>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($maxNivel, 2) }}m</p>
                    <p class="text-sm text-gray-500">Registrado</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Vazão Máxima</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($maxVazao, 1) }} m³/s</p>
                    <p class="text-sm text-gray-500">Registrada</p>
                </div>
            </div>

        <!-- Estatísticas do Rio Piracicaba -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Rio Piracicaba - Estatísticas</h2>
                <div class="space-y-4">
                    @if($piracicabaData->count() > 0)
                        @php
                            $avgNivel = $piracicabaData->avg('nivel') ?: 0;
                            $maxNivel = $piracicabaData->max('nivel') ?: 0;
                            $minNivel = $piracicabaData->min('nivel') ?: 0;
                            $lastRecord = $piracicabaData->first();
                            $avgVazao = $piracicabaData->avg('vazao') ?: 0;
                            $maxVazao = $piracicabaData->max('vazao') ?: 0;
                        @endphp
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Nível Atual:</span>
                                <span class="text-2xl font-bold text-blue-600">{{ number_format($lastRecord->nivel, 2) }}m</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Nível Médio:</span>
                                <span class="text-lg font-semibold text-green-600">{{ number_format($avgNivel, 2) }}m</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Variação:</span>
                                <span class="text-lg font-semibold text-purple-600">{{ number_format($maxNivel - $minNivel, 2) }}m</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Vazão Média:</span>
                                <span class="text-lg font-semibold text-indigo-600">{{ number_format($avgVazao, 1) }} m³/s</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Status:</span>
                                <span class="text-lg font-semibold {{ $lastRecord->nivel > 2.5 ? 'text-red-600' : ($lastRecord->nivel > 1.5 ? 'text-yellow-600' : 'text-green-600') }}">
                                    {{ $lastRecord->nivel > 2.5 ? 'Alto' : ($lastRecord->nivel > 1.5 ? 'Médio' : 'Normal') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">Dados do Rio Piracicaba não disponíveis</p>
                    @endif
                </div>
            </div>
            
            <!-- Gráfico Linear do Rio Piracicaba -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Gráfico de Nível - Últimas 24h</h2>
                @if($chartData->count() > 0)
                    <div class="relative h-64">
                        <canvas id="piracicabaChart"></canvas>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-8">Dados insuficientes para o gráfico</p>
                @endif
            </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Resumo das Estações</h2>
                    <div class="space-y-4">
                        @foreach($stations->take(5) as $station)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $station->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $station->code }} • {{ $station->location }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ number_format($station->river_data_count) }} medições</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $station->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $station->status === 'active' ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Dados Recentes -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Dados Recentes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível (m)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vazão (m³/s)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chuva (mm)</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentData as $record)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $record->station->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($record->data_medicao)->format('d/m H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $record->nivel ? number_format($record->nivel, 2) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $record->vazao ? number_format($record->vazao, 1) : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $record->chuva ? number_format($record->chuva, 1) : '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Nenhum dado encontrado
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Gráfico do Rio Piracicaba
        @if($chartData->count() > 0)
        const ctx = document.getElementById('piracicabaChart').getContext('2d');
        const chartData = @json($chartData);
        
        // Preparar dados para o gráfico
        const labels = chartData.map(item => {
            const date = new Date(item.data_medicao);
            return date.toLocaleTimeString('pt-BR', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
        });
        
        const nivelData = chartData.map(item => item.nivel);
        const vazaoData = chartData.map(item => item.vazao);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Nível (m)',
                        data: nivelData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Vazão (m³/s)',
                        data: vazaoData,
                        borderColor: 'rgb(16, 185, 129)',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        yAxisID: 'y1'
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
                        },
                        grid: {
                            color: 'rgba(59, 130, 246, 0.1)'
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
                            color: 'rgba(16, 185, 129, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toFixed(2);
                                    if (context.dataset.label.includes('Nível')) {
                                        label += 'm';
                                    } else if (context.dataset.label.includes('Vazão')) {
                                        label += ' m³/s';
                                    }
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        @endif
    </script>

</body>
</html>