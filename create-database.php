<?php

echo "🗄️ CRIANDO BANCO DE DADOS SQLITE\n";
echo "================================\n";

try {
    // Conectar ao SQLite
    $pdo = new PDO('sqlite:database/database.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Conectado ao SQLite\n";

    // Criar tabela migrations
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        migration VARCHAR(255) NOT NULL,
        batch INTEGER NOT NULL
    )");

    // Criar tabela stations
    $pdo->exec("CREATE TABLE IF NOT EXISTS stations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        code VARCHAR(255) UNIQUE NOT NULL,
        location VARCHAR(255),
        latitude DECIMAL(10,8),
        longitude DECIMAL(11,8),
        description TEXT,
        status VARCHAR(20) DEFAULT 'active',
        last_measurement TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Criar tabela river_data
    $pdo->exec("CREATE TABLE IF NOT EXISTS river_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        station_id INTEGER NOT NULL,
        nivel DECIMAL(8,3),
        vazao DECIMAL(10,3),
        chuva DECIMAL(8,2),
        data_medicao TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (station_id) REFERENCES stations(id) ON DELETE CASCADE
    )");

    // Criar índices
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_river_data_station_date ON river_data(station_id, data_medicao)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_river_data_date ON river_data(data_medicao)");

    echo "✅ Tabelas criadas\n";

    // Inserir registro de migração
    $pdo->exec("INSERT OR IGNORE INTO migrations (migration, batch) VALUES 
        ('0001_01_01_000000_create_users_table', 1),
        ('0001_01_01_000001_create_cache_table', 1),
        ('0001_01_01_000002_create_jobs_table', 1),
        ('2025_09_01_143835_create_stations_table', 1),
        ('2025_09_01_005051_create_river_data_table', 1)
    ");

    echo "✅ Migrações registradas\n";

    // Inserir estações do Rio Piracicaba
    $stations = [
        ['PIR001', 'Rio Piracicaba - Ipatinga', 'Vale do Aço - MG', -19.4708, -42.5369, 'Estação principal do Rio Piracicaba em Ipatinga'],
        ['PIR002', 'Rio Piracicaba - Timóteo', 'Vale do Aço - MG', -19.5831, -42.6442, 'Estação do Rio Piracicaba em Timóteo'],
        ['PIR003', 'Rio Piracicaba - Coronel Fabriciano', 'Vale do Aço - MG', -19.5189, -42.6289, 'Estação do Rio Piracicaba em Coronel Fabriciano'],
    ];

    foreach ($stations as $station) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO stations (code, name, location, latitude, longitude, description, status, last_measurement) VALUES (?, ?, ?, ?, ?, ?, 'active', ?)");
        $stmt->execute([$station[0], $station[1], $station[2], $station[3], $station[4], $station[5], date('Y-m-d H:i:s')]);
    }

    echo "✅ Estações inseridas\n";

    // Inserir dados hidrológicos mock
    $stationIds = $pdo->query("SELECT id FROM stations")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($stationIds as $stationId) {
        for ($i = 0; $i < 48; $i++) { // 48 horas de dados
            $measurementTime = date('Y-m-d H:i:s', strtotime("-{$i} hours"));
            
            // Simular variações realistas do Rio Piracicaba
            $baseNivel = 2.5 + (sin($i * 0.3) * 0.5);
            $baseVazao = 120 + (cos($i * 0.2) * 30);
            $baseChuva = $i < 12 ? rand(0, 3) : 0;

            $nivel = round($baseNivel + (rand(-15, 15) / 100), 2);
            $vazao = round($baseVazao + (rand(-20, 20)), 1);
            $chuva = $baseChuva;

            $stmt = $pdo->prepare("INSERT INTO river_data (station_id, nivel, vazao, chuva, data_medicao) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$stationId, $nivel, $vazao, $chuva, $measurementTime]);
        }
    }

    echo "✅ Dados hidrológicos inseridos\n";

    // Verificar dados
    $count = $pdo->query("SELECT COUNT(*) FROM river_data")->fetchColumn();
    echo "📊 Total de registros: {$count}\n";

    echo "\n🎉 BANCO DE DADOS CRIADO COM SUCESSO!\n";
    echo "====================================\n";

} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
}
