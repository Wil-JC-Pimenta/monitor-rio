#!/bin/bash

echo "🚀 Deploy do Monitor Rio Piracicaba com Nginx"
echo "============================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Função para imprimir com cores
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar se está rodando como root
if [ "$EUID" -ne 0 ]; then
    print_error "Execute como root: sudo ./deploy-nginx.sh"
    exit 1
fi

print_status "Atualizando sistema..."
apt update && apt upgrade -y

print_status "Instalando dependências..."
apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-sqlite3 composer git unzip

print_status "Configurando PHP-FPM..."
systemctl enable php8.2-fpm
systemctl start php8.2-fpm

print_status "Configurando Nginx..."
cat > /etc/nginx/sites-available/monitor-rio << 'EOF'
server {
    listen 80;
    server_name _;
    root /var/www/monitor-rio/public;
    index index.php index.html;

    # Configurações de segurança
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Configurações do Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Cache para assets estáticos
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Negar acesso a arquivos sensíveis
    location ~ /\. {
        deny all;
    }

    location ~ /(storage|bootstrap/cache) {
        deny all;
    }
}
EOF

print_status "Habilitando site..."
ln -sf /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

print_status "Testando configuração do Nginx..."
nginx -t

if [ $? -eq 0 ]; then
    print_status "Configuração OK! Reiniciando Nginx..."
    systemctl restart nginx
    systemctl enable nginx
else
    print_error "Erro na configuração do Nginx!"
    exit 1
fi

print_status "Criando diretório do projeto..."
mkdir -p /var/www/monitor-rio
cd /var/www/monitor-rio

print_status "Clonando repositório..."
git clone https://github.com/Wil-JC-Pimenta/monitor-rio.git .

print_status "Instalando dependências..."
composer install --no-dev --optimize-autoloader

print_status "Configurando permissões..."
chown -R www-data:www-data /var/www/monitor-rio
chmod -R 755 /var/www/monitor-rio
chmod -R 775 /var/www/monitor-rio/storage
chmod -R 775 /var/www/monitor-rio/bootstrap/cache

print_status "Configurando arquivo .env..."
cp config.example.php .env
php artisan key:generate

print_status "Configurando banco de dados..."
php artisan migrate --force

print_status "Gerando dados de exemplo..."
php artisan generate:realistic-data

print_status "Otimizando aplicação..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

print_status "Configurando SSL com Let's Encrypt..."
apt install -y certbot python3-certbot-nginx

print_warning "Para configurar SSL, execute:"
echo "certbot --nginx -d seu-dominio.com"

print_status "✅ Deploy concluído!"
print_status "🌐 Acesse: http://$(curl -s ifconfig.me)"
print_status "📁 Projeto em: /var/www/monitor-rio"
print_status "📝 Logs: tail -f /var/log/nginx/access.log"
