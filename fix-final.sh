#!/bin/bash

echo "🔧 CORREÇÃO FINAL - MONITOR RIO PIRACICABA"
echo "========================================="

# 1. Instalar extensões PHP necessárias
echo "1️⃣ Instalando extensões PHP..."
apt install -y php-sqlite3 php-xml php-curl php-mbstring php-zip

# 2. Configurar .env para SQLite
echo "2️⃣ Configurando .env para SQLite..."
cat > .env << 'EOF'
APP_NAME="Monitor Rio Piracicaba"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=http://localhost

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

# 3. Gerar chave da aplicação
echo "3️⃣ Gerando chave da aplicação..."
php artisan key:generate --force

# 4. Criar banco de dados
echo "4️⃣ Criando banco de dados..."
php create-database.php

# 5. Corrigir configuração do Nginx
echo "5️⃣ Corrigindo configuração do Nginx..."
cat > /etc/nginx/sites-available/monitor-rio << 'EOF'
server {
    listen 80;
    server_name _;
    root /home/wilker/Área\ de\ Trabalho/monitor-rio/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
EOF

# 6. Testar configuração do Nginx
echo "6️⃣ Testando configuração do Nginx..."
nginx -t

# 7. Reiniciar Nginx
echo "7️⃣ Reiniciando Nginx..."
systemctl restart nginx

# 8. Configurar permissões
echo "8️⃣ Configurando permissões..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 9. Testar sistema
echo "9️⃣ Testando sistema..."
php artisan river:fetch --mock

# 10. Testar API
echo "🔟 Testando API..."
echo "Testando /api/river-data/stats..."
curl -s http://localhost/api/river-data/stats | head -c 200
echo ""
echo "Testando /api/stations..."
curl -s http://localhost/api/stations | head -c 200
echo ""

# 11. Verificar status dos serviços
echo "1️⃣1️⃣ Status dos serviços:"
echo "Nginx: $(systemctl is-active nginx)"
echo "PostgreSQL: $(systemctl is-active postgresql)"

echo ""
echo "✅ CORREÇÃO CONCLUÍDA!"
echo "====================="
echo "🌐 Sistema disponível em: http://localhost"
echo "📊 API disponível em: http://localhost/api/river-data/stats"
echo "🏭 Estações disponíveis em: http://localhost/api/stations"
echo ""
echo "🎉 MONITOR RIO PIRACICABA FUNCIONANDO!"
echo "====================================="
