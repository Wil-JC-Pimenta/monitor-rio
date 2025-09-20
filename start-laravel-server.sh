#!/bin/bash

echo "🚀 INICIANDO SERVIDOR LARAVEL"
echo "============================"

# 1. Parar Apache se estiver rodando
echo "1️⃣ Parando Apache..."
systemctl stop apache2 2>/dev/null || true

# 2. Verificar se o arquivo .env está correto
echo "2️⃣ Verificando arquivo .env..."
if [ ! -f .env ]; then
    echo "Criando arquivo .env..."
    cat > .env << 'EOF'
APP_NAME="Monitor Rio Piracicaba"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/home/wilker/Área de Trabalho/monitor-rio/database/database.sqlite

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

CACHE_STORE=database
QUEUE_CONNECTION=database

ANA_API_BASE_URL=https://www.ana.gov.br/hidrowebservice
ANA_API_TIMEOUT=30
ANA_API_RETRY_ATTEMPTS=3
ANA_API_RETRY_DELAY=1000

PIRACICABA_STATIONS=12345678,87654321,11223344
EOF
fi

# 3. Gerar chave da aplicação
echo "3️⃣ Gerando chave da aplicação..."
php artisan key:generate --force

# 4. Criar banco de dados se não existir
echo "4️⃣ Verificando banco de dados..."
if [ ! -f database/database.sqlite ]; then
    echo "Criando banco de dados..."
    php create-database.php
fi

# 5. Executar migrações
echo "5️⃣ Executando migrações..."
php artisan migrate --force

# 6. Executar dados mock
echo "6️⃣ Executando dados mock..."
php artisan river:fetch --mock

# 7. Iniciar servidor Laravel
echo "7️⃣ Iniciando servidor Laravel..."
echo "🌐 Sistema disponível em: http://localhost:8000"
echo "📊 API disponível em: http://localhost:8000/api/river-data/stats"
echo "🏭 Estações disponíveis em: http://localhost:8000/api/stations"
echo ""
echo "Pressione Ctrl+C para parar o servidor"
echo ""

php artisan serve --host=0.0.0.0 --port=8000
