#!/bin/bash

echo "🚀 INSTALANDO SISTEMA MONITOR RIO PIRACICABA - PRODUÇÃO"
echo "======================================================"

# Atualizar sistema
echo "📦 Atualizando pacotes do sistema..."
apt update

# Instalar PostgreSQL
echo "🐘 Instalando PostgreSQL..."
apt install -y postgresql postgresql-contrib

# Instalar Nginx
echo "🌐 Instalando Nginx..."
apt install -y nginx

# Instalar extensões PHP necessárias
echo "🐘 Instalando extensões PHP..."
apt install -y php-pgsql php-xml php-dom php-curl php-mbstring php-zip php-fpm php-cli

# Instalar Node.js e npm
echo "📦 Instalando Node.js e npm..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Instalar PM2 para gerenciamento de processos
echo "⚙️ Instalando PM2..."
npm install -g pm2

# Instalar Certbot para SSL
echo "🔒 Instalando Certbot para SSL..."
apt install -y certbot python3-certbot-nginx

# Configurar PostgreSQL
echo "⚙️ Configurando PostgreSQL..."
systemctl start postgresql
systemctl enable postgresql

# Criar usuário e banco de dados
sudo -u postgres psql -c "CREATE USER monitor_rio WITH PASSWORD 'monitor_rio_2025';"
sudo -u postgres psql -c "CREATE DATABASE monitor_rio OWNER monitor_rio;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE monitor_rio TO monitor_rio;"

echo "✅ Dependências instaladas com sucesso!"
echo "💡 Próximo passo: ./configure-production.sh"
