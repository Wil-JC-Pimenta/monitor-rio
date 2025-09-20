#!/bin/bash

echo "=== CORRIGINDO PERMISSÕES DE STORAGE DEFINITIVAMENTE ==="

# Parar servidor
pkill -f "php artisan serve" 2>/dev/null
sleep 2

# Remover completamente os diretórios problemáticos
echo "Removendo diretórios com permissões incorretas..."
rm -rf storage/framework/views
rm -rf storage/framework/sessions
rm -rf storage/framework/cache
rm -rf bootstrap/cache

# Recriar com permissões corretas
echo "Recriando diretórios..."
mkdir -p storage/framework/views
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache
mkdir -p storage/framework/cache/data
mkdir -p bootstrap/cache

# Definir permissões usando um método diferente
echo "Definindo permissões..."
find storage -type d -exec chmod 755 {} \;
find storage -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;

# Criar arquivos .gitignore
echo "Criando arquivos .gitignore..."
echo "*" > storage/framework/views/.gitignore
echo "*" > storage/framework/sessions/.gitignore
echo "*" > storage/framework/cache/.gitignore
echo "*" > storage/framework/cache/data/.gitignore
echo "*" > bootstrap/cache/.gitignore

# Verificar se funcionou
echo "Verificando permissões..."
ls -la storage/framework/
ls -la bootstrap/cache/

echo "✅ Permissões corrigidas!"
echo "✅ Agora você pode iniciar o servidor"
