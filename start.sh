#!/bin/bash

# Script de inicialização do Monitor Rio Piracicaba
# Uso: ./start.sh [dev|prod]

set -e

echo "🌊 Monitor Rio Piracicaba - Inicializando..."
echo "================================================"

# Verificar se Docker está instalado
if ! command -v docker &> /dev/null; then
    echo "❌ Docker não está instalado. Por favor, instale o Docker primeiro."
    echo "📖 Guia de instalação: https://docs.docker.com/get-docker/"
    exit 1
fi

# Verificar se Docker Compose está instalado
if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
    echo "📖 Guia de instalação: https://docs.docker.com/compose/install/"
    exit 1
fi

# Definir modo (padrão: produção)
MODE=${1:-prod}

echo "🔧 Modo selecionado: $MODE"

# Criar arquivos necessários se não existirem
echo "📁 Preparando arquivos..."

if [ ! -f .env ]; then
    echo "📝 Criando arquivo .env..."
    cp .env.example .env
fi

if [ ! -f database/database.sqlite ]; then
    echo "🗄️ Criando banco de dados SQLite..."
    touch database/database.sqlite
fi

# Definir comando baseado no modo
if [ "$MODE" = "dev" ]; then
    echo "🚀 Iniciando ambiente de desenvolvimento..."
    echo "📍 URLs disponíveis:"
    echo "   - Aplicação: http://localhost:8001"
    echo "   - Hot Reload: http://localhost:3000"
    echo ""
    
    docker-compose --profile dev up -d
    
    echo "⏳ Aguardando inicialização..."
    sleep 10
    
    echo "🔧 Executando migrações..."
    docker-compose exec dev php artisan migrate --force
    
    echo "📊 Gerando dados de exemplo..."
    docker-compose exec dev php artisan data:generate --days=30
    
else
    echo "🚀 Iniciando ambiente de produção..."
    echo "📍 URL disponível: http://localhost:8000"
    echo ""
    
    docker-compose up -d
    
    echo "⏳ Aguardando inicialização..."
    sleep 15
    
    echo "🔧 Executando migrações..."
    docker-compose exec app php artisan migrate --force
    
    echo "📊 Gerando dados de exemplo..."
    docker-compose exec app php artisan data:generate --days=30
fi

echo ""
echo "✅ Monitor Rio Piracicaba iniciado com sucesso!"
echo ""
echo "📋 Comandos úteis:"
echo "   - Ver logs: docker-compose logs -f"
echo "   - Parar: docker-compose down"
echo "   - Rebuild: docker-compose build --no-cache"
echo "   - Executar comandos: docker-compose exec app php artisan [comando]"
echo ""
echo "🎉 Aproveite o monitoramento!"
