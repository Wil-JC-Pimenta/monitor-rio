<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ANA API Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com a API da Agência Nacional de Águas
    | e Saneamento Básico (ANA)
    |
    */

    'base_url' => env('ANA_API_BASE_URL', 'https://www.ana.gov.br/hidrowebservice'),
    
    'timeout' => env('ANA_API_TIMEOUT', 30),
    
    'retry_attempts' => env('ANA_API_RETRY_ATTEMPTS', 3),
    
    'retry_delay' => env('ANA_API_RETRY_DELAY', 1000), // em milissegundos
    
    /*
    |--------------------------------------------------------------------------
    | Autenticação ANA
    |--------------------------------------------------------------------------
    |
    | Configurações de autenticação para a API da ANA
    | Baseado na documentação oficial do Swagger
    |
    */
    
    'auth' => [
        'identificador' => env('ANA_API_IDENTIFICADOR', ''),
        'senha' => env('ANA_API_SENHA', ''),
        'token_ttl' => env('ANA_TOKEN_TTL', 3600), // 1 hora
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Estações do Rio Piracicaba no Vale do Aço
    |--------------------------------------------------------------------------
    |
    | Códigos das estações hidrológicas da ANA para o Rio Piracicaba
    | no Vale do Aço. Estes códigos devem ser obtidos da documentação
    | oficial da ANA ou através de consulta à API.
    |
    */
    
    'stations' => [
        'piracicaba' => [
            'codes' => explode(',', env('PIRACICABA_STATIONS', '12345678,87654321,11223344')),
            'name' => 'Rio Piracicaba - Vale do Aço',
            'region' => 'Minas Gerais',
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Parâmetros de Consulta
    |--------------------------------------------------------------------------
    |
    | Parâmetros padrão para consultas à API da ANA
    | Baseado na documentação oficial: https://www.ana.gov.br/hidrowebservice/swagger-ui/index.html
    |
    */
    
    'default_params' => [
        'dataInicio' => now()->subDays(7)->format('d/m/Y'),
        'dataFim' => now()->format('d/m/Y'),
        'tipoDados' => 1, // 1 = Cota (nível), 2 = Vazão, 3 = Chuva
        'nivelConsistencia' => '', // Vazio = todos os níveis
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Endpoints da API
    |--------------------------------------------------------------------------
    |
    | Endpoints disponíveis na API da ANA
    |
    */
    
    'endpoints' => [
        'auth' => '/EstacoesTelemetricas/OAUth/v1',
        'estacoes' => '/EstacoesTelemetricas/HidroInventarioEstacoes/v1',
        'dados_telemetricos' => '/EstacoesTelemetricas/HidroinfoanaSerieTelemetricaAdotada/v2',
        'dados_cotas' => '/EstacoesTelemetricas/HidroSerieCotas/v1',
        'dados_vazao' => '/EstacoesTelemetricas/HidroSerieVazao/v1',
        'dados_chuva' => '/EstacoesTelemetricas/HidroSerieChuva/v1',
        'qualidade_agua' => '/EstacoesTelemetricas/HidroSerieQA/v1',
        'rios' => '/EstacoesTelemetricas/HidroRio/v1',
        'municipios' => '/EstacoesTelemetricas/HidroMunicipio/v1',
        'bacias' => '/EstacoesTelemetricas/HidroBacia/v1',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações de cache para dados da ANA
    |
    */
    
    'cache' => [
        'enabled' => env('ANA_CACHE_ENABLED', true),
        'ttl' => env('ANA_CACHE_TTL', 3600), // 1 hora em segundos
        'prefix' => 'ana_data_',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações de logging para monitoramento da API
    |
    */
    
    'logging' => [
        'enabled' => env('ANA_LOGGING_ENABLED', true),
        'channel' => env('ANA_LOG_CHANNEL', 'daily'),
        'level' => env('ANA_LOG_LEVEL', 'info'),
    ],
];
