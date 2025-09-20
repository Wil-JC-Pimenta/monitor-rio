<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fly.io Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações específicas para o ambiente Fly.io
    |
    */

    'app_name' => env('FLY_APP_NAME', 'monitor-rio-piracicaba'),
    'region' => env('FLY_REGION', 'gru'),
    'url' => env('FLY_URL', 'https://monitor-rio-piracicaba.fly.dev'),
    
    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração do banco de dados para Fly.io
    |
    */
    
    'database' => [
        'connection' => 'sqlite',
        'path' => '/var/www/html/database/database.sqlite',
        'backup_enabled' => true,
        'backup_interval' => 'daily',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração de cache otimizada para Fly.io
    |
    */
    
    'cache' => [
        'driver' => 'file',
        'path' => '/tmp/cache',
        'ttl' => 3600, // 1 hora
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração de sessão para Fly.io
    |
    */
    
    'session' => [
        'driver' => 'file',
        'path' => '/tmp/sessions',
        'lifetime' => 120, // 2 horas
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Log Configuration
    |--------------------------------------------------------------------------
    |
    | Configuração de logs para Fly.io
    |
    */
    
    'logging' => [
        'driver' => 'single',
        'path' => '/var/log/app.log',
        'level' => 'error',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Health Check
    |--------------------------------------------------------------------------
    |
    | Configuração do health check
    |
    */
    
    'health_check' => [
        'enabled' => true,
        'path' => '/health',
        'timeout' => 5,
    ],
];
