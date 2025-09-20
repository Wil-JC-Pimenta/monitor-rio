#!/bin/bash

# =============================================================================
# CONFIGURAÇÃO DO NGINX PARA PRODUÇÃO - MONITOR RIO
# Execute este script como root: sudo ./configure-nginx-production.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration variables
DOMAIN="monitor-rio.piracicaba"  # Change this to your domain
APP_NAME="monitor-rio"
APP_PATH="/var/www/$APP_NAME"
NGINX_USER="www-data"

echo -e "${BLUE}🌐 Configurando Nginx para Monitor Rio - Produção${NC}"
echo "=============================================================================="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Create application directory if it doesn't exist
echo -e "${BLUE}📁 Criando diretório da aplicação...${NC}"
mkdir -p $APP_PATH
chown $NGINX_USER:$NGINX_USER $APP_PATH
print_status "Diretório da aplicação criado: $APP_PATH"

# Copy application files
echo -e "${BLUE}📥 Copiando arquivos da aplicação...${NC}"
cp -r . $APP_PATH/
chown -R $NGINX_USER:$NGINX_USER $APP_PATH
print_status "Arquivos copiados"

# Create Nginx configuration
echo -e "${BLUE}📝 Criando configuração do Nginx...${NC}"
cat > /etc/nginx/sites-available/$APP_NAME << EOF
server {
    listen 80;
    listen [::]:80;
    
    server_name $DOMAIN www.$DOMAIN;
    root $APP_PATH/public;
    index index.php index.html index.htm;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

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

    # Robots.txt
    location = /robots.txt {
        access_log off;
        log_not_found off;
    }

    # Favicon
    location = /favicon.ico {
        access_log off;
        log_not_found off;
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
echo -e "${BLUE}🔗 Ativando site e desativando default...${NC}"
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
print_status "Site ativado e default desabilitado"

# Test Nginx configuration
echo -e "${BLUE}🧪 Testando configuração do Nginx...${NC}"
nginx -t
if [ $? -eq 0 ]; then
    print_status "Configuração do Nginx válida"
else
    echo -e "${RED}❌ Erro na configuração do Nginx${NC}"
    exit 1
fi

# Restart Nginx
echo -e "${BLUE}🔄 Reiniciando Nginx...${NC}"
systemctl restart nginx
systemctl enable nginx
print_status "Nginx reiniciado e habilitado"

# Update hosts file for local testing
if [ "$DOMAIN" = "monitor-rio.piracicaba" ]; then
    echo -e "${BLUE}📝 Atualizando arquivo hosts para teste local...${NC}"
    echo "127.0.0.1 $DOMAIN www.$DOMAIN" >> /etc/hosts
    print_status "Arquivo hosts atualizado"
fi

echo -e "${GREEN}🎉 Configuração do Nginx concluída!${NC}"
echo ""
echo -e "${YELLOW}📋 Informações importantes:${NC}"
echo "• Site configurado para: http://$DOMAIN"
echo "• Diretório da aplicação: $APP_PATH"
echo "• Logs do Nginx: /var/log/nginx/"
echo ""
echo -e "${YELLOW}🔧 Próximos passos:${NC}"
echo "1. Configure PHP-FPM: sudo systemctl restart php8.1-fpm"
echo "2. Configure SSL: sudo ./scripts/configure-ssl.sh"
echo "3. Teste o site: curl http://$DOMAIN"

