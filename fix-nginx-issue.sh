#!/bin/bash

# =============================================================================
# CORRIGIR PROBLEMA DO NGINX - MONITOR RIO
# Execute este script como root: sudo ./fix-nginx-issue.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Corrigindo Problema do Nginx - Monitor Rio${NC}"
echo "=============================================================================="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

# 1. Check Nginx status
echo -e "${BLUE}🔍 Verificando status do Nginx...${NC}"
systemctl status nginx || true

# 2. Check Nginx logs
echo -e "${BLUE}📝 Verificando logs do Nginx...${NC}"
journalctl -xeu nginx.service --no-pager -n 20 || true

# 3. Check if port 80 is in use
echo -e "${BLUE}🔍 Verificando se a porta 80 está em uso...${NC}"
netstat -tlnp | grep :80 || echo "Porta 80 não está em uso"

# 4. Check Nginx configuration
echo -e "${BLUE}🧪 Testando configuração do Nginx...${NC}"
nginx -t

# 5. Stop Nginx if running
echo -e "${BLUE}🛑 Parando Nginx...${NC}"
systemctl stop nginx || true

# 6. Kill any Nginx processes
echo -e "${BLUE}🔪 Finalizando processos do Nginx...${NC}"
pkill -f nginx || true
sleep 2

# 7. Check for conflicting services
echo -e "${BLUE}🔍 Verificando serviços conflitantes...${NC}"
systemctl status apache2 || echo "Apache2 não está rodando"
systemctl status lighttpd || echo "Lighttpd não está rodando"

# 8. Create minimal Nginx configuration
echo -e "${BLUE}📝 Criando configuração mínima do Nginx...${NC}"
cat > /etc/nginx/nginx.conf << 'EOF'
user www-data;
worker_processes auto;
pid /run/nginx.pid;
include /etc/nginx/modules-enabled/*.conf;

events {
    worker_connections 1024;
}

http {
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;

    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
EOF

# 9. Create basic site configuration
echo -e "${BLUE}📝 Criando configuração básica do site...${NC}"
cat > /etc/nginx/sites-available/default << 'EOF'
server {
    listen 80 default_server;
    listen [::]:80 default_server;
    
    root /var/www/html;
    index index.html index.htm index.nginx-debian.html;
    
    server_name _;
    
    location / {
        try_files $uri $uri/ =404;
    }
}
EOF

# 10. Enable default site
echo -e "${BLUE}🔗 Habilitando site padrão...${NC}"
ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# 11. Create default index page
echo -e "${BLUE}📄 Criando página padrão...${NC}"
mkdir -p /var/www/html
echo "<h1>Nginx funcionando!</h1>" > /var/www/html/index.html
chown www-data:www-data /var/www/html/index.html

# 12. Test Nginx configuration
echo -e "${BLUE}🧪 Testando configuração do Nginx...${NC}"
nginx -t
if [ $? -eq 0 ]; then
    print_status "Configuração do Nginx válida"
else
    print_error "Erro na configuração do Nginx"
    exit 1
fi

# 13. Start Nginx
echo -e "${BLUE}🚀 Iniciando Nginx...${NC}"
systemctl start nginx
if [ $? -eq 0 ]; then
    print_status "Nginx iniciado com sucesso"
else
    print_error "Falha ao iniciar Nginx"
    exit 1
fi

# 14. Check Nginx status
echo -e "${BLUE}🔍 Verificando status do Nginx...${NC}"
systemctl status nginx --no-pager

# 15. Test Nginx response
echo -e "${BLUE}🧪 Testando resposta do Nginx...${NC}"
sleep 3
if curl -f -s http://localhost > /dev/null; then
    print_status "Nginx respondendo corretamente"
else
    print_error "Nginx não está respondendo"
fi

# 16. Show final information
echo -e "${GREEN}🎉 Nginx corrigido com sucesso!${NC}"
echo ""
echo -e "${YELLOW}📋 Informações importantes:${NC}"
echo "• Nginx rodando na porta 80"
echo "• Página padrão: http://localhost"
echo "• Logs: /var/log/nginx/"
echo ""
echo -e "${YELLOW}🔧 Comandos úteis:${NC}"
echo "• Status: systemctl status nginx"
echo "• Logs: tail -f /var/log/nginx/error.log"
echo "• Reiniciar: systemctl restart nginx"
echo ""
echo -e "${GREEN}🎉 Agora você pode configurar o Laravel!${NC}"

