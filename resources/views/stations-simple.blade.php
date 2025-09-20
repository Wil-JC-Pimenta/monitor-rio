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
                    <p class="text-3xl font-bold text-blue-600">5</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Estações Ativas</h3>
                    <p class="text-3xl font-bold text-green-600">5</p>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900">Total de Dados</h3>
                    <p class="text-3xl font-bold text-purple-600">480</p>
                </div>
            </div>

            <!-- Lista de Estações -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-400 mr-3"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Rio das Velhas - BH</h3>
                                <p class="text-sm text-gray-500">RDV001 • Belo Horizonte, MG</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Ativa
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Medições:</span>
                            <span class="font-medium">96</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nível Médio:</span>
                            <span class="font-medium">2.45m</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vazão Média:</span>
                            <span class="font-medium">15.2 m³/s</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Medição:</span>
                            <span class="font-medium">{{ now()->format('d/m H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-400 mr-3"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Rio São Francisco - Pirapora</h3>
                                <p class="text-sm text-gray-500">RSF001 • Pirapora, MG</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Ativa
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Medições:</span>
                            <span class="font-medium">96</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nível Médio:</span>
                            <span class="font-medium">3.12m</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vazão Média:</span>
                            <span class="font-medium">22.8 m³/s</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Medição:</span>
                            <span class="font-medium">{{ now()->format('d/m H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-green-400 mr-3"></div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Rio Doce - GV</h3>
                                <p class="text-sm text-gray-500">RDC001 • Governador Valadares, MG</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Ativa
                        </span>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Medições:</span>
                            <span class="font-medium">96</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Nível Médio:</span>
                            <span class="font-medium">1.87m</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Vazão Média:</span>
                            <span class="font-medium">12.5 m³/s</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-500">Última Medição:</span>
                            <span class="font-medium">{{ now()->format('d/m H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

