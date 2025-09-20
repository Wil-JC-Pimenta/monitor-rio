<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estações - Monitor Rio Piracicaba</title>
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
                        <a href="/stations" class="text-blue-600 font-medium">Estações</a>
                        <a href="/data" class="text-gray-700 hover:text-blue-600">Dados</a>
                        <a href="/analytics" class="text-gray-700 hover:text-blue-600">Análises</a>
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
                    <p class="text-3xl font-bold text-blue-600">{{ $totalStations }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Estações Ativas</h3>
                    <p class="text-3xl font-bold text-green-600">{{ $activeStations }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Dados</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totalMeasurements) }}</p>
                </div>
            </div>

            <!-- Lista de Estações -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($stations as $station)
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
                            <span class="font-medium">{{ number_format($station->measurements_count) }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nível Médio:</span>
                            <span class="font-medium">{{ $station->avg_nivel }}m</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vazão Média:</span>
                            <span class="font-medium">{{ $station->avg_vazao }} m³/s</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Medição:</span>
                            <span class="font-medium">
                                @if($station->last_measurement)
                                    {{ \Carbon\Carbon::parse($station->last_measurement)->format('d/m H:i') }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>