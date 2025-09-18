<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análises - Monitor Rio Piracicaba</title>
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
                        <a href="/" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                        <a href="/stations" class="text-gray-700 hover:text-blue-600">Estações</a>
                        <a href="/data" class="text-gray-700 hover:text-blue-600">Dados</a>
                        <a href="/analytics" class="text-blue-600 font-medium">Análises</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Análises e Estatísticas</h1>
            
            <!-- Métricas Principais -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nível Máximo</h3>
                    <p class="text-3xl font-bold text-red-600">{{ number_format($maxNivel, 2) }}m</p>
                    <p class="text-sm text-gray-500">Últimos 7 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nível Mínimo</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($minNivel, 2) }}m</p>
                    <p class="text-sm text-gray-500">Últimos 7 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Vazão Máxima</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($maxVazao, 1) }} m³/s</p>
                    <p class="text-sm text-gray-500">Últimos 7 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Chuva Acumulada</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totalChuva, 1) }}mm</p>
                    <p class="text-sm text-gray-500">Últimos 7 dias</p>
                </div>
            </div>

            <!-- Resumo Estatístico -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Estatísticas de Níveis -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Estatísticas de Níveis</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Nível Atual Médio:</span>
                            <span class="text-2xl font-bold text-blue-600">{{ number_format($avgNivel, 2) }}m</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Variação (Max-Min):</span>
                            <span class="text-lg font-semibold text-green-600">{{ number_format($maxNivel - $minNivel, 2) }}m</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Tendência:</span>
                            <span class="text-lg font-semibold {{ $avgNivel > 2.0 ? 'text-red-600' : ($avgNivel > 1.5 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $avgNivel > 2.0 ? 'Alto' : ($avgNivel > 1.5 ? 'Médio' : 'Baixo') }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Resumo de Vazões -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Resumo de Vazões</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Vazão Máxima:</span>
                            <span class="text-2xl font-bold text-red-600">{{ number_format($maxVazao, 1) }} m³/s</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Chuva Acumulada:</span>
                            <span class="text-lg font-semibold text-purple-600">{{ number_format($totalChuva, 1) }}mm</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status:</span>
                            <span class="text-lg font-semibold {{ $totalChuva > 50 ? 'text-yellow-600' : 'text-green-600' }}">
                                {{ $totalChuva > 50 ? 'Chuvoso' : 'Normal' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Análise por Estação -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Análise por Estação</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estação</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível Médio</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vazão Média</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chuva Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Medições</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($stations as $station)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $station['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($station['avg_nivel'], 2) }}m
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($station['avg_vazao'], 1) }} m³/s
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($station['total_chuva'], 1) }}mm
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ number_format($station['river_data_count']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $station['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $station['status'] === 'active' ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Alertas e Recomendações -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Alertas e Recomendações</h2>
                <div class="space-y-4">
                    @if($maxNivel > 3.0)
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Alerta de Nível Alto</h3>
                                <p class="text-sm text-red-700">O nível máximo registrado ({{ number_format($maxNivel, 2) }}m) está acima do normal. Monitore constantemente.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($totalChuva > 50)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Chuva Intensa</h3>
                                <p class="text-sm text-yellow-700">Chuva acumulada de {{ number_format($totalChuva, 1) }}mm nos últimos dias. Possível aumento do nível do rio.</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($avgNivel < 1.5)
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Nível Baixo</h3>
                                <p class="text-sm text-blue-700">Nível médio baixo ({{ number_format($avgNivel, 2) }}m). Considere medidas de conservação de água.</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</body>
</html>