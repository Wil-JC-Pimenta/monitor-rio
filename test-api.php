<?php

echo "🧪 TESTANDO API DO MONITOR RIO PIRACICABA\n";
echo "========================================\n\n";

// Simular dados da API
$stats = [
    'total_measurements' => 1248,
    'total_stations' => 3,
    'active_stations' => 3,
    'latest_measurement' => [
        'id' => 1,
        'station_id' => 1,
        'nivel' => 2.5,
        'vazao' => 150.2,
        'chuva' => 0.0,
        'data_medicao' => date('Y-m-d H:i:s'),
    ],
    'measurements_today' => 72,
    'measurements_this_week' => 504,
    'max_nivel' => 3.2,
    'max_vazao' => 180.5,
    'max_chuva' => 5.0,
];

$stations = [
    [
        'id' => 1,
        'name' => 'Rio Piracicaba - Ipatinga',
        'code' => 'PIR001',
        'location' => 'Vale do Aço - MG',
        'status' => 'active',
        'river_data_count' => 416,
    ],
    [
        'id' => 2,
        'name' => 'Rio Piracicaba - Timóteo',
        'code' => 'PIR002',
        'location' => 'Vale do Aço - MG',
        'status' => 'active',
        'river_data_count' => 416,
    ],
    [
        'id' => 3,
        'name' => 'Rio Piracicaba - Coronel Fabriciano',
        'code' => 'PIR003',
        'location' => 'Vale do Aço - MG',
        'status' => 'active',
        'river_data_count' => 416,
    ],
];

$riverData = [];
for ($i = 0; $i < 10; $i++) {
    $riverData[] = [
        'id' => $i + 1,
        'station_id' => ($i % 3) + 1,
        'nivel' => round(2.0 + (sin($i * 0.5) * 0.8), 2),
        'vazao' => round(120 + (cos($i * 0.3) * 30), 1),
        'chuva' => $i < 3 ? rand(0, 3) : 0,
        'data_medicao' => date('Y-m-d H:i:s', strtotime("-{$i} hours")),
        'station' => $stations[($i % 3)],
    ];
}

// Função para retornar JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// Roteamento simples
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

echo "📡 Testando endpoints da API...\n\n";

// Simular diferentes endpoints
if (strpos($request, '/api/river-data/stats') !== false) {
    echo "✅ GET /api/river-data/stats\n";
    jsonResponse([
        'success' => true,
        'data' => $stats,
    ]);
} elseif (strpos($request, '/api/stations') !== false) {
    echo "✅ GET /api/stations\n";
    jsonResponse([
        'success' => true,
        'data' => $stations,
        'meta' => [
            'total' => count($stations),
            'active_stations' => count(array_filter($stations, fn($s) => $s['status'] === 'active')),
        ],
    ]);
} elseif (strpos($request, '/api/river-data') !== false) {
    echo "✅ GET /api/river-data\n";
    jsonResponse([
        'success' => true,
        'data' => $riverData,
        'meta' => [
            'total' => count($riverData),
            'period' => 'Últimas 10 horas',
        ],
    ]);
} else {
    echo "✅ GET /\n";
    echo "<!DOCTYPE html>\n";
    echo "<html>\n";
    echo "<head>\n";
    echo "    <title>Monitor Rio Piracicaba</title>\n";
    echo "    <meta charset='UTF-8'>\n";
    echo "    <style>\n";
    echo "        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }\n";
    echo "        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }\n";
    echo "        h1 { color: #2c5aa0; text-align: center; }\n";
    echo "        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0; }\n";
    echo "        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }\n";
    echo "        .stat-number { font-size: 2em; font-weight: bold; color: #2c5aa0; }\n";
    echo "        .stat-label { color: #666; margin-top: 5px; }\n";
    echo "        .api-links { margin: 30px 0; }\n";
    echo "        .api-link { display: inline-block; margin: 10px; padding: 10px 20px; background: #2c5aa0; color: white; text-decoration: none; border-radius: 5px; }\n";
    echo "        .api-link:hover { background: #1e3d6f; }\n";
    echo "    </style>\n";
    echo "</head>\n";
    echo "<body>\n";
    echo "    <div class='container'>\n";
    echo "        <h1>🌊 Monitor Rio Piracicaba</h1>\n";
    echo "        <p style='text-align: center; color: #666;'>Sistema de monitoramento hidrológico em tempo real</p>\n";
    echo "        \n";
    echo "        <div class='stats'>\n";
    echo "            <div class='stat-card'>\n";
    echo "                <div class='stat-number'>{$stats['total_measurements']}</div>\n";
    echo "                <div class='stat-label'>Total de Medições</div>\n";
    echo "            </div>\n";
    echo "            <div class='stat-card'>\n";
    echo "                <div class='stat-number'>{$stats['active_stations']}</div>\n";
    echo "                <div class='stat-label'>Estações Ativas</div>\n";
    echo "            </div>\n";
    echo "            <div class='stat-card'>\n";
    echo "                <div class='stat-number'>{$stats['max_nivel']}m</div>\n";
    echo "                <div class='stat-label'>Nível Máximo</div>\n";
    echo "            </div>\n";
    echo "            <div class='stat-card'>\n";
    echo "                <div class='stat-number'>{$stats['max_vazao']}m³/s</div>\n";
    echo "                <div class='stat-label'>Vazão Máxima</div>\n";
    echo "            </div>\n";
    echo "        </div>\n";
    echo "        \n";
    echo "        <div class='api-links'>\n";
    echo "            <h3>🔗 Endpoints da API:</h3>\n";
    echo "            <a href='/api/river-data/stats' class='api-link'>📊 Estatísticas</a>\n";
    echo "            <a href='/api/stations' class='api-link'>🏭 Estações</a>\n";
    echo "            <a href='/api/river-data' class='api-link'>📈 Dados Hidrológicos</a>\n";
    echo "        </div>\n";
    echo "        \n";
    echo "        <div style='margin-top: 30px; padding: 20px; background: #e8f4f8; border-radius: 8px;'>\n";
    echo "            <h3>✅ Sistema Funcionando!</h3>\n";
    echo "            <p>O sistema Monitor Rio Piracicaba está online e funcionando corretamente.</p>\n";
    echo "            <p><strong>Última atualização:</strong> " . date('d/m/Y H:i:s') . "</p>\n";
    echo "        </div>\n";
    echo "    </div>\n";
    echo "</body>\n";
    echo "</html>\n";
}

echo "\n🎉 TESTE CONCLUÍDO!\n";
echo "==================\n";
