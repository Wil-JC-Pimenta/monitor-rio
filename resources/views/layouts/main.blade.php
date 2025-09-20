<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monitor Rio Piracicaba')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .nav-link {
            position: relative;
            transition: all 0.3s ease;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 50%;
            background-color: #3b82f6;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }
        .nav-link.active::after,
        .nav-link:hover::after {
            width: 100%;
        }
        .chart-container {
            position: relative;
            height: 400px;
        }
        .loading-spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @yield('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-water text-white text-xl"></i>
                        </div>
                        <div>
                            <span class="text-xl font-bold text-gray-900">Monitor Rio Piracicaba</span>
                            <p class="text-xs text-gray-500">Sistema de Monitoramento Hidrológico</p>
                        </div>
                    </a>
                </div>
                
                <div class="hidden md:flex items-center space-x-1">
                    <a href="/" class="nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->is('/') ? 'text-blue-600 bg-blue-50 active' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/stations" class="nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->is('stations') ? 'text-blue-600 bg-blue-50 active' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                        <i class="fas fa-satellite-dish mr-2"></i>Estações
                    </a>
                    <a href="/data" class="nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->is('data') ? 'text-blue-600 bg-blue-50 active' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                        <i class="fas fa-chart-line mr-2"></i>Dados
                    </a>
                    <a href="/analytics" class="nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->is('analytics') ? 'text-blue-600 bg-blue-50 active' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                        <i class="fas fa-chart-bar mr-2"></i>Análises
                    </a>
                    <a href="/alerts" class="nav-link px-4 py-2 rounded-lg text-sm font-medium {{ request()->is('alerts') ? 'text-blue-600 bg-blue-50 active' : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50' }}">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Alertas
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-gray-700 hover:text-blue-600 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-gray-200">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <a href="/stations" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    <i class="fas fa-satellite-dish mr-2"></i>Estações
                </a>
                <a href="/data" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    <i class="fas fa-chart-line mr-2"></i>Dados
                </a>
                <a href="/analytics" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    <i class="fas fa-chart-bar mr-2"></i>Análises
                </a>
                <a href="/alerts" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Alertas
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-water text-white"></i>
                        </div>
                        <span class="text-xl font-bold">Monitor Rio Piracicaba</span>
                    </div>
                    <p class="text-gray-300 mb-4 max-w-md">
                        Sistema de monitoramento hidrológico em tempo real do Rio Piracicaba no Vale do Aço. 
                        Dados fornecidos pela ANA (Agência Nacional de Águas e Saneamento Básico).
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://github.com" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                        <a href="https://twitter.com" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="mailto:contato@monitorpiracicaba.com" class="text-gray-400 hover:text-white transition-colors">
                            <i class="fas fa-envelope text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Dados Monitorados</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-2"></i>
                            Nível do rio
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-2"></i>
                            Vazão hidrológica
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-2"></i>
                            Precipitação
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-400 mr-2"></i>
                            Alertas meteorológicos
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Informações</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="/about" class="hover:text-white transition-colors">Sobre o Projeto</a></li>
                        <li><a href="/api" class="hover:text-white transition-colors">API Documentation</a></li>
                        <li><a href="/privacy" class="hover:text-white transition-colors">Privacidade</a></li>
                        <li><a href="/terms" class="hover:text-white transition-colors">Termos de Uso</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400 text-sm">
                        &copy; 2025 Monitor Rio Piracicaba. Todos os direitos reservados.
                    </p>
                    <div class="flex items-center space-x-4 mt-4 md:mt-0">
                        <span class="text-gray-400 text-sm">Licenciado sob</span>
                        <a href="https://opensource.org/licenses/MIT" class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                            MIT License
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Auto-refresh data every 5 minutes
        setInterval(function() {
            if (typeof refreshData === 'function') {
                refreshData();
            }
        }, 300000); // 5 minutes

        // Global utility functions
        function formatNumber(num, decimals = 2) {
            return new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }).format(num);
        }

        function formatDate(date) {
            return new Intl.DateTimeFormat('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(new Date(date));
        }

        function showLoading(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                element.innerHTML = '<div class="flex justify-center items-center h-32"><div class="loading-spinner"></div></div>';
            }
        }
    </script>

    @yield('scripts')
</body>
</html>

