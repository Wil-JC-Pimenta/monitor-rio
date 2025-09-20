#!/bin/bash

echo "🚀 INICIANDO SISTEMA EM PRODUÇÃO"
echo "==============================="

# Iniciar serviços
echo "🔄 Iniciando serviços..."
systemctl start postgresql
systemctl start nginx
systemctl start php8.4-fpm

# Configurar PM2 para Laravel
echo "⚙️ Configurando PM2..."
cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [{
    name: 'monitor-rio-api',
    script: 'artisan',
    args: 'serve --host=0.0.0.0 --port=8000',
    cwd: '/home/wilker/Área de Trabalho/monitor-rio',
    instances: 1,
    autorestart: true,
    watch: false,
    max_memory_restart: '1G',
    env: {
      NODE_ENV: 'production'
    }
  }]
};
EOF

# Iniciar aplicação com PM2
pm2 start ecosystem.config.js
pm2 save
pm2 startup

# Testar sistema
echo "🧪 Testando sistema..."
curl -f http://localhost/api/river-data/stats || echo "⚠️ API não respondeu"

# Executar primeira atualização de dados
echo "📊 Executando primeira atualização de dados..."
php artisan river:fetch --mock

echo "✅ Sistema iniciado com sucesso!"
echo ""
echo "🌐 ACESSO AO SISTEMA:"
echo "===================="
echo "🔗 URL: https://monitor-rio-piracicaba.com"
echo "📊 API: https://monitor-rio-piracicaba.com/api/river-data"
echo "📈 Stats: https://monitor-rio-piracicaba.com/api/river-data/stats"
echo "🏭 Estações: https://monitor-rio-piracicaba.com/api/stations"
echo ""
echo "📋 COMANDOS ÚTEIS:"
echo "=================="
echo "📊 Ver logs: pm2 logs monitor-rio-api"
echo "🔄 Reiniciar: pm2 restart monitor-rio-api"
echo "📈 Status: pm2 status"
echo "📊 Dados: php artisan river:fetch --mock"
echo "🧪 Teste: curl https://monitor-rio-piracicaba.com/api/river-data/stats"
echo ""
echo "🎉 SISTEMA MONITOR RIO PIRACICABA ONLINE!"
echo "========================================"
