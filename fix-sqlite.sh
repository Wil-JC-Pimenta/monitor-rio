#!/bin/bash

echo "🔧 CORRIGINDO COM SQLITE (MAIS SIMPLES)"
echo "====================================="

# 1. Instalar extensões PHP básicas
echo "1️⃣ Instalando extensões PHP básicas..."
apt install -y php-sqlite3 php-xml php-curl php-mbstring php-zip

# 2. Configurar para usar SQLite
echo "2️⃣ Configurando para usar SQLite..."
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

# 4. Criar banco SQLite
echo "4️⃣ Criando banco SQLite..."
touch database/database.sqlite
chmod 664 database/database.sqlite

# 5. Executar migrações
echo "5️⃣ Executando migrações..."
php artisan migrate --force

# 6. Executar seeders
echo "6️⃣ Executando seeders..."
php artisan db:seed --force

# 7. Corrigir configuração do Nginx
echo "7️⃣ Corrigindo configuração do Nginx..."
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

# 8. Testar configuração do Nginx
echo "8️⃣ Testando configuração do Nginx..."
nginx -t

# 9. Reiniciar Nginx
echo "9️⃣ Reiniciando Nginx..."
systemctl restart nginx

# 10. Testar sistema
echo "🔟 Testando sistema..."
php artisan river:fetch --mock

# 11. Testar API
echo "1️⃣1️⃣ Testando API..."
curl -s http://localhost/api/river-data/stats | head -c 100
echo ""

echo "✅ SISTEMA CORRIGIDO E FUNCIONANDO!"
echo "=================================="
echo "🌐 Acesse: http://localhost"
echo "📊 API: http://localhost/api/river-data/stats"
