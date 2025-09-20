#!/bin/bash

# Script de Deploy para VPS sem Domínio
# Monitor Rio Piracicaba

set -e

echo "🌊 Deploy Monitor Rio Piracicaba - VPS sem Domínio"
echo "=================================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir com cores
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar se é root
if [ "$EUID" -eq 0 ]; then
    print_error "Não execute como root! Use: sudo ./deploy-vps-no-domain.sh"
    exit 1
fi

# Obter IP público
print_status "Obtendo IP público do servidor..."
PUBLIC_IP=$(curl -s ifconfig.me)
print_success "IP público: $PUBLIC_IP"

# Atualizar sistema
print_status "Atualizando sistema..."
sudo apt update && sudo apt upgrade -y

# Instalar dependências
print_status "Instalando dependências..."
sudo apt install -y nginx php8.1-fpm php8.1-sqlite3 php8.1-mbstring php8.1-xml php8.1-curl php8.1-zip php8.1-gd php8.1-intl php8.1-bcmath php8.1-cli php8.1-common php8.1-mysql php8.1-pgsql php8.1-readline php8.1-tokenizer php8.1-xmlwriter php8.1-xsl sqlite3 curl wget unzip git

# Instalar Composer
print_status "Instalando Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    sudo chmod +x /usr/local/bin/composer
fi

# Instalar Node.js (opcional para assets)
print_status "Instalando Node.js..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt-get install -y nodejs
fi

# Criar diretório do projeto
PROJECT_DIR="/var/www/monitor-rio"
print_status "Criando diretório do projeto: $PROJECT_DIR"
sudo mkdir -p $PROJECT_DIR
sudo chown -R $USER:$USER $PROJECT_DIR

# Clone do repositório
print_status "Clonando repositório..."
if [ -d "$PROJECT_DIR/.git" ]; then
    print_warning "Repositório já existe. Atualizando..."
    cd $PROJECT_DIR
    git pull origin main
else
    git clone https://github.com/Wil-JC-Pimenta/monitor-rio.git $PROJECT_DIR
    cd $PROJECT_DIR
fi

# Instalar dependências PHP
print_status "Instalando dependências PHP..."
composer install --no-dev --optimize-autoloader

# Instalar dependências Node.js
print_status "Instalando dependências Node.js..."
npm install
npm run build

# Configurar Laravel
print_status "Configurando Laravel..."
cp .env.example .env
php artisan key:generate

# Configurar banco de dados
print_status "Configurando banco de dados..."
touch database/database.sqlite
php artisan migrate
php artisan data:generate --days=30

# Configurar permissões
print_status "Configurando permissões..."
sudo chown -R www-data:www-data $PROJECT_DIR
sudo chmod -R 755 $PROJECT_DIR
sudo chmod -R 775 $PROJECT_DIR/storage
sudo chmod -R 775 $PROJECT_DIR/bootstrap/cache

# Configurar Nginx
print_status "Configurando Nginx..."
sudo tee /etc/nginx/sites-available/monitor-rio > /dev/null <<EOF
server {
    listen 80;
    server_name $PUBLIC_IP;
    
    root $PROJECT_DIR/public;
    index index.php index.html;
    
    # Logs
    access_log /var/log/nginx/monitor-rio.access.log;
    error_log /var/log/nginx/monitor-rio.error.log;
    
    # Configurações de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # Configurações de performance
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;
    
    # Laravel
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        
        # Configurações de timeout
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;
    }
    
    # Arquivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # Denegar acesso a arquivos sensíveis
    location ~ /\. {
        deny all;
    }
    
    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
}
EOF

# Habilitar site
print_status "Habilitando site no Nginx..."
sudo ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Testar configuração do Nginx
print_status "Testando configuração do Nginx..."
sudo nginx -t

# Reiniciar serviços
print_status "Reiniciando serviços..."
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
sudo systemctl enable nginx
sudo systemctl enable php8.1-fpm

# Configurar cron para coleta automática
print_status "Configurando cron para coleta automática..."
(crontab -l 2>/dev/null; echo "0 * * * * cd $PROJECT_DIR && php artisan river:fetch >> /var/log/monitor-rio-cron.log 2>&1") | crontab -

# Configurar logrotate
print_status "Configurando logrotate..."
sudo tee /etc/logrotate.d/monitor-rio > /dev/null <<EOF
/var/log/monitor-rio-cron.log {
    daily
    missingok
    rotate 7
    compress
    notifempty
    create 644 www-data www-data
}
EOF

# Configurar firewall (opcional)
print_status "Configurando firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Testar aplicação
print_status "Testando aplicação..."
sleep 5
if curl -s -o /dev/null -w "%{http_code}" http://$PUBLIC_IP | grep -q "200"; then
    print_success "Aplicação funcionando corretamente!"
else
    print_warning "Aplicação pode não estar funcionando. Verifique os logs."
fi

# Informações finais
echo ""
echo "🎉 Deploy concluído com sucesso!"
echo "=================================="
echo "🌐 URL: http://$PUBLIC_IP"
echo "📁 Diretório: $PROJECT_DIR"
echo "📋 Logs: /var/log/nginx/monitor-rio.*.log"
echo "⏰ Cron: Configurado para coleta a cada hora"
echo ""
echo "🔧 Comandos úteis:"
echo "  sudo systemctl status nginx"
echo "  sudo systemctl status php8.1-fpm"
echo "  sudo tail -f /var/log/nginx/monitor-rio.error.log"
echo "  cd $PROJECT_DIR && php artisan tinker"
echo ""
echo "📝 Para configurar domínio gratuito:"
echo "  1. Acesse noip.com ou duckdns.org"
echo "  2. Crie um hostname gratuito"
echo "  3. Configure o DNS dinâmico"
echo "  4. Atualize o server_name no Nginx"
echo "  5. Configure SSL com Let's Encrypt"
echo ""
print_success "Deploy finalizado! Acesse: http://$PUBLIC_IP"
