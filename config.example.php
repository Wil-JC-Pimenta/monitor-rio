<?php
/**
 * Arquivo de configuração de exemplo
 * Copie este arquivo para .env e configure suas credenciais
 */

return [
    'app_name' => 'Monitor Rio Piracicaba',
    'app_env' => 'local',
    'app_debug' => true,
    'app_url' => 'http://localhost:8000',
    
    'database' => [
        'connection' => 'sqlite',
        'database' => 'database/database.sqlite',
    ],
    
    'ana_api' => [
        'identificador' => 'your_ana_identifier_here',
        'senha' => 'your_ana_password_here',
        'piracicaba_stations' => '56690000,56690001,56690002,56690003,56690004',
        'cache_enabled' => true,
        'cache_ttl' => 3600,
        'logging_enabled' => true,
        'log_channel' => 'single',
        'log_level' => 'info',
    ],
    
    'logging' => [
        'channel' => 'stack',
        'level' => 'debug',
        'path' => '/tmp/monitor-rio-logs/laravel.log',
    ],
];
