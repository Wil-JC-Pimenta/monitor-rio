#!/bin/bash

echo "🔧 CORRIGINDO ERROS DO DEPLOY"
echo "============================"

# 1. Instalar extensões PHP corretas
echo "1️⃣ Instalando extensões PHP corretas..."
apt install -y php8.4-xml php8.4-pgsql php8.4-fpm

# 2. Verificar versão do PHP
echo "2️⃣ Verificando versão do PHP..."
php -v

# 3. Corrigir configuração do Nginx
echo "3️⃣ Corrigindo configuração do Nginx..."
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

# 4. Testar configuração do Nginx
echo "4️⃣ Testando configuração do Nginx..."
nginx -t

# 5. Reiniciar serviços
echo "5️⃣ Reiniciando serviços..."
systemctl restart nginx
systemctl restart php8.4-fpm
systemctl restart postgresql

# 6. Verificar status dos serviços
echo "6️⃣ Verificando status dos serviços..."
echo "Nginx: $(systemctl is-active nginx)"
echo "PostgreSQL: $(systemctl is-active postgresql)"
echo "PHP-FPM: $(systemctl is-active php8.4-fpm)"

# 7. Testar conexão com banco
echo "7️⃣ Testando conexão com banco..."
php -r "
try {
    \$pdo = new PDO('pgsql:host=127.0.0.1;port=5432;dbname=monitor_rio', 'monitor_rio', 'monitor_rio_2025');
    echo '✅ Banco conectado com sucesso\n';
} catch (Exception \$e) {
    echo '❌ Erro ao conectar banco: ' . \$e->getMessage() . '\n';
}
"

# 8. Executar migrações
echo "8️⃣ Executando migrações..."
php artisan migrate --force

# 9. Executar comando de dados mock
echo "9️⃣ Executando dados mock..."
php artisan river:fetch --mock

# 10. Testar API
echo "🔟 Testando API..."
curl -s http://localhost/api/river-data/stats | head -c 100
echo ""

echo "✅ CORREÇÕES APLICADAS!"
echo "======================"
echo "🌐 Sistema disponível em: http://localhost"
echo "📊 API disponível em: http://localhost/api/river-data/stats"
