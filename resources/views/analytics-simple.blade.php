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
                    <p class="text-sm text-gray-500">Últimos 30 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nível Mínimo</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($minNivel, 2) }}m</p>
                    <p class="text-sm text-gray-500">Últimos 30 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Vazão Máxima</h3>
                    <p class="text-3xl font-bold text-green-600">{{ number_format($maxVazao, 1) }} m³/s</p>
                    <p class="text-sm text-gray-500">Últimos 30 dias</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Chuva Acumulada</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format($totalChuva, 1) }}mm</p>
                    <p class="text-sm text-gray-500">Últimos 30 dias</p>
                </div>
            </div>

            <!-- Resumo das Estações -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Resumo das Estações</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($stations as $station)
                    <div class="p-4 border rounded-lg">
                        <h3 class="font-medium text-gray-900">{{ $station['name'] }}</h3>
                        <p class="text-sm text-gray-500">{{ $station['code'] }}</p>
                        <p class="text-sm text-gray-500">Medições: {{ number_format($station['river_data_count']) }}</p>
                        <p class="text-sm text-gray-500">Nível Médio: {{ $station['avg_nivel'] }}m</p>
                        <p class="text-sm text-gray-500">Vazão Média: {{ $station['avg_vazao'] }} m³/s</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>
