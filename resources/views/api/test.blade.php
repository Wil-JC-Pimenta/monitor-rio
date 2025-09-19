<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste - Monitor Rio Piracicaba</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Teste de Dados</h1>
        
        @php
            $totalData = \App\Models\RiverData::count();
            $stations = \App\Models\Station::count();
            $riverData = \App\Models\RiverData::orderBy('data_medicao', 'desc')->limit(5)->get();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900">Total de Dados</h3>
                <p class="text-3xl font-bold text-blue-600">{{ number_format($totalData) }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900">Estações</h3>
                <p class="text-3xl font-bold text-green-600">{{ $stations }}</p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold text-gray-900">Última Atualização</h3>
                <p class="text-3xl font-bold text-orange-600">{{ now()->format('H:i') }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Dados Recentes</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nível</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vazão</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Chuva</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($riverData as $data)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $data->data_medicao->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $data->nivel ? number_format($data->nivel, 2) . 'm' : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $data->vazao ? number_format($data->vazao, 1) . 'm³/s' : 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $data->chuva ? number_format($data->chuva, 1) . 'mm' : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
