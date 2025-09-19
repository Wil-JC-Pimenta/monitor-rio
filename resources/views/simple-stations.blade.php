<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estações - Monitor Rio Piracicaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="/" class="text-gray-700 hover:text-blue-600">Dashboard</a>
                        <a href="/stations" class="text-blue-600 font-medium">Estações</a>
                        <a href="/data" class="text-gray-700 hover:text-blue-600">Dados</a>
                        <a href="/analytics" class="text-gray-700 hover:text-blue-600">Análises</a>
                        <a href="/alerts" class="text-gray-700 hover:text-blue-600">Alertas</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Estações de Monitoramento</h1>
            
            <!-- Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Estações</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Station::count() }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Estações Ativas</h3>
                    <p class="text-3xl font-bold text-green-600">{{ \App\Models\Station::where('status', 'active')->count() }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Dados</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ \App\Models\RiverData::count() }}</p>
                </div>
            </div>

            <!-- Lista de Estações -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(\App\Models\Station::withCount('riverData')->get() as $station)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full {{ $station->status === 'active' ? 'bg-green-400' : 'bg-red-400' }} mr-3"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $station->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $station->code }} • {{ $station->location }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $station->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $station->status === 'active' ? 'Ativa' : 'Inativa' }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Medições:</span>
                            <span class="font-medium">{{ $station->river_data_count }}</span>
                        </div>
                        
                        @php
                            $recentData = \App\Models\RiverData::where('station_id', $station->id)->orderBy('data_medicao', 'desc')->first();
                            $avgNivel = \App\Models\RiverData::where('station_id', $station->id)->whereNotNull('nivel')->avg('nivel') ?: 0;
                            $avgVazao = \App\Models\RiverData::where('station_id', $station->id)->whereNotNull('vazao')->avg('vazao') ?: 0;
                        @endphp
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nível Médio:</span>
                            <span class="font-medium">{{ number_format($avgNivel, 2) }}m</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vazão Média:</span>
                            <span class="font-medium">{{ number_format($avgVazao, 1) }} m³/s</span>
                        </div>
                        
                        @if($recentData)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Medição:</span>
                            <span class="font-medium">{{ $recentData->data_medicao->format('d/m H:i') }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Mini Gráfico -->
                    <div class="mt-4">
                        <canvas id="chart-{{ $station->id }}" width="300" height="100"></canvas>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        // Gerar mini gráficos para cada estação
        @foreach(\App\Models\Station::all() as $station)
        (function() {
            const ctx = document.getElementById('chart-{{ $station->id }}');
            if (!ctx) return;

            // Dados simulados para o gráfico
            const labels = [];
            const data = [];
            
            for (let i = 23; i >= 0; i--) {
                const hour = new Date(Date.now() - (i * 60 * 60 * 1000)).getHours();
                labels.push(hour + 'h');
                data.push(2.0 + Math.random() * 1.5);
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
                        tension: 0.4,
                        pointRadius: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { display: false },
                        x: { display: false }
                    }
                }
            });
        })();
        @endforeach
    </script>
</body>
</html>

