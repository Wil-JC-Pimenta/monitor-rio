#!/bin/bash

echo "⏰ CONFIGURANDO ATUALIZAÇÃO AUTOMÁTICA"
echo "===================================="

# Configurar cron para atualização de dados
echo "🔄 Configurando cron para atualização de dados..."
(crontab -l 2>/dev/null; echo "# Monitor Rio Piracicaba - Atualização de dados") | crontab -
(crontab -l 2>/dev/null; echo "*/15 * * * * cd /home/wilker/Área\\ de\\ Trabalho/monitor-rio && php artisan river:fetch --type=niveis --days=1 >> /var/log/monitor-rio.log 2>&1") | crontab -
(crontab -l 2>/dev/null; echo "0 * * * * cd /home/wilker/Área\\ de\\ Trabalho/monitor-rio && php artisan river:fetch --type=vazoes --days=7 >> /var/log/monitor-rio.log 2>&1") | crontab -
(crontab -l 2>/dev/null; echo "0 6 * * * cd /home/wilker/Área\\ de\\ Trabalho/monitor-rio && php artisan river:fetch --type=chuvas --days=1 >> /var/log/monitor-rio.log 2>&1") | crontab -

# Configurar limpeza de logs
echo "🧹 Configurando limpeza de logs..."
(crontab -l 2>/dev/null; echo "0 2 * * * find /var/log/monitor-rio.log -mtime +30 -delete") | crontab -

# Configurar backup do banco
echo "💾 Configurando backup do banco..."
(crontab -l 2>/dev/null; echo "0 3 * * * pg_dump -h localhost -U monitor_rio monitor_rio > /home/wilker/backups/monitor_rio_\$(date +\%Y\%m\%d).sql") | crontab -

# Criar diretório de backup
mkdir -p /home/wilker/backups

echo "✅ Cron configurado com sucesso!"
echo "💡 Próximo passo: ./start-production.sh"
