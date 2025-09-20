<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dados - Monitor Rio Piracicaba</title>
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
                        <a href="/data" class="text-blue-600 font-medium">Dados</a>
                        <a href="/analytics" class="text-gray-700 hover:text-blue-600">Análises</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-8">Dados Hidrológicos</h1>
            
            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Estação</label>
                        <select class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="">Todas as estações</option>
                            <option value="1">Rio das Velhas - BH</option>
                            <option value="2">Rio São Francisco - Pirapora</option>
                            <option value="3">Rio Doce - GV</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Dados</label>
                        <select class="w-full border border-gray-300 rounded-md px-3 py-2">
                            <option value="nivel">Nível</option>
                            <option value="vazao">Vazão</option>
                            <option value="chuva">Chuva</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Início</label>
                        <input type="date" class="w-full border border-gray-300 rounded-md px-3 py-2" value="{{ now()->subDays(7)->format('Y-m-d') }}">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fim</label>
                        <input type="date" class="w-full border border-gray-300 rounded-md px-3 py-2" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                        Aplicar Filtros
                    </button>
                    <button class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 ml-2">
                        Limpar
                    </button>
                </div>
            </div>

            <!-- Gráfico Principal -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Gráfico de Dados</h2>
                <div class="h-64 bg-gray-100 rounded flex items-center justify-center">
                    <p class="text-gray-500">Gráfico será carregado aqui</p>
                </div>
            </div>

            <!-- Tabela de Dados -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900">Dados Detalhados</h2>
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
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rio das Velhas - BH
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ now()->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    2.45
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    15.2
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    0.5
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rio São Francisco - Pirapora
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ now()->subHour()->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    3.12
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    22.8
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    1.2
                                </td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Rio Doce - GV
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ now()->subHours(2)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    1.87
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    12.5
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    0.8
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

