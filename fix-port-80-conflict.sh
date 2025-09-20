#!/bin/bash

# =============================================================================
# RESOLVER CONFLITO DE PORTA 80 - MONITOR RIO
# Execute este script como root: sudo ./fix-port-80-conflict.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🔧 Resolvendo Conflito de Porta 80 - Monitor Rio${NC}"
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

# 1. Check what's using port 80
echo -e "${BLUE}🔍 Verificando o que está usando a porta 80...${NC}"
PORT_80_PROCESSES=$(lsof -i :80 2>/dev/null || true)
if [ -n "$PORT_80_PROCESSES" ]; then
    echo "Processos usando a porta 80:"
    echo "$PORT_80_PROCESSES"
else
    echo "Nenhum processo encontrado usando a porta 80"
fi

# 2. Check for Apache
echo -e "${BLUE}🔍 Verificando Apache...${NC}"
if systemctl is-active apache2 >/dev/null 2>&1; then
    echo "Apache2 está rodando. Parando..."
    systemctl stop apache2
    systemctl disable apache2
    print_status "Apache2 parado e desabilitado"
else
    echo "Apache2 não está rodando"
fi

# 3. Check for other web servers
echo -e "${BLUE}🔍 Verificando outros servidores web...${NC}"
for service in lighttpd httpd tomcat8 tomcat9; do
    if systemctl is-active $service >/dev/null 2>&1; then
        echo "$service está rodando. Parando..."
        systemctl stop $service
        systemctl disable $service
        print_status "$service parado e desabilitado"
    fi
done

# 4. Check for Docker containers
echo -e "${BLUE}🔍 Verificando containers Docker...${NC}"
DOCKER_CONTAINERS=$(docker ps --format "table {{.Names}}\t{{.Ports}}" | grep ":80" || true)
if [ -n "$DOCKER_CONTAINERS" ]; then
    echo "Containers Docker usando porta 80:"
    echo "$DOCKER_CONTAINERS"
    echo "Parando containers Docker..."
    docker stop $(docker ps -q) 2>/dev/null || true
    print_status "Containers Docker parados"
else
    echo "Nenhum container Docker usando porta 80"
fi

# 5. Kill any remaining processes on port 80
echo -e "${BLUE}🔪 Finalizando processos na porta 80...${NC}"
PIDS=$(lsof -t -i:80 2>/dev/null || true)
if [ -n "$PIDS" ]; then
    echo "Finalizando PIDs: $PIDS"
    kill -9 $PIDS 2>/dev/null || true
    sleep 2
    print_status "Processos finalizados"
else
    echo "Nenhum processo encontrado na porta 80"
fi

# 6. Verify port 80 is free
echo -e "${BLUE}🔍 Verificando se a porta 80 está livre...${NC}"
if lsof -i :80 >/dev/null 2>&1; then
    print_error "Porta 80 ainda está em uso"
    exit 1
else
    print_status "Porta 80 está livre"
fi

# 7. Stop Nginx if running
echo -e "${BLUE}🛑 Parando Nginx...${NC}"
systemctl stop nginx || true

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
echo "<h1>Nginx funcionando!</h1><p>Porta 80 liberada com sucesso!</p>" > /var/www/html/index.html
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
echo -e "${GREEN}🎉 Conflito de porta 80 resolvido!${NC}"
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
echo -e "${BLUE}Execute: sudo ./configure-laravel-after-nginx.sh${NC}"

