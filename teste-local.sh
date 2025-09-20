#!/bin/bash

# 🚀 Script de Teste Local - Monitor Rio Piracicaba
# Este script testa o projeto localmente

echo "🚀 Iniciando teste local do Monitor Rio Piracicaba..."
echo "=================================================="

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para log
log() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Verificar pré-requisitos
log "Verificando pré-requisitos..."

# PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    success "PHP encontrado: $PHP_VERSION"
else
    error "PHP não encontrado!"
    exit 1
fi

# Composer
if command -v composer &> /dev/null; then
    COMPOSER_VERSION=$(composer --version)
    success "Composer encontrado: $COMPOSER_VERSION"
else
    error "Composer não encontrado!"
    exit 1
fi

# Node.js
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    success "Node.js encontrado: $NODE_VERSION"
else
    error "Node.js não encontrado!"
    exit 1
fi

# npm
if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm -v)
    success "npm encontrado: v$NPM_VERSION"
else
    error "npm não encontrado!"
    exit 1
fi

echo ""

# Verificar dependências
log "Verificando dependências..."

if [ -d "vendor" ]; then
    success "Dependências PHP instaladas (vendor/)"
else
    warning "Dependências PHP não encontradas. Execute: composer install"
fi

if [ -d "node_modules" ]; then
    success "Dependências Node.js instaladas (node_modules/)"
else
    warning "Dependências Node.js não encontradas. Execute: npm install"
fi

if [ -f "database/database.sqlite" ]; then
    success "Banco de dados SQLite encontrado"
else
    warning "Banco de dados SQLite não encontrado"
fi

echo ""

# Testar Laravel
log "Testando Laravel..."

if php artisan --version &> /dev/null; then
    LARAVEL_VERSION=$(php artisan --version)
    success "Laravel funcionando: $LARAVEL_VERSION"
else
    error "Laravel não está funcionando!"
    exit 1
fi

# Testar banco de dados
log "Testando conexão com banco de dados..."

if php artisan migrate:status &> /dev/null; then
    success "Conexão com banco de dados OK"
else
    warning "Problema com banco de dados"
fi

echo ""

# Iniciar servidor
log "Iniciando servidor Laravel..."

# Parar servidor anterior se estiver rodando
pkill -f "php artisan serve" 2>/dev/null

# Iniciar servidor em background
php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &
SERVER_PID=$!

# Aguardar servidor iniciar
log "Aguardando servidor iniciar..."
sleep 5

# Testar endpoints
log "Testando endpoints..."

# Health check
if curl -s -f http://127.0.0.1:8000/health > /dev/null; then
    success "Health check: OK"
else
    error "Health check: FALHOU"
fi

# Página principal
if curl -s -f http://127.0.0.1:8000/ > /dev/null; then
    success "Página principal: OK"
else
    error "Página principal: FALHOU"
fi

# API de estações
if curl -s -f http://127.0.0.1:8000/api/stations > /dev/null; then
    success "API de estações: OK"
else
    warning "API de estações: Não disponível"
fi

echo ""

# Testar funcionalidades específicas
log "Testando funcionalidades..."

# Verificar se há dados
STATION_COUNT=$(php artisan tinker --execute="echo App\Models\Station::count();" 2>/dev/null | tail -1)
if [ "$STATION_COUNT" -gt 0 ]; then
    success "Estações no banco: $STATION_COUNT"
else
    warning "Nenhuma estação encontrada no banco"
fi

echo ""

# Testes E2E (opcional)
log "Testando configuração dos testes E2E..."

if command -v npx &> /dev/null && [ -f "playwright.config.ts" ]; then
    if npx playwright test --list > /dev/null 2>&1; then
        TEST_COUNT=$(npx playwright test --list 2>/dev/null | grep -c "test")
        success "Testes E2E configurados: $TEST_COUNT testes encontrados"
    else
        warning "Problema com configuração dos testes E2E"
    fi
else
    warning "Playwright não configurado"
fi

echo ""

# Mostrar informações de acesso
success "🎉 Teste local concluído com sucesso!"
echo ""
echo "📋 Informações de acesso:"
echo "   🌐 URL: http://localhost:8000"
echo "   📊 Dashboard: http://localhost:8000/"
echo "   🏭 Estações: http://localhost:8000/stations"
echo "   📈 Dados: http://localhost:8000/data"
echo "   📊 Análises: http://localhost:8000/analytics"
echo "   🔧 Health: http://localhost:8000/health"
echo ""
echo "🚀 Para executar testes E2E:"
echo "   npm run test:e2e"
echo ""
echo "🛑 Para parar o servidor:"
echo "   pkill -f 'php artisan serve'"
echo ""

# Manter servidor rodando
log "Servidor rodando em http://localhost:8000"
log "Pressione Ctrl+C para parar"

# Função para cleanup
cleanup() {
    echo ""
    log "Parando servidor..."
    kill $SERVER_PID 2>/dev/null
    success "Servidor parado"
    exit 0
}

# Capturar Ctrl+C
trap cleanup SIGINT

# Manter script rodando
wait $SERVER_PID
