<?php
// Script para corrigir o problema de logging do Laravel
echo "=== CORRIGINDO SISTEMA DE LOGGING ===\n";

// Criar diretório de log alternativo
$logDir = '/tmp/monitor-rio-logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
    echo "✅ Diretório de log alternativo criado: $logDir\n";
}

// Atualizar arquivo .env para usar log alternativo
$envFile = '.env';
$envContent = file_get_contents($envFile);

// Adicionar configuração de log se não existir
if (strpos($envContent, 'LOG_CHANNEL') === false) {
    $envContent .= "\nLOG_CHANNEL=stack\n";
    $envContent .= "LOG_DEPRECATIONS_CHANNEL=null\n";
    $envContent .= "LOG_LEVEL=debug\n";
}

// Salvar arquivo .env atualizado
file_put_contents($envFile, $envContent);
echo "✅ Arquivo .env atualizado\n";

// Criar arquivo de configuração de log personalizado
$logConfig = '<?php
return [
    "default" => "stack",
    "channels" => [
        "stack" => [
            "driver" => "stack",
            "channels" => ["single"],
            "ignore_exceptions" => false,
        ],
        "single" => [
            "driver" => "single",
            "path" => "/tmp/monitor-rio-logs/laravel.log",
            "level" => "debug",
        ],
    ],
];';

file_put_contents('config/logging.php', $logConfig);
echo "✅ Configuração de logging personalizada criada\n";

// Limpar cache
echo "Limpando cache do Laravel...\n";
system('php artisan config:clear 2>/dev/null');
system('php artisan route:clear 2>/dev/null');
system('php artisan view:clear 2>/dev/null');

echo "✅ Sistema de logging corrigido!\n";
echo "✅ Logs serão salvos em: $logDir\n";
echo "✅ Agora você pode iniciar o servidor sem erros de permissão\n";
?>
