#!/bin/bash

# =============================================================================
# CONFIGURAÇÃO DA APLICAÇÃO PARA PRODUÇÃO - MONITOR RIO
# Execute este script como root: sudo ./configure-app-production.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration variables
APP_NAME="monitor-rio"
APP_PATH="/var/www/$APP_NAME"
NGINX_USER="www-data"

echo -e "${BLUE}⚙️  Configurando Aplicação - Monitor Rio - Produção${NC}"
echo "=============================================================================="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Navigate to application directory
cd $APP_PATH

# Install PHP dependencies
echo -e "${BLUE}📦 Instalando dependências PHP...${NC}"
sudo -u $NGINX_USER composer install --no-dev --optimize-autoloader
print_status "Dependências PHP instaladas"

# Create .env file for production
echo -e "${BLUE}⚙️  Configurando ambiente de produção...${NC}"
cat > $APP_PATH/.env << EOF
APP_NAME="Monitor Rio"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://monitor-rio.piracicaba

LOG_CHANNEL=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=sqlite
DB_DATABASE=$APP_PATH/database/database.sqlite

BROADCAST_DRIVER=log
CACHE_STORE=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="\${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"
EOF

# Generate application key
echo -e "${BLUE}🔑 Gerando chave da aplicação...${NC}"
sudo -u $NGINX_USER php artisan key:generate --force
print_status "Chave da aplicação gerada"

# Create SQLite database
echo -e "${BLUE}🗄️  Criando banco de dados SQLite...${NC}"
touch $APP_PATH/database/database.sqlite
chown $NGINX_USER:$NGINX_USER $APP_PATH/database/database.sqlite
print_status "Banco de dados SQLite criado"

# Set correct permissions
echo -e "${BLUE}🔐 Configurando permissões...${NC}"
chown -R $NGINX_USER:$NGINX_USER $APP_PATH
chmod -R 755 $APP_PATH/storage
chmod -R 755 $APP_PATH/bootstrap/cache
print_status "Permissões configuradas"

# Run database migrations
echo -e "${BLUE}🗄️  Executando migrações...${NC}"
sudo -u $NGINX_USER php artisan migrate --force
print_status "Migrações executadas"

# Generate sample data
echo -e "${BLUE}📊 Gerando dados de exemplo...${NC}"
sudo -u $NGINX_USER php artisan data:generate || echo "Comando de dados não encontrado, continuando..."
print_status "Dados de exemplo gerados"

# Optimize Laravel
echo -e "${BLUE}⚡ Otimizando Laravel...${NC}"
sudo -u $NGINX_USER php artisan config:cache
sudo -u $NGINX_USER php artisan route:cache
sudo -u $NGINX_USER php artisan view:cache
print_status "Laravel otimizado"

print_status "Configuração da aplicação concluída!"
echo ""
echo -e "${YELLOW}📋 Próximos passos:${NC}"
echo "1. Configure o Nginx: sudo ./configure-nginx-production.sh"
echo "2. Configure SSL: sudo ./scripts/configure-ssl.sh"
echo "3. Configure serviços: sudo ./scripts/configure-services.sh"
echo ""
echo -e "${GREEN}🎉 Aplicação configurada com sucesso!${NC}"

