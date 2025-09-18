# 🌊 Monitor Rio Piracicaba - Deploy em Produção

Sistema completo de monitoramento hidrológico do Rio Piracicaba no Vale do Aço, integrado com a API da ANA.

## 🚀 Deploy Rápido

### Opção 1: Deploy Automático (Recomendado)
```bash
# Executar como root
sudo ./deploy-simple.sh
```

### Opção 2: Deploy Manual
```bash
# 1. Instalar dependências
sudo apt update
sudo apt install -y postgresql postgresql-contrib nginx php-pgsql php-xml php-dom php-curl php-mbstring php-zip php-fpm php-cli nodejs npm

# 2. Configurar PostgreSQL
sudo -u postgres psql -c "CREATE USER monitor_rio WITH PASSWORD 'monitor_rio_2025';"
sudo -u postgres psql -c "CREATE DATABASE monitor_rio OWNER monitor_rio;"

# 3. Configurar aplicação
php artisan key:generate --force
php artisan migrate --force
npm install
npm run build

# 4. Configurar Nginx
sudo cp nginx.conf /etc/nginx/sites-available/monitor-rio
sudo ln -s /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
sudo systemctl restart nginx
```

## 🧪 Testar Sistema

```bash
# Executar testes
./test-system.sh

# Testar API
curl http://localhost/api/river-data/stats
curl http://localhost/api/stations

# Testar busca de dados
php artisan river:fetch --mock
```

## 🌐 Acessar Sistema

- **Frontend**: http://localhost
- **API**: http://localhost/api/river-data
- **Stats**: http://localhost/api/river-data/stats
- **Estações**: http://localhost/api/stations

## 📊 Funcionalidades

### ✅ Implementadas
- [x] Backend Laravel com API REST
- [x] Frontend React com dados em tempo real
- [x] Integração com API da ANA
- [x] Atualização automática a cada 15 minutos
- [x] Nginx como servidor web
- [x] PostgreSQL como banco de dados
- [x] Sistema de cache inteligente
- [x] Tratamento robusto de erros
- [x] Logs detalhados
- [x] Testes automáticos

### 🔄 Atualizações Automáticas
- **Níveis**: A cada 15 minutos
- **Vazões**: A cada hora
- **Chuvas**: Diariamente às 6h

## 🛠️ Comandos Úteis

### Gerenciamento de Dados
```bash
# Buscar dados mock
php artisan river:fetch --mock

# Buscar dados reais da ANA
php artisan river:fetch

# Buscar dados de estação específica
php artisan river:fetch --station=PIR001 --days=7

# Buscar dados de vazão
php artisan river:fetch --type=vazoes --days=30
```

### Gerenciamento de Serviços
```bash
# Reiniciar Nginx
sudo systemctl restart nginx

# Reiniciar PostgreSQL
sudo systemctl restart postgresql

# Ver logs do Nginx
sudo tail -f /var/log/nginx/error.log

# Ver logs da aplicação
tail -f storage/logs/laravel.log
```

### Monitoramento
```bash
# Ver status dos serviços
systemctl status nginx postgresql php8.4-fpm

# Ver uso de memória
free -h

# Ver uso de disco
df -h
```

## 🔧 Configuração Avançada

### SSL/HTTPS (Opcional)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx

# Configurar SSL
sudo certbot --nginx -d seu-dominio.com
```

### Domínio Personalizado
1. Editar arquivo `/etc/nginx/sites-available/monitor-rio`
2. Alterar `server_name` para seu domínio
3. Reiniciar Nginx: `sudo systemctl restart nginx`

### Backup do Banco
```bash
# Backup manual
pg_dump -h localhost -U monitor_rio monitor_rio > backup_$(date +%Y%m%d).sql

# Restaurar backup
psql -h localhost -U monitor_rio monitor_rio < backup_20250918.sql
```

## 📈 Monitoramento

### Logs
- **Nginx**: `/var/log/nginx/`
- **Aplicação**: `storage/logs/laravel.log`
- **Sistema**: `/var/log/syslog`

### Métricas
- **CPU**: `htop` ou `top`
- **Memória**: `free -h`
- **Disco**: `df -h`
- **Rede**: `netstat -tulpn`

## 🚨 Troubleshooting

### Problema: Nginx não inicia
```bash
# Verificar configuração
sudo nginx -t

# Ver logs
sudo journalctl -u nginx
```

### Problema: PostgreSQL não conecta
```bash
# Verificar status
sudo systemctl status postgresql

# Verificar logs
sudo journalctl -u postgresql
```

### Problema: API não responde
```bash
# Verificar logs da aplicação
tail -f storage/logs/laravel.log

# Testar conexão com banco
php artisan tinker
>>> DB::connection()->getPdo();
```

## 📞 Suporte

- **Documentação**: Este README
- **Logs**: Verificar logs em `/var/log/` e `storage/logs/`
- **Testes**: Executar `./test-system.sh`

## 🎉 Sistema Online!

Seu sistema Monitor Rio Piracicaba está funcionando com:
- ✅ Backend Laravel
- ✅ Frontend React
- ✅ Integração ANA
- ✅ Atualização automática
- ✅ Nginx + PostgreSQL
- ✅ Dados em tempo real

**Acesse**: http://localhost
