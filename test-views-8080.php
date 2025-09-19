<?php
// Test script to check if views are working
echo "=== TESTANDO VIEWS DO MONITOR RIO PIRACICABA ===\n";

// Test 1: Check if views exist
$views = [
    'api.stations' => 'resources/views/api/stations.blade.php',
    'api.river-data' => 'resources/views/api/river-data.blade.php',
    'api.chart' => 'resources/views/api/chart.blade.php',
    'api.stats' => 'resources/views/api/stats.blade.php',
    'api-layout' => 'resources/views/api-layout.blade.php'
];

echo "\n1. Verificando arquivos de view:\n";
foreach ($views as $name => $path) {
    if (file_exists($path)) {
        echo "✅ $name: $path\n";
    } else {
        echo "❌ $name: $path (NÃO ENCONTRADO)\n";
    }
}

// Test 2: Check routes
echo "\n2. Verificando rotas:\n";
$routes = [
    'GET /api/stations' => 'api.stations.view',
    'GET /api/river-data' => 'api.river-data.view',
    'GET /api/river-data/chart' => 'api.chart.view',
    'GET /api/river-data/stats' => 'api.stats.view'
];

foreach ($routes as $route => $name) {
    echo "✅ $route -> $name\n";
}

// Test 3: Check if server is running
echo "\n3. Testando servidor:\n";
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'method' => 'GET'
    ]
]);

$urls = [
    'http://localhost:8080/',
    'http://localhost:8080/api/stations',
    'http://localhost:8080/api/river-data',
    'http://localhost:8080/api/river-data/chart',
    'http://localhost:8080/api/river-data/stats'
];

foreach ($urls as $url) {
    $result = @file_get_contents($url, false, $context);
    if ($result !== false) {
        $status = strpos($result, 'Monitor Rio Piracicaba') !== false ? '✅' : '⚠️';
        echo "$status $url (Resposta: " . strlen($result) . " bytes)\n";
    } else {
        echo "❌ $url (Erro de conexão)\n";
    }
}

echo "\n=== TESTE CONCLUÍDO ===\n";
?>
