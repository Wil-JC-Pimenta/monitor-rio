#!/bin/bash

echo "🧪 TESTANDO SISTEMA MONITOR RIO PIRACICABA"
echo "========================================="
echo ""

# Testar se o PHP está funcionando
echo "1️⃣ Testando PHP..."
php -v
if [ $? -eq 0 ]; then
    echo "✅ PHP funcionando"
else
    echo "❌ PHP com problemas"
fi

# Testar se o Laravel está funcionando
echo ""
echo "2️⃣ Testando Laravel..."
php artisan --version
if [ $? -eq 0 ]; then
    echo "✅ Laravel funcionando"
else
    echo "❌ Laravel com problemas"
fi

# Testar se o banco está conectado
echo ""
echo "3️⃣ Testando conexão com banco..."
php artisan tinker --execute="echo 'Banco conectado: ' . (DB::connection()->getPdo() ? 'Sim' : 'Não');"
if [ $? -eq 0 ]; then
    echo "✅ Banco conectado"
else
    echo "❌ Banco com problemas"
fi

# Testar comando de busca de dados
echo ""
echo "4️⃣ Testando comando de busca de dados..."
php artisan river:fetch --mock
if [ $? -eq 0 ]; then
    echo "✅ Comando de busca funcionando"
else
    echo "❌ Comando de busca com problemas"
fi

# Testar se o Nginx está rodando
echo ""
echo "5️⃣ Testando Nginx..."
systemctl is-active nginx
if [ $? -eq 0 ]; then
    echo "✅ Nginx rodando"
else
    echo "❌ Nginx não está rodando"
fi

# Testar se o PostgreSQL está rodando
echo ""
echo "6️⃣ Testando PostgreSQL..."
systemctl is-active postgresql
if [ $? -eq 0 ]; then
    echo "✅ PostgreSQL rodando"
else
    echo "❌ PostgreSQL não está rodando"
fi

# Testar API endpoints
echo ""
echo "7️⃣ Testando API endpoints..."
echo "Testando /api/river-data/stats..."
curl -s http://localhost/api/river-data/stats | head -c 100
echo ""
echo "Testando /api/stations..."
curl -s http://localhost/api/stations | head -c 100
echo ""

# Mostrar status geral
echo ""
echo "📊 STATUS GERAL DO SISTEMA:"
echo "=========================="
echo "🌐 Nginx: $(systemctl is-active nginx)"
echo "🐘 PostgreSQL: $(systemctl is-active postgresql)"
echo "🐘 PHP-FPM: $(systemctl is-active php8.4-fpm)"
echo "📊 Dados mock: $(php artisan river:fetch --mock > /dev/null 2>&1 && echo 'OK' || echo 'ERRO')"

echo ""
echo "🎉 TESTE CONCLUÍDO!"
echo "=================="
