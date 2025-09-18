#!/bin/bash

echo "🔧 Instalando dependências do sistema..."

# Atualizar sistema
echo "📦 Atualizando pacotes do sistema..."
sudo apt update

# Instalar PostgreSQL
echo "🐘 Instalando PostgreSQL..."
sudo apt install -y postgresql postgresql-contrib

# Instalar extensões PHP necessárias
echo "🐘 Instalando extensões PHP..."
sudo apt install -y php-pgsql php-xml php-dom php-curl php-mbstring php-zip

# Iniciar e habilitar PostgreSQL
echo "🚀 Iniciando PostgreSQL..."
sudo systemctl start postgresql
sudo systemctl enable postgresql

# Configurar PostgreSQL
echo "⚙️ Configurando PostgreSQL..."
sudo -u postgres psql -c "ALTER USER postgres PASSWORD 'postgres';"

# Criar banco de dados
echo "🗄️ Criando banco de dados..."
sudo -u postgres psql -c "CREATE DATABASE monitor_rio;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE monitor_rio TO postgres;"

echo "✅ Dependências instaladas com sucesso!"
echo "💡 Agora execute: ./setup-postgresql.sh"
