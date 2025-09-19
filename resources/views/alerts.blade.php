@extends('layouts.main')

@section('title', 'Sistema de Alertas - Monitor Rio Piracicaba')

@section('content')
@php
    // Load data for alerts
    $stations = \App\Models\Station::all();
    $recentData = \App\Models\RiverData::with('station')
        ->orderBy('data_medicao', 'desc')
        ->limit(100)
        ->get();
    
    // Calculate alert levels
    $averageNivel = \App\Models\RiverData::whereNotNull('nivel')->avg('nivel') ?: 0;
    $maxNivel = \App\Models\RiverData::whereNotNull('nivel')->max('nivel') ?: 0;
    $totalChuva = \App\Models\RiverData::whereNotNull('chuva')->sum('chuva') ?: 0;
    $recentChuva = \App\Models\RiverData::where('data_medicao', '>=', now()->subHours(24))
        ->whereNotNull('chuva')
        ->sum('chuva') ?: 0;
    
    // Define alert thresholds
    $nivelAlerts = [
        'normal' => ['min' => 0, 'max' => 2.0, 'color' => 'green', 'icon' => 'check-circle'],
        'attention' => ['min' => 2.0, 'max' => 2.5, 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
        'warning' => ['min' => 2.5, 'max' => 3.0, 'color' => 'orange', 'icon' => 'exclamation-triangle'],
        'danger' => ['min' => 3.0, 'max' => 999, 'color' => 'red', 'icon' => 'exclamation-circle']
    ];
    
    $chuvaAlerts = [
        'normal' => ['min' => 0, 'max' => 20, 'color' => 'green', 'icon' => 'check-circle'],
        'attention' => ['min' => 20, 'max' => 40, 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
        'warning' => ['min' => 40, 'max' => 60, 'color' => 'orange', 'icon' => 'exclamation-triangle'],
        'danger' => ['min' => 60, 'max' => 999, 'color' => 'red', 'icon' => 'exclamation-circle']
    ];
    
    // Determine current alert levels
    $currentNivelAlert = 'normal';
    $currentChuvaAlert = 'normal';
    
    foreach ($nivelAlerts as $level => $config) {
        if ($averageNivel >= $config['min'] && $averageNivel < $config['max']) {
            $currentNivelAlert = $level;
            break;
        }
    }
    
    foreach ($chuvaAlerts as $level => $config) {
        if ($recentChuva >= $config['min'] && $recentChuva < $config['max']) {
            $currentChuvaAlert = $level;
            break;
        }
    }
    
    // Generate alerts
    $alerts = [];
    
    // River level alerts
    if ($currentNivelAlert !== 'normal') {
        $alerts[] = [
            'type' => 'nivel',
            'level' => $currentNivelAlert,
            'title' => 'Alerta de Nível do Rio',
            'message' => $currentNivelAlert === 'danger' ? 
                'Nível do rio muito elevado! Risco de enchente iminente.' :
                ($currentNivelAlert === 'warning' ? 
                    'Nível do rio elevado. Monitore as condições.' :
                    'Nível do rio acima do normal. Atenção necessária.'),
            'value' => $averageNivel,
            'unit' => 'metros',
            'timestamp' => now(),
            'station' => 'Múltiplas estações'
        ];
    }
    
    // Rain alerts
    if ($currentChuvaAlert !== 'normal') {
        $alerts[] = [
            'type' => 'chuva',
            'level' => $currentChuvaAlert,
            'title' => 'Alerta de Precipitação',
            'message' => $currentChuvaAlert === 'danger' ? 
                'Chuvas intensas! Risco de alagamentos e enchentes.' :
                ($currentChuvaAlert === 'warning' ? 
                    'Chuvas fortes. Monitore o nível do rio.' :
                    'Chuvas moderadas. Atenção às condições.'),
            'value' => $recentChuva,
            'unit' => 'mm (24h)',
            'timestamp' => now(),
            'station' => 'Múltiplas estações'
        ];
    }
    
    // Station status alerts
    foreach ($stations as $station) {
        if ($station->status !== 'active') {
            $alerts[] = [
                'type' => 'station',
                'level' => 'warning',
                'title' => 'Estação Inativa',
                'message' => "A estação {$station->name} está inativa e não está coletando dados.",
                'value' => null,
                'unit' => null,
                'timestamp' => now(),
                'station' => $station->name
            ];
        }
    }
    
    // Historical alerts (simulated)
    $historicalAlerts = [
        [
            'type' => 'nivel',
            'level' => 'warning',
            'title' => 'Nível Elevado',
            'message' => 'Nível do rio atingiu 2.8m na estação PIR001',
            'value' => 2.8,
            'unit' => 'metros',
            'timestamp' => now()->subHours(6),
            'station' => 'PIR001 - Rio Piracicaba'
        ],
        [
            'type' => 'chuva',
            'level' => 'attention',
            'title' => 'Chuvas Moderadas',
            'message' => 'Precipitação de 25mm registrada nas últimas 12h',
            'value' => 25,
            'unit' => 'mm',
            'timestamp' => now()->subHours(12),
            'station' => 'PIR002 - Vale do Aço'
        ]
    ];
    
    $allAlerts = array_merge($alerts, $historicalAlerts);
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Sistema de Alertas</h1>
                    <p class="mt-2 text-gray-600">Monitoramento em tempo real de condições hidrológicas e meteorológicas</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ count($alerts) }}</div>
                            <div class="text-sm text-gray-500">Alertas Ativos</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stations->where('status', 'active')->count() }}</div>
                            <div class="text-sm text-gray-500">Estações Ativas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Current Status -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- River Level Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Nível do Rio</h3>
                    <div class="w-12 h-12 bg-{{ $nivelAlerts[$currentNivelAlert]['color'] }}-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-{{ $nivelAlerts[$currentNivelAlert]['icon'] }} text-{{ $nivelAlerts[$currentNivelAlert]['color'] }}-600 text-xl"></i>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-{{ $nivelAlerts[$currentNivelAlert]['color'] }}-600 mb-2">
                        {{ number_format($averageNivel, 2) }}m
                    </div>
                    <div class="text-sm text-gray-500 mb-4">
                        Nível atual médio
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $nivelAlerts[$currentNivelAlert]['color'] }}-100 text-{{ $nivelAlerts[$currentNivelAlert]['color'] }}-800">
                        {{ ucfirst($currentNivelAlert) }}
                    </div>
                </div>
            </div>

            <!-- Rain Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Precipitação</h3>
                    <div class="w-12 h-12 bg-{{ $chuvaAlerts[$currentChuvaAlert]['color'] }}-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-{{ $chuvaAlerts[$currentChuvaAlert]['icon'] }} text-{{ $chuvaAlerts[$currentChuvaAlert]['color'] }}-600 text-xl"></i>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-{{ $chuvaAlerts[$currentChuvaAlert]['color'] }}-600 mb-2">
                        {{ number_format($recentChuva, 1) }}mm
                    </div>
                    <div class="text-sm text-gray-500 mb-4">
                        Últimas 24 horas
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $chuvaAlerts[$currentChuvaAlert]['color'] }}-100 text-{{ $chuvaAlerts[$currentChuvaAlert]['color'] }}-800">
                        {{ ucfirst($currentChuvaAlert) }}
                    </div>
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white rounded-xl shadow-lg p-6 card-hover">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sistema</h3>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600 mb-2">
                        {{ $stations->where('status', 'active')->count() }}/{{ $stations->count() }}
                    </div>
                    <div class="text-sm text-gray-500 mb-4">
                        Estações ativas
                    </div>
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Operacional
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        @if(count($alerts) > 0)
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                Alertas Ativos
            </h3>
            <div class="space-y-4">
                @foreach($alerts as $alert)
                <div class="border-l-4 border-{{ $nivelAlerts[$alert['level']]['color'] }}-400 bg-{{ $nivelAlerts[$alert['level']]['color'] }}-50 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-{{ $nivelAlerts[$alert['level']]['icon'] }} text-{{ $nivelAlerts[$alert['level']]['color'] }}-400 text-xl"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="flex items-center justify-between">
                                <h4 class="text-sm font-medium text-{{ $nivelAlerts[$alert['level']]['color'] }}-800">
                                    {{ $alert['title'] }}
                                </h4>
                                <span class="text-xs text-{{ $nivelAlerts[$alert['level']]['color'] }}-600">
                                    {{ $alert['timestamp']->format('d/m H:i') }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-{{ $nivelAlerts[$alert['level']]['color'] }}-700">
                                {{ $alert['message'] }}
                            </p>
                            @if($alert['value'])
                            <div class="mt-2 text-sm text-{{ $nivelAlerts[$alert['level']]['color'] }}-600">
                                <strong>{{ $alert['value'] }} {{ $alert['unit'] }}</strong> - {{ $alert['station'] }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Alert History -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-history text-gray-600 mr-2"></i>
                Histórico de Alertas
            </h3>
            <div class="space-y-4">
                @foreach($historicalAlerts as $alert)
                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full bg-{{ $nivelAlerts[$alert['level']]['color'] }}-400 mr-3"></div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</h4>
                                <p class="text-sm text-gray-500">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-900">{{ $alert['value'] }} {{ $alert['unit'] }}</div>
                            <div class="text-xs text-gray-500">{{ $alert['timestamp']->format('d/m H:i') }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Alert Settings -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                <i class="fas fa-cog text-gray-600 mr-2"></i>
                Configurações de Alertas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Níveis de Alerta - Rio</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm text-gray-700">Normal</span>
                            <span class="text-sm font-medium text-gray-900">0.0 - 2.0m</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <span class="text-sm text-gray-700">Atenção</span>
                            <span class="text-sm font-medium text-gray-900">2.0 - 2.5m</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <span class="text-sm text-gray-700">Aviso</span>
                            <span class="text-sm font-medium text-gray-900">2.5 - 3.0m</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <span class="text-sm text-gray-700">Perigo</span>
                            <span class="text-sm font-medium text-gray-900">> 3.0m</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-md font-medium text-gray-900 mb-4">Níveis de Alerta - Chuva</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm text-gray-700">Normal</span>
                            <span class="text-sm font-medium text-gray-900">0 - 20mm</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                            <span class="text-sm text-gray-700">Atenção</span>
                            <span class="text-sm font-medium text-gray-900">20 - 40mm</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                            <span class="text-sm text-gray-700">Aviso</span>
                            <span class="text-sm font-medium text-gray-900">40 - 60mm</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                            <span class="text-sm text-gray-700">Perigo</span>
                            <span class="text-sm font-medium text-gray-900">> 60mm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Auto-refresh alerts every 30 seconds
setInterval(function() {
    // This would typically make an AJAX call to refresh alert data
    console.log('Refreshing alerts...');
}, 30000);

// Alert notification sound (if user allows)
function playAlertSound() {
    // Implement alert sound notification
    console.log('Playing alert sound...');
}

// Check for critical alerts
function checkCriticalAlerts() {
    const alerts = @json($alerts);
    const criticalAlerts = alerts.filter(alert => alert.level === 'danger');
    
    if (criticalAlerts.length > 0) {
        // Show critical alert notification
        if (Notification.permission === 'granted') {
            new Notification('Alerta Crítico - Monitor Rio Piracicaba', {
                body: criticalAlerts[0].message,
                icon: '/favicon.ico'
            });
        }
        playAlertSound();
    }
}

// Request notification permission
if (Notification.permission === 'default') {
    Notification.requestPermission();
}

// Check alerts on page load
document.addEventListener('DOMContentLoaded', function() {
    checkCriticalAlerts();
});
</script>
@endsection

