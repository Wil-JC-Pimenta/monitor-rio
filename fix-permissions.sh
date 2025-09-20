#!/bin/bash

echo "=== CORRIGINDO PERMISSÕES DO LARAVEL ==="

# Parar servidor se estiver rodando
pkill -f "php artisan serve" 2>/dev/null
sleep 2

# Remover diretórios problemáticos
echo "Removendo diretórios com permissões incorretas..."
rm -rf storage/logs
rm -rf storage/framework/views
rm -rf storage/framework/sessions
rm -rf storage/framework/cache
rm -rf bootstrap/cache

# Recriar diretórios com permissões corretas
echo "Recriando diretórios..."
mkdir -p storage/logs
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache

# Definir permissões corretas
echo "Definindo permissões..."
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Criar arquivos necessários
echo "Criando arquivos necessários..."
touch storage/logs/laravel.log
touch storage/framework/views/.gitignore
touch storage/framework/sessions/.gitignore
touch storage/framework/cache/.gitignore
touch storage/framework/cache/data/.gitignore
touch bootstrap/cache/.gitignore

# Definir permissões dos arquivos
chmod 664 storage/logs/laravel.log
chmod 664 storage/framework/views/.gitignore
chmod 664 storage/framework/sessions/.gitignore
chmod 664 storage/framework/cache/.gitignore
chmod 664 storage/framework/cache/data/.gitignore
chmod 664 bootstrap/cache/.gitignore

echo "✅ Permissões corrigidas com sucesso!"
echo "✅ Diretórios recriados com permissões corretas"
echo "✅ Arquivos necessários criados"

# Limpar cache do Laravel
echo "Limpando cache do Laravel..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

echo "✅ Cache limpo com sucesso!"
echo ""
echo "Agora você pode iniciar o servidor com:"
echo "php artisan serve --host=0.0.0.0 --port=8000"
