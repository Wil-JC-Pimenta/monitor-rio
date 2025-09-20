#!/bin/bash

echo "🚀 INICIANDO SISTEMA MONITOR RIO PIRACICABA"
echo "=========================================="

# Parar processos anteriores
pkill -f "php.*serve" 2>/dev/null || true

# Navegar para o diretório correto
cd "/home/wilker/Área de Trabalho/monitor-rio"

# Verificar se estamos no diretório correto
if [ ! -f "artisan" ]; then
    echo "❌ Erro: Não estamos no diretório correto do Laravel"
    exit 1
fi

# Configurar permissões
echo "🔧 Configurando permissões..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod 664 storage/logs/laravel.log 2>/dev/null || true

# Criar diretórios necessários
mkdir -p storage/logs storage/framework/views storage/framework/cache storage/framework/sessions bootstrap/cache

# Verificar se o banco existe
if [ ! -f "/tmp/monitor_rio.sqlite" ]; then
    echo "🗄️ Criando banco de dados..."
    php create-database.php
fi

# Iniciar servidor PHP simples
echo "🌐 Iniciando servidor na porta 8000..."
echo "✅ Sistema disponível em: http://localhost:8000"
echo "📊 API disponível em: http://localhost:8000/api/river-data/stats"
echo "🏭 Estações disponíveis em: http://localhost:8000/api/stations"
echo ""
echo "Pressione Ctrl+C para parar o servidor"
echo ""

# Iniciar servidor
php -S localhost:8000 test-api.php
