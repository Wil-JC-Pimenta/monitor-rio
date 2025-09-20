#!/bin/bash

# =============================================================================
# CONFIGURAR LARAVEL APÓS NGINX FUNCIONAR - MONITOR RIO
# Execute este script como root: sudo ./configure-laravel-after-nginx.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}⚙️  Configurando Laravel após Nginx funcionar${NC}"
echo "=============================================================================="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Configuration variables
APP_NAME="monitor-rio"
APP_PATH="/var/www/$APP_NAME"
NGINX_USER="www-data"

# 1. Create application directory
echo -e "${BLUE}📁 Criando diretório da aplicação...${NC}"
mkdir -p $APP_PATH
chown $NGINX_USER:$NGINX_USER $APP_PATH
print_status "Diretório da aplicação criado"

# 2. Copy application files
echo -e "${BLUE}📥 Copiando arquivos da aplicação...${NC}"
cp -r /home/wilker/Área\ de\ Trabalho/monitor-rio/* $APP_PATH/
chown -R $NGINX_USER:$NGINX_USER $APP_PATH
print_status "Arquivos copiados"

# 3. Navigate to application directory
cd $APP_PATH

# 4. Fix log configuration
echo -e "${BLUE}📝 Corrigindo configuração de logs...${NC}"
mkdir -p storage/logs
touch storage/logs/laravel.log
chown -R $NGINX_USER:$NGINX_USER storage/logs
chmod -R 775 storage/logs

# Fix logging configuration
sed -i 's|/tmp/laravel.log|storage_path("logs/laravel.log")|g' config/logging.php
print_status "Configuração de logs corrigida"

# 5. Create SQLite database
echo -e "${BLUE}🗄️  Criando banco de dados SQLite...${NC}"
mkdir -p database
touch database/database.sqlite
chown $NGINX_USER:$NGINX_USER database/database.sqlite
chmod 664 database/database.sqlite
print_status "Banco de dados SQLite criado"

# 6. Configure .env for production
echo -e "${BLUE}⚙️  Configurando ambiente de produção...${NC}"
cat > .env << EOF
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
sudo -u $NGINX_USER php artisan key:generate --force
print_status "Chave da aplicação gerada"

# 7. Set correct permissions
echo -e "${BLUE}🔐 Configurando permissões...${NC}"
chown -R $NGINX_USER:$NGINX_USER $APP_PATH
chmod -R 755 $APP_PATH/storage
chmod -R 755 $APP_PATH/bootstrap/cache
print_status "Permissões configuradas"

# 8. Run database migrations
echo -e "${BLUE}🗄️  Executando migrações...${NC}"
sudo -u $NGINX_USER php artisan migrate --force
print_status "Migrações executadas"

# 9. Optimize Laravel
echo -e "${BLUE}⚡ Otimizando Laravel...${NC}"
sudo -u $NGINX_USER php artisan config:cache
sudo -u $NGINX_USER php artisan route:cache
sudo -u $NGINX_USER php artisan view:cache
print_status "Laravel otimizado"

# 10. Configure Nginx for Laravel
echo -e "${BLUE}🌐 Configurando Nginx para Laravel...${NC}"
cat > /etc/nginx/sites-available/$APP_NAME << EOF
server {
    listen 80;
    listen [::]:80;
    
    server_name monitor-rio.piracicaba www.monitor-rio.piracicaba;
    root $APP_PATH/public;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/javascript application/json;

    # Main location block
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        
        # Security
        fastcgi_param PHP_VALUE "expose_php=off";
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~* \.(env|log|htaccess|htpasswd|ini|log|sh|sql|conf)$ {
        deny all;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \$uri =404;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }

    # Error pages
    error_page 404 /index.php;
    error_page 500 502 503 504 /50x.html;
    
    location = /50x.html {
        root /usr/share/nginx/html;
    }
}
EOF

# Enable site and disable default
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
nginx -t
if [ $? -eq 0 ]; then
    print_status "Configuração do Nginx para Laravel criada"
else
    echo -e "${RED}❌ Erro na configuração do Nginx${NC}"
    exit 1
fi

# Restart Nginx
systemctl restart nginx
print_status "Nginx reiniciado"

# 11. Update hosts file
echo -e "${BLUE}📝 Atualizando arquivo hosts...${NC}"
echo "127.0.0.1 monitor-rio.piracicaba www.monitor-rio.piracicaba" >> /etc/hosts
print_status "Arquivo hosts atualizado"

# 12. Test application
echo -e "${BLUE}🧪 Testando aplicação...${NC}"
sleep 5
if curl -f -s http://monitor-rio.piracicaba/health > /dev/null; then
    print_status "Aplicação respondendo corretamente"
else
    echo -e "${YELLOW}⚠️  Aplicação pode não estar respondendo corretamente${NC}"
fi

echo -e "${GREEN}🎉 Laravel configurado com sucesso!${NC}"
echo ""
echo -e "${YELLOW}📋 Informações importantes:${NC}"
echo "• Site configurado para: http://monitor-rio.piracicaba"
echo "• Diretório da aplicação: $APP_PATH"
echo "• Logs do Nginx: /var/log/nginx/"
echo "• Logs do Laravel: $APP_PATH/storage/logs/"
echo ""
echo -e "${YELLOW}🔧 Comandos úteis:${NC}"
echo "• Status do Nginx: systemctl status nginx"
echo "• Logs do Nginx: tail -f /var/log/nginx/error.log"
echo "• Teste o site: curl http://monitor-rio.piracicaba"
echo ""
echo -e "${GREEN}🎉 Sistema funcionando em produção!${NC}"

