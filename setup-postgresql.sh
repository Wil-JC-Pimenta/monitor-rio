#!/bin/bash

echo "🐘 Configurando PostgreSQL para Monitor Rio Piracicaba..."

# Criar arquivo .env com configurações PostgreSQL
cat > .env << 'EOF'
APP_NAME="Monitor Rio Piracicaba"
APP_ENV=local
APP_KEY=base64:your-app-key-here
APP_DEBUG=true
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost:8000

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

# Configuração PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=monitor_rio
DB_USERNAME=postgres
DB_PASSWORD=postgres

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# Configurações da API da ANA
ANA_API_BASE_URL=https://www.ana.gov.br/hidrowebservice
ANA_API_TIMEOUT=30
ANA_API_RETRY_ATTEMPTS=3
ANA_API_RETRY_DELAY=1000

# Estações do Rio Piracicaba no Vale do Aço
PIRACICABA_STATIONS=12345678,87654321,11223344
EOF

echo "✅ Arquivo .env criado com configurações PostgreSQL"

# Gerar chave da aplicação
php artisan key:generate

echo "✅ Chave da aplicação gerada"

# Criar banco de dados PostgreSQL
echo "🗄️ Criando banco de dados PostgreSQL..."
sudo -u postgres psql -c "CREATE DATABASE monitor_rio;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE monitor_rio TO postgres;"

echo "✅ Banco de dados 'monitor_rio' criado"

# Executar migrações
echo "🔄 Executando migrações..."
php artisan migrate

echo "✅ Migrações executadas com sucesso"

# Executar seeders
echo "🌱 Executando seeders..."
php artisan db:seed

echo "✅ Seeders executados com sucesso"

echo "🎉 Sistema configurado com PostgreSQL!"
echo "💡 Para executar o sistema: php artisan river:fetch --mock"
