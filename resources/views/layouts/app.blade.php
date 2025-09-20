<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monitor Rio Piracicaba')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @yield('styles')
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <i class="fas fa-water text-blue-600 text-2xl"></i>
                        <span class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('/') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-home mr-2"></i>Início
                    </a>
                    <a href="/api/stations" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('api/stations') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-satellite-dish mr-2"></i>Estações
                    </a>
                    <a href="/api/river-data" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('api/river-data') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-chart-line mr-2"></i>Dados
                    </a>
                    <a href="/api/dashboard" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('api/dashboard') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-cloud-sun mr-2"></i>Dashboard
                    </a>
                    <a href="/api/river-data/stats" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium {{ request()->is('api/river-data/stats') ? 'bg-blue-50 text-blue-600' : '' }}">
                        <i class="fas fa-chart-bar mr-2"></i>Estatísticas
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Monitor Rio Piracicaba</h3>
                    <p class="text-gray-300">Sistema de monitoramento hidrológico em tempo real do Rio Piracicaba no Vale do Aço.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Dados</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-check mr-2"></i>Nível do rio</li>
                        <li><i class="fas fa-check mr-2"></i>Vazão</li>
                        <li><i class="fas fa-check mr-2"></i>Precipitação</li>
                        <li><i class="fas fa-check mr-2"></i>Alertas meteorológicos</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Fonte</h3>
                    <p class="text-gray-300">Dados fornecidos pela ANA (Agência Nacional de Águas e Saneamento Básico)</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Monitor Rio Piracicaba. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>

