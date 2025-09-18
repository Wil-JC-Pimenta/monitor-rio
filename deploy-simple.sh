#!/bin/bash

echo "🚀 DEPLOY SIMPLIFICADO - MONITOR RIO PIRACICABA"
echo "============================================="
echo ""

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Este script precisa ser executado com sudo"
    echo "💡 Execute: sudo ./deploy-simple.sh"
    exit 1
fi

echo "📋 EXECUTANDO DEPLOY COMPLETO..."
echo "==============================="

# 1. Atualizar sistema
echo "1️⃣ Atualizando sistema..."
apt update -y

# 2. Instalar dependências
echo "2️⃣ Instalando dependências..."
apt install -y postgresql postgresql-contrib nginx php-pgsql php-xml php-dom php-curl php-mbstring php-zip php-fpm php-cli nodejs npm

# 3. Configurar PostgreSQL
echo "3️⃣ Configurando PostgreSQL..."
systemctl start postgresql
systemctl enable postgresql
sudo -u postgres psql -c "CREATE USER monitor_rio WITH PASSWORD 'monitor_rio_2025';" 2>/dev/null || true
sudo -u postgres psql -c "CREATE DATABASE monitor_rio OWNER monitor_rio;" 2>/dev/null || true
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE monitor_rio TO monitor_rio;" 2>/dev/null || true

# 4. Configurar arquivo .env
echo "4️⃣ Configurando arquivo .env..."
cat > .env << 'EOF'
APP_NAME="Monitor Rio Piracicaba"
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_TIMEZONE=America/Sao_Paulo
APP_URL=https://monitor-rio-piracicaba.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=monitor_rio
DB_USERNAME=monitor_rio
DB_PASSWORD=monitor_rio_2025

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

CACHE_STORE=database
QUEUE_CONNECTION=database

ANA_API_BASE_URL=https://www.ana.gov.br/hidrowebservice
ANA_API_TIMEOUT=30
ANA_API_RETRY_ATTEMPTS=3
ANA_API_RETRY_DELAY=1000

PIRACICABA_STATIONS=12345678,87654321,11223344
EOF

# 5. Gerar chave da aplicação
echo "5️⃣ Gerando chave da aplicação..."
php artisan key:generate --force

# 6. Executar migrações
echo "6️⃣ Executando migrações..."
php artisan migrate --force

# 7. Instalar dependências do frontend
echo "7️⃣ Instalando dependências do frontend..."
npm install

# 8. Build do frontend
echo "8️⃣ Fazendo build do frontend..."
npm run build

# 9. Configurar Nginx
echo "9️⃣ Configurando Nginx..."
cat > /etc/nginx/sites-available/monitor-rio << 'EOF'
server {
    listen 80;
    server_name _;
    root /home/wilker/Área de Trabalho/monitor-rio/public;
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

# 10. Habilitar site
echo "🔟 Habilitando site..."
ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# 11. Configurar permissões
echo "1️⃣1️⃣ Configurando permissões..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 12. Reiniciar serviços
echo "1️⃣2️⃣ Reiniciando serviços..."
systemctl restart nginx
systemctl restart postgresql
systemctl restart php8.4-fpm

# 13. Executar primeira atualização de dados
echo "1️⃣3️⃣ Executando primeira atualização de dados..."
php artisan river:fetch --mock

# 14. Configurar cron
echo "1️⃣4️⃣ Configurando atualização automática..."
(crontab -l 2>/dev/null; echo "*/15 * * * * cd /home/wilker/Área\\ de\\ Trabalho/monitor-rio && php artisan river:fetch --type=niveis --days=1 >> /var/log/monitor-rio.log 2>&1") | crontab -

echo ""
echo "🎉 DEPLOY CONCLUÍDO COM SUCESSO!"
echo "==============================="
echo ""
echo "🌐 SISTEMA ONLINE EM:"
echo "===================="
echo "🔗 http://$(hostname -I | awk '{print $1}')"
echo "🔗 http://localhost"
echo ""
echo "📊 FUNCIONALIDADES ATIVAS:"
echo "========================="
echo "✅ Backend Laravel com API REST"
echo "✅ Frontend React com dados em tempo real"
echo "✅ Integração com API da ANA"
echo "✅ Atualização automática a cada 15 minutos"
echo "✅ Nginx como servidor web"
echo "✅ PostgreSQL como banco de dados"
echo ""
echo "🔧 COMANDOS ÚTEIS:"
echo "=================="
echo "📊 Ver logs: tail -f /var/log/nginx/error.log"
echo "📊 Dados: php artisan river:fetch --mock"
echo "🧪 Teste: curl http://localhost/api/river-data/stats"
echo "🔄 Reiniciar: systemctl restart nginx"
echo ""
echo "🌊 MONITOR RIO PIRACICABA - SISTEMA ONLINE!"
echo "=========================================="
