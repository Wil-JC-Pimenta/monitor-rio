<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Rio Piracicaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</h1>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">Dashboard</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Estações</h3>
                    <p class="text-3xl font-bold text-blue-600">{{ \App\Models\Station::count() }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Dados</h3>
                    <p class="text-3xl font-bold text-green-600">{{ \App\Models\RiverData::count() }}</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Nível Médio</h3>
                    <p class="text-3xl font-bold text-purple-600">{{ number_format(\App\Models\RiverData::whereNotNull('nivel')->avg('nivel'), 2) }}m</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estações Ativas</h3>
                <div class="space-y-2">
                    @foreach(\App\Models\Station::all() as $station)
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                        <span class="font-medium">{{ $station->name }}</span>
                        <span class="text-sm text-gray-500">{{ $station->code }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</body>
</html>

