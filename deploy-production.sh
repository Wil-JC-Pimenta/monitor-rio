#!/bin/bash

# =============================================
# 🚀 DEPLOY PRODUCTION - MONITOR RIO PIRACICABA
# =============================================
# Sistema de Monitoramento Hidrológico
# Versão: 2.0.0
# Licença: MIT

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

info() {
    echo -e "${CYAN}ℹ️  $1${NC}"
}

# Banner
echo -e "${PURPLE}"
echo "============================================="
echo "🌊 MONITOR RIO PIRACICABA - DEPLOY PRODUCTION"
echo "============================================="
echo "Sistema de Monitoramento Hidrológico"
echo "Versão: 2.0.0 | Licença: MIT"
echo "============================================="
echo -e "${NC}"

# Verificar se está rodando como root
if [[ $EUID -eq 0 ]]; then
   error "Este script não deve ser executado como root"
   exit 1
fi

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    error "Execute este script no diretório raiz do projeto Laravel"
    exit 1
fi

log "Iniciando deploy de produção..."

# 1. Atualizar sistema
log "1️⃣ Atualizando sistema..."
sudo apt update -y
sudo apt upgrade -y

# 2. Instalar dependências do sistema
log "2️⃣ Instalando dependências do sistema..."
sudo apt install -y \
    nginx \
    php8.4-fpm \
    php8.4-cli \
    php8.4-mysql \
    php8.4-pgsql \
    php8.4-sqlite3 \
    php8.4-xml \
    php8.4-curl \
    php8.4-zip \
    php8.4-mbstring \
    php8.4-gd \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-redis \
    redis-server \
    supervisor \
    cron \
    curl \
    wget \
    unzip \
    git

# 3. Configurar PHP
log "3️⃣ Configurando PHP..."
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 100M/' /etc/php/8.4/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 100M/' /etc/php/8.4/fpm/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.4/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.4/fpm/php.ini

# 4. Configurar Nginx
log "4️⃣ Configurando Nginx..."
sudo tee /etc/nginx/sites-available/monitor-rio > /dev/null <<EOF
server {
    listen 80;
    server_name _;
    root $(pwd)/public;
    index index.php index.html;

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
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Main location
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Security
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
}
EOF

# Ativar site
sudo ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Testar configuração do Nginx
sudo nginx -t

# 5. Configurar Laravel
log "5️⃣ Configurando Laravel..."

# Instalar dependências
composer install --optimize-autoloader --no-dev

# Configurar .env para produção
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Configurar .env
sed -i 's/APP_ENV=local/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/' .env
sed -i 's/DB_DATABASE=laravel/DB_DATABASE=\/tmp\/monitor-rio\.sqlite/' .env

# Gerar chave da aplicação
php artisan key:generate --force

# Configurar cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 6. Configurar banco de dados
log "6️⃣ Configurando banco de dados..."
php artisan migrate --force

# 7. Configurar permissões
log "7️⃣ Configurando permissões..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 8. Configurar Supervisor para tarefas
log "8️⃣ Configurando Supervisor..."
sudo tee /etc/supervisor/conf.d/monitor-rio.conf > /dev/null <<EOF
[program:monitor-rio-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $(pwd)/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=$(pwd)/storage/logs/worker.log
stopwaitsecs=3600
EOF

# 9. Configurar Cron
log "9️⃣ Configurando Cron..."
(crontab -l 2>/dev/null; echo "* * * * * cd $(pwd) && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# 10. Iniciar serviços
log "🔟 Iniciando serviços..."
sudo systemctl enable nginx php8.4-fpm redis-server supervisor
sudo systemctl restart nginx php8.4-fpm redis-server supervisor

# 11. Executar comandos iniciais
log "1️⃣1️⃣ Executando comandos iniciais..."
php artisan ana:fetch --hours=24

# 12. Testar sistema
log "1️⃣2️⃣ Testando sistema..."
sleep 5

# Testar se o servidor está respondendo
if curl -s http://localhost > /dev/null; then
    success "Sistema está funcionando!"
else
    warning "Sistema pode não estar funcionando corretamente"
fi

# 13. Mostrar informações finais
echo -e "${GREEN}"
echo "============================================="
echo "🎉 DEPLOY CONCLUÍDO COM SUCESSO!"
echo "============================================="
echo -e "${NC}"

info "URLs disponíveis:"
echo "  🌐 Aplicação: http://localhost"
echo "  📊 Dashboard: http://localhost"
echo "  📡 Estações: http://localhost/stations"
echo "  📈 Dados: http://localhost/data"
echo "  📊 Análises: http://localhost/analytics"
echo "  🚨 Alertas: http://localhost/alerts"

info "Comandos úteis:"
echo "  📥 Buscar dados ANA: php artisan ana:fetch"
echo "  🔄 Limpar cache: php artisan cache:clear"
echo "  📊 Status: php artisan about"
echo "  📝 Logs: tail -f storage/logs/laravel.log"

info "Arquivos importantes:"
echo "  ⚙️  Configuração: .env"
echo "  📝 Logs: storage/logs/"
echo "  🗄️  Banco: /tmp/monitor-rio.sqlite"
echo "  🌐 Nginx: /etc/nginx/sites-available/monitor-rio"

success "Sistema pronto para uso em produção! 🚀"

