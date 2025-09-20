#!/bin/bash

# Script de inicialização para o container Docker

echo "🚀 Iniciando Monitor Rio Piracicaba..."

# Aguardar o PostgreSQL estar pronto
echo "⏳ Aguardando PostgreSQL..."
until pg_isready -h postgres -p 5432 -U monitor_user; do
  echo "PostgreSQL não está pronto ainda, aguardando..."
  sleep 2
done

echo "✅ PostgreSQL está pronto!"

# Executar migrações
echo "📊 Executando migrações..."
php artisan migrate --force

# Limpar e recriar cache
echo "🧹 Limpando cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar seeders se necessário
echo "🌱 Executando seeders..."
php artisan db:seed --force

echo "✅ Aplicação pronta!"

# Iniciar supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
