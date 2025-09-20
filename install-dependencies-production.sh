#!/bin/bash

# =============================================================================
# INSTALAÇÃO DE DEPENDÊNCIAS PARA PRODUÇÃO - MONITOR RIO
# Execute este script como root: sudo ./install-dependencies-production.sh
# =============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 Instalação de Dependências - Monitor Rio - Produção${NC}"
echo "=============================================================================="

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}❌ Este script deve ser executado como root (sudo)${NC}"
   exit 1
fi

print_status() {
    echo -e "${GREEN}✅ $1${NC}"
}

# Update system
echo -e "${BLUE}📦 Atualizando sistema...${NC}"
apt update && apt upgrade -y
print_status "Sistema atualizado"

# Install basic dependencies
echo -e "${BLUE}📦 Instalando dependências básicas...${NC}"
apt install -y software-properties-common curl wget git unzip nginx sqlite3
print_status "Dependências básicas instaladas"

# Add PHP repository
echo -e "${BLUE}🐘 Adicionando repositório PHP...${NC}"
add-apt-repository ppa:ondrej/php -y
apt update
print_status "Repositório PHP adicionado"

# Install PHP and extensions
echo -e "${BLUE}🐘 Instalando PHP 8.1 e extensões...${NC}"
apt install -y \
    php8.1 \
    php8.1-fpm \
    php8.1-cli \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-curl \
    php8.1-zip \
    php8.1-gd \
    php8.1-sqlite3 \
    php8.1-intl \
    php8.1-dom \
    php8.1-fileinfo
print_status "PHP 8.1 e extensões instaladas"

# Install Composer
echo -e "${BLUE}🎼 Instalando Composer...${NC}"
cd /tmp
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
print_status "Composer instalado"

# Install Certbot for SSL
echo -e "${BLUE}🔒 Instalando Certbot para SSL...${NC}"
apt install -y certbot python3-certbot-nginx
print_status "Certbot instalado"

# Enable and start services
echo -e "${BLUE}🚀 Habilitando serviços...${NC}"
systemctl enable nginx
systemctl enable php8.1-fpm
systemctl start nginx
systemctl start php8.1-fpm
print_status "Serviços habilitados e iniciados"

echo -e "${GREEN}🎉 Instalação de dependências concluída!${NC}"
echo ""
echo -e "${YELLOW}📋 Próximos passos:${NC}"
echo "1. Configure o Nginx: sudo ./configure-nginx-production.sh"
echo "2. Configure SSL: sudo ./scripts/configure-ssl.sh"
echo "3. Configure serviços: sudo ./scripts/configure-services.sh"
echo ""
echo -e "${GREEN}🎉 Sistema pronto para configuração!${NC}"

