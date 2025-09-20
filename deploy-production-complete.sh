#!/bin/bash

# =============================================================================
# DEPLOY COMPLETO EM PRODUÇÃO - MONITOR RIO
# Execute este script como root: sudo ./deploy-production-complete.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 DEPLOY COMPLETO EM PRODUÇÃO - MONITOR RIO${NC}"
echo "=============================================================================="
echo -e "${YELLOW}Este script irá configurar completamente o ambiente de produção${NC}"
echo -e "${YELLOW}para o Monitor Rio (Laravel + Nginx + SQLite + Systemd)${NC}"
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

# Get domain from user
echo -e "${BLUE}🌐 Configuração do domínio${NC}"
read -p "Digite o domínio (ex: monitor-rio.com ou monitor-rio.piracicaba): " DOMAIN
if [ -z "$DOMAIN" ]; then
    DOMAIN="monitor-rio.piracicaba"
    echo -e "${YELLOW}Usando domínio padrão: $DOMAIN${NC}"
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Step 1: Install dependencies
echo -e "${BLUE}📦 PASSO 1: Instalando dependências...${NC}"
echo "=============================================================================="
./install-dependencies-production.sh
echo ""

# Step 2: Configure application
echo -e "${BLUE}⚙️  PASSO 2: Configurando aplicação...${NC}"
echo "=============================================================================="
./configure-app-production.sh
echo ""

# Step 3: Configure Nginx
echo -e "${BLUE}🌐 PASSO 3: Configurando Nginx...${NC}"
echo "=============================================================================="
./configure-nginx-production.sh
echo ""

# Step 4: Configure SSL (only if domain is not local)
if [[ "$DOMAIN" != *"local"* && "$DOMAIN" != *"piracicaba"* ]]; then
    echo -e "${BLUE}🔒 PASSO 4: Configurando SSL...${NC}"
    echo "=============================================================================="
    ./scripts/configure-ssl.sh
    echo ""
else
    echo -e "${YELLOW}⏭️  PASSO 4: Pulando SSL (domínio local detectado)${NC}"
    echo ""
fi

# Step 5: Configure services
echo -e "${BLUE}⚙️  PASSO 5: Configurando serviços...${NC}"
echo "=============================================================================="
./scripts/configure-services.sh
echo ""

# Final status check
echo -e "${BLUE}🏥 VERIFICAÇÃO FINAL${NC}"
echo "=============================================================================="

# Check services
echo -e "${BLUE}Verificando serviços...${NC}"
systemctl is-active nginx && echo -e "${GREEN}✅ Nginx: Ativo${NC}" || echo -e "${RED}❌ Nginx: Inativo${NC}"
systemctl is-active php8.1-fpm && echo -e "${GREEN}✅ PHP-FPM: Ativo${NC}" || echo -e "${RED}❌ PHP-FPM: Inativo${NC}"
systemctl is-active laravel-queue-worker && echo -e "${GREEN}✅ Queue Worker: Ativo${NC}" || echo -e "${RED}❌ Queue Worker: Inativo${NC}"

# Test application
echo -e "${BLUE}Testando aplicação...${NC}"
if curl -f -s http://$DOMAIN/health > /dev/null; then
    echo -e "${GREEN}✅ Aplicação: Respondendo${NC}"
else
    echo -e "${YELLOW}⚠️  Aplicação: Verifique manualmente${NC}"
fi

# Show final information
echo ""
echo -e "${GREEN}🎉 DEPLOY COMPLETO CONCLUÍDO!${NC}"
echo ""
echo -e "${YELLOW}📋 INFORMAÇÕES IMPORTANTES:${NC}"
echo "• Domínio: http://$DOMAIN"
if [[ "$DOMAIN" != *"local"* && "$DOMAIN" != *"piracicaba"* ]]; then
    echo "• HTTPS: https://$DOMAIN"
fi
echo "• Diretório: /var/www/monitor-rio"
echo "• Usuário: www-data"
echo "• Logs: /var/log/nginx/ e journalctl -u laravel-queue-worker"
echo ""
echo -e "${YELLOW}🔧 COMANDOS ÚTEIS:${NC}"
echo "• Status: monitor-rio-status"
echo "• Deploy: ./scripts/deploy.sh"
echo "• Logs queue: journalctl -u laravel-queue-worker -f"
echo "• Logs nginx: tail -f /var/log/nginx/error.log"
echo ""
echo -e "${YELLOW}🌐 TESTE O SITE:${NC}"
echo "curl -I http://$DOMAIN"
echo ""

# Create quick deploy alias
echo -e "${BLUE}📝 Criando alias para deploy rápido...${NC}"
cat >> ~/.bashrc << EOF

# Monitor Rio - Deploy rápido
alias deploy-rio='cd /var/www/monitor-rio && sudo ./scripts/deploy.sh'
alias status-rio='monitor-rio-status'
alias logs-rio='journalctl -u laravel-queue-worker -f'
EOF

print_status "Aliases criados (recarregue o terminal: source ~/.bashrc)"

echo -e "${GREEN}🎉 Deploy em produção finalizado!${NC}"
echo -e "${BLUE}Acesse seu site em: http://$DOMAIN${NC}"

