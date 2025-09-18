#!/bin/bash

echo "🚀 DEPLOY COMPLETO - MONITOR RIO PIRACICABA"
echo "=========================================="
echo ""

echo "📋 EXECUTANDO TODOS OS PASSOS DE DEPLOY..."
echo "========================================="

# Tornar scripts executáveis
chmod +x *.sh

# 1. Instalar dependências
echo "1️⃣ Instalando dependências do sistema..."
sudo ./install-production.sh

# 2. Configurar produção
echo "2️⃣ Configurando sistema para produção..."
sudo ./configure-production.sh

# 3. Configurar SSL
echo "3️⃣ Configurando SSL/HTTPS..."
sudo ./setup-ssl.sh

# 4. Configurar cron
echo "4️⃣ Configurando atualização automática..."
sudo ./setup-cron.sh

# 5. Iniciar sistema
echo "5️⃣ Iniciando sistema em produção..."
sudo ./start-production.sh

echo ""
echo "🎉 DEPLOY CONCLUÍDO COM SUCESSO!"
echo "==============================="
echo ""
echo "🌐 SISTEMA ONLINE EM:"
echo "===================="
echo "🔗 https://monitor-rio-piracicaba.com"
echo ""
echo "📊 FUNCIONALIDADES ATIVAS:"
echo "========================="
echo "✅ Backend Laravel com API REST"
echo "✅ Frontend React com dados em tempo real"
echo "✅ Integração com API da ANA"
echo "✅ Atualização automática a cada 15 minutos"
echo "✅ SSL/HTTPS configurado"
echo "✅ Nginx como proxy reverso"
echo "✅ Monitoramento com PM2"
echo "✅ Logs e backup automático"
echo ""
echo "🔧 COMANDOS DE MANUTENÇÃO:"
echo "========================="
echo "📊 Ver status: pm2 status"
echo "📈 Ver logs: pm2 logs monitor-rio-api"
echo "🔄 Reiniciar: pm2 restart monitor-rio-api"
echo "📊 Atualizar dados: php artisan river:fetch"
echo "🧪 Testar API: curl https://monitor-rio-piracicaba.com/api/river-data/stats"
echo ""
echo "🌊 MONITOR RIO PIRACICABA - SISTEMA ONLINE!"
echo "=========================================="
