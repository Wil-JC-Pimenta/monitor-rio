#!/bin/bash

# Script de deploy para Fly.io
# Uso: ./deploy-fly.sh

set -e

echo "🚀 Iniciando deploy para Fly.io..."

# Verificar se o Fly CLI está instalado
if ! command -v fly &> /dev/null; then
    echo "❌ Fly CLI não encontrado. Instale em: https://fly.io/docs/getting-started/installing-flyctl/"
    exit 1
fi

# Verificar se está logado no Fly.io
if ! fly auth whoami &> /dev/null; then
    echo "❌ Não está logado no Fly.io. Execute: fly auth login"
    exit 1
fi

echo "✅ Fly CLI configurado"

# Verificar se o arquivo fly.toml existe
if [ ! -f "fly.toml" ]; then
    echo "❌ Arquivo fly.toml não encontrado"
    exit 1
fi

echo "✅ Configuração fly.toml encontrada"

# Verificar se o Dockerfile.fly existe
if [ ! -f "Dockerfile.fly" ]; then
    echo "❌ Dockerfile.fly não encontrado"
    exit 1
fi

echo "✅ Dockerfile.fly encontrado"

# Fazer backup do Dockerfile atual
if [ -f "Dockerfile" ]; then
    cp Dockerfile Dockerfile.backup
    echo "✅ Backup do Dockerfile criado"
fi

# Usar Dockerfile.fly para o deploy
cp Dockerfile.fly Dockerfile
echo "✅ Dockerfile.fly copiado para Dockerfile"

# Deploy para Fly.io
echo "🚀 Fazendo deploy..."
fly deploy

# Restaurar Dockerfile original
if [ -f "Dockerfile.backup" ]; then
    mv Dockerfile.backup Dockerfile
    echo "✅ Dockerfile original restaurado"
fi

echo "✅ Deploy concluído com sucesso!"
echo "🌐 Aplicação disponível em: https://monitor-rio-piracicaba.fly.dev"
echo "🔍 Health check: https://monitor-rio-piracicaba.fly.dev/health"
