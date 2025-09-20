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
            
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Sistema Funcionando!</h2>
                <p class="text-gray-600 mb-4">Dados restaurados com sucesso do último commit.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-900">Total de Estações</h3>
                        <p class="text-2xl font-bold text-blue-600">{{ \App\Models\Station::count() }}</p>
                    </div>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-900">Total de Dados</h3>
                        <p class="text-2xl font-bold text-green-600">{{ \App\Models\RiverData::count() }}</p>
                    </div>
                </div>
                
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-3">Estações Disponíveis:</h3>
                    <div class="space-y-2">
                        @foreach(\App\Models\Station::all() as $station)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <span class="font-medium">{{ $station->name }}</span>
                                <span class="text-sm text-gray-500 ml-2">({{ $station->code }})</span>
                            </div>
                            <span class="text-sm {{ $station->status === 'active' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $station->status === 'active' ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

