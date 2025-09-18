#!/bin/bash

echo "⚙️ CONFIGURANDO SISTEMA PARA PRODUÇÃO"
echo "===================================="

# Configurar arquivo .env para produção
echo "🔧 Configurando arquivo .env..."
cat > .env << 'EOF'
APP_NAME="Monitor Rio Piracicaba"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://monitor-rio-piracicaba.com

APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=info

# Configuração PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=monitor_rio
DB_USERNAME=monitor_rio
DB_PASSWORD=monitor_rio_2025

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=monitor-rio-piracicaba.com

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database

CACHE_STORE=redis
CACHE_PREFIX=monitor_rio_

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=monitor.rio.piracicaba@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="monitor.rio.piracicaba@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"

# Configurações da API da ANA
ANA_API_BASE_URL=https://www.ana.gov.br/hidrowebservice
ANA_API_TIMEOUT=30
ANA_API_RETRY_ATTEMPTS=3
ANA_API_RETRY_DELAY=1000

# Estações do Rio Piracicaba no Vale do Aço
PIRACICABA_STATIONS=12345678,87654321,11223344
EOF

# Gerar chave da aplicação
echo "🔑 Gerando chave da aplicação..."
php artisan key:generate --force

# Executar migrações
echo "🗄️ Executando migrações..."
php artisan migrate --force

# Executar seeders
echo "🌱 Executando seeders..."
php artisan db:seed --force

# Instalar dependências do frontend
echo "📦 Instalando dependências do frontend..."
npm install

# Build do frontend
echo "🏗️ Fazendo build do frontend..."
npm run build

# Configurar permissões
echo "🔐 Configurando permissões..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Configurar Nginx
echo "🌐 Configurando Nginx..."
cat > /etc/nginx/sites-available/monitor-rio << 'EOF'
server {
    listen 80;
    server_name monitor-rio-piracicaba.com www.monitor-rio-piracicaba.com;
    root /home/wilker/Área de Trabalho/monitor-rio/public;
    index index.php index.html;

    # Configuração para Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Configuração para PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Configuração para arquivos estáticos
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Configuração de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Logs
    access_log /var/log/nginx/monitor-rio.access.log;
    error_log /var/log/nginx/monitor-rio.error.log;
}
EOF

# Habilitar site
ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Testar configuração do Nginx
nginx -t

# Reiniciar serviços
systemctl restart nginx
systemctl restart postgresql

echo "✅ Sistema configurado para produção!"
echo "💡 Próximo passo: ./setup-ssl.sh"
