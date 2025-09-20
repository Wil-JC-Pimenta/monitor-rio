#!/bin/bash

echo "🔧 CORRIGINDO CONFLITO APACHE/NGINX"
echo "=================================="

# 1. Parar Apache
echo "1️⃣ Parando Apache..."
systemctl stop apache2
systemctl disable apache2

# 2. Verificar se Nginx está rodando
echo "2️⃣ Verificando Nginx..."
systemctl status nginx

# 3. Se Nginx não estiver rodando, iniciar
echo "3️⃣ Iniciando Nginx..."
systemctl start nginx
systemctl enable nginx

# 4. Verificar configuração do Nginx
echo "4️⃣ Verificando configuração do Nginx..."
nginx -t

# 5. Se houver erro, corrigir configuração
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

# 6. Habilitar site
echo "6️⃣ Habilitando site..."
ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# 7. Testar configuração
echo "7️⃣ Testando configuração..."
nginx -t

# 8. Reiniciar Nginx
echo "8️⃣ Reiniciando Nginx..."
systemctl restart nginx

# 9. Verificar status
echo "9️⃣ Verificando status..."
echo "Apache: $(systemctl is-active apache2)"
echo "Nginx: $(systemctl is-active nginx)"

# 10. Testar acesso
echo "🔟 Testando acesso..."
curl -s http://localhost | head -c 200
echo ""

echo "✅ CONFLITO RESOLVIDO!"
echo "====================="
echo "🌐 Sistema disponível em: http://localhost"
echo "📊 API disponível em: http://localhost/api/river-data/stats"
