#!/bin/bash

echo "🔒 CONFIGURANDO SSL/HTTPS"
echo "========================"

# Configurar SSL com Let's Encrypt
echo "🔐 Configurando certificado SSL..."
certbot --nginx -d monitor-rio-piracicaba.com -d www.monitor-rio-piracicaba.com --non-interactive --agree-tos --email admin@monitor-rio-piracicaba.com

# Configurar renovação automática
echo "🔄 Configurando renovação automática..."
(crontab -l 2>/dev/null; echo "0 12 * * * /usr/bin/certbot renew --quiet") | crontab -

echo "✅ SSL configurado com sucesso!"
echo "💡 Próximo passo: ./setup-cron.sh"
