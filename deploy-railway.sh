#!/bin/bash

echo "🚀 Deploy do Monitor Rio Piracicaba para Railway"
echo "================================================"

# 1. Instalar Railway CLI
echo "📦 Instalando Railway CLI..."
npm install -g @railway/cli

# 2. Login no Railway
echo "🔐 Fazendo login no Railway..."
railway login

# 3. Inicializar projeto
echo "🏗️ Inicializando projeto..."
railway init

# 4. Adicionar banco PostgreSQL
echo "🗄️ Adicionando banco PostgreSQL..."
railway add postgresql

# 5. Configurar variáveis de ambiente
echo "⚙️ Configurando variáveis de ambiente..."
railway variables set APP_ENV=production
railway variables set APP_DEBUG=false
railway variables set APP_URL=https://monitor-rio-piracicaba.railway.app
railway variables set DB_CONNECTION=pgsql
railway variables set DB_DATABASE=\${{Postgres.DATABASE}}
railway variables set DB_USERNAME=\${{Postgres.USERNAME}}
railway variables set DB_PASSWORD=\${{Postgres.PASSWORD}}
railway variables set DB_HOST=\${{Postgres.HOST}}
railway variables set DB_PORT=\${{Postgres.PORT}}

# 6. Configurar ANA API (você precisa configurar manualmente)
echo "🔑 Configure suas credenciais da ANA API:"
echo "railway variables set ANA_API_IDENTIFICADOR=seu_identificador"
echo "railway variables set ANA_API_SENHA=sua_senha"

# 7. Deploy
echo "🚀 Fazendo deploy..."
railway up

echo "✅ Deploy concluído!"
echo "🌐 Acesse: https://monitor-rio-piracicaba.railway.app"
