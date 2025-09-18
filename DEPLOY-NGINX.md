# 🌐 Deploy com Nginx - Monitor Rio Piracicaba

## 🚀 Opções Gratuitas com Nginx

### 1. **Oracle Cloud (Recomendado)** ⭐
**2 VMs sempre gratuitas - 1GB RAM cada**

### 2. **Google Cloud Platform**
**$300 créditos gratuitos**

### 3. **AWS Free Tier**
**1 ano gratuito - EC2 t2.micro**

### 4. **DigitalOcean**
**$200 créditos com cupom**

## 🔧 Deploy Automático

### Opção 1: Script Automático
```bash
# Tornar executável
chmod +x deploy-nginx.sh

# Executar como root
sudo ./deploy-nginx.sh
```

### Opção 2: Deploy Manual

#### 1. **Configurar VPS**
```bash
# Atualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependências
sudo apt install -y nginx php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-sqlite3 composer git unzip
```

#### 2. **Configurar Nginx**
```bash
# Copiar configuração
sudo cp nginx.conf /etc/nginx/sites-available/monitor-rio

# Habilitar site
sudo ln -s /etc/nginx/sites-available/monitor-rio /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Testar configuração
sudo nginx -t

# Reiniciar Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

#### 3. **Deploy da Aplicação**
```bash
# Criar diretório
sudo mkdir -p /var/www/monitor-rio
cd /var/www/monitor-rio

# Clonar repositório
sudo git clone https://github.com/Wil-JC-Pimenta/monitor-rio.git .

# Instalar dependências
sudo composer install --no-dev --optimize-autoloader

# Configurar permissões
sudo chown -R www-data:www-data /var/www/monitor-rio
sudo chmod -R 755 /var/www/monitor-rio
sudo chmod -R 775 /var/www/monitor-rio/storage
sudo chmod -R 775 /var/www/monitor-rio/bootstrap/cache

# Configurar ambiente
sudo cp config.example.php .env
sudo php artisan key:generate

# Configurar banco
sudo php artisan migrate --force

# Gerar dados
sudo php artisan generate:realistic-data

# Otimizar
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

#### 4. **Configurar SSL (Let's Encrypt)**
```bash
# Instalar Certbot
sudo apt install -y certbot python3-certbot-nginx

# Configurar SSL
sudo certbot --nginx -d seu-dominio.com

# Renovação automática
sudo crontab -e
# Adicionar: 0 12 * * * /usr/bin/certbot renew --quiet
```

## 🔒 Configurações de Segurança

### Firewall (UFW)
```bash
# Configurar firewall
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Fail2Ban
```bash
# Instalar Fail2Ban
sudo apt install -y fail2ban

# Configurar para Nginx
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Editar configuração
sudo nano /etc/fail2ban/jail.local

# Adicionar:
[nginx-http-auth]
enabled = true
port = http,https
logpath = /var/log/nginx/error.log

# Reiniciar
sudo systemctl restart fail2ban
```

## 📊 Monitoramento

### Logs
```bash
# Logs do Nginx
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log

# Logs da aplicação
sudo tail -f /var/www/monitor-rio/storage/logs/laravel.log

# Logs do sistema
sudo journalctl -u nginx -f
```

### Status dos Serviços
```bash
# Status do Nginx
sudo systemctl status nginx

# Status do PHP-FPM
sudo systemctl status php8.2-fpm

# Reiniciar serviços
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## 🚀 Otimizações

### PHP-FPM
```bash
# Editar configuração
sudo nano /etc/php/8.2/fpm/pool.d/www.conf

# Configurações recomendadas:
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500

# Reiniciar
sudo systemctl restart php8.2-fpm
```

### Nginx
```bash
# Editar configuração principal
sudo nano /etc/nginx/nginx.conf

# Configurações recomendadas:
worker_processes auto;
worker_connections 1024;
keepalive_timeout 65;
client_max_body_size 100M;
```

## 🔄 Atualizações

### Deploy Automático
```bash
# Criar script de atualização
cat > update.sh << 'EOF'
#!/bin/bash
cd /var/www/monitor-rio
sudo git pull origin main
sudo composer install --no-dev --optimize-autoloader
sudo php artisan migrate --force
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
sudo systemctl reload nginx
EOF

chmod +x update.sh
```

### Cron Jobs
```bash
# Editar crontab
sudo crontab -e

# Adicionar:
# Atualizar dados a cada hora
0 * * * * cd /var/www/monitor-rio && php artisan data:update-hourly

# Limpar logs semanalmente
0 0 * * 0 find /var/www/monitor-rio/storage/logs -name "*.log" -mtime +7 -delete
```

## 💰 Custos

- **Oracle Cloud:** Gratuito (sempre)
- **Google Cloud:** $300 créditos
- **AWS:** 1 ano gratuito
- **DigitalOcean:** $200 créditos

## 🆘 Troubleshooting

### Problemas Comuns

1. **Erro 502 Bad Gateway**
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

2. **Permissões**
```bash
sudo chown -R www-data:www-data /var/www/monitor-rio
sudo chmod -R 755 /var/www/monitor-rio
```

3. **Cache**
```bash
sudo php artisan cache:clear
sudo php artisan config:clear
sudo php artisan view:clear
```

4. **Logs**
```bash
sudo tail -f /var/log/nginx/error.log
```

---

**Recomendação:** Use Oracle Cloud para VPS gratuito permanente! 🚀
