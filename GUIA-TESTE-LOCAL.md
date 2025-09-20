# 🚀 Guia de Teste Local - Monitor Rio Piracicaba

Este guia mostra como testar o projeto Monitor Rio Piracicaba localmente em seu ambiente de desenvolvimento.

## 📋 Pré-requisitos

✅ **PHP 8.1+** (atual: PHP 8.4.12)
✅ **Composer 2.x** (atual: 2.8.12)
✅ **Node.js 18+** (para frontend)
✅ **SQLite** (banco de dados)

## 🔧 Configuração Inicial

### 1. Verificar Dependências

```bash
# Verificar PHP
php -v

# Verificar Composer
composer --version

# Verificar Node.js
node -v
npm -v
```

### 2. Instalar Dependências

```bash
# Instalar dependências PHP
composer install

# Instalar dependências Node.js
npm install

# Instalar navegadores do Playwright (para testes E2E)
npx playwright install
```

### 3. Configurar Ambiente

```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Gerar chave da aplicação
php artisan key:generate

# Executar migrações
php artisan migrate

# Popular banco com dados de teste
php artisan db:seed
```

## 🚀 Executar o Servidor

### Opção 1: Servidor Laravel (Recomendado)

```bash
# Iniciar servidor Laravel
php artisan serve --host=0.0.0.0 --port=8000
```

**Acesso:** http://localhost:8000

### Opção 2: Docker (Alternativo)

```bash
# Construir e executar com Docker
docker-compose up -d

# Acesso: http://localhost:8000
```

## 🧪 Executar Testes

### Testes E2E (Playwright)

```bash
# Executar todos os testes E2E
npm run test:e2e

# Executar com interface visual
npm run test:e2e:ui

# Executar com navegador visível
npm run test:e2e:headed

# Executar em modo debug
npm run test:e2e:debug

# Ver relatórios de testes
npm run test:e2e:report
```

### Testes Unitários (Vitest)

```bash
# Executar testes unitários
npm run test

# Executar com cobertura
npm run test:coverage
```

### Linting e Formatação

```bash
# Executar ESLint
npm run lint

# Corrigir problemas automaticamente
npm run lint:fix

# Formatar código
npm run format

# Verificar formatação
npm run format:check

# Verificar tipos TypeScript
npm run types
```

## 🎯 Testar Funcionalidades

### 1. Dashboard Principal

**URL:** http://localhost:8000

**O que testar:**
- ✅ Carregamento da página
- ✅ Exibição de métricas hidrológicas
- ✅ Gráficos de nível, vazão e chuva
- ✅ Status do sistema
- ✅ Navegação entre seções

### 2. Estações Hidrológicas

**URL:** http://localhost:8000/stations

**O que testar:**
- ✅ Lista de estações
- ✅ Busca por nome/código
- ✅ Filtros por status
- ✅ Detalhes das estações
- ✅ Exportação de dados

### 3. Dados Hidrológicos

**URL:** http://localhost:8000/data

**O que testar:**
- ✅ Visualização de dados
- ✅ Filtros por data/estação
- ✅ Gráficos interativos
- ✅ Exportação de dados

### 4. Análises

**URL:** http://localhost:8000/analytics

**O que testar:**
- ✅ Estatísticas gerais
- ✅ Análises por estação
- ✅ Tendências temporais
- ✅ Relatórios

### 5. APIs

**Endpoints para testar:**

```bash
# Health check
curl http://localhost:8000/health

# Estações
curl http://localhost:8000/api/stations

# Dados hidrológicos
curl http://localhost:8000/api/river-data

# Análises
curl http://localhost:8000/api/analytics
```

## 📱 Teste Responsivo

### Desktop (1920x1080)
- Teste todas as funcionalidades
- Verifique layout e navegação

### Tablet (768x1024)
- Teste navegação mobile
- Verifique adaptação de layout

### Mobile (375x667)
- Teste menu hambúrguer
- Verifique usabilidade touch

## 🔍 Debugging

### Logs da Aplicação

```bash
# Ver logs em tempo real
tail -f storage/logs/laravel.log

# Ver logs de erro específicos
grep "ERROR" storage/logs/laravel.log
```

### Debug no Navegador

1. **F12** para abrir DevTools
2. **Console** para ver erros JavaScript
3. **Network** para ver requisições
4. **Elements** para inspecionar HTML

### Debug Laravel

```bash
# Habilitar debug no .env
APP_DEBUG=true
LOG_LEVEL=debug

# Ver configurações
php artisan config:show

# Limpar cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 🚨 Solução de Problemas

### Erro: "Could not open input file: artisan"

```bash
# Verificar se está no diretório correto
pwd
ls -la artisan

# Se não existir, recriar
# (o arquivo já existe no projeto)
```

### Erro: "Database file does not exist"

```bash
# Criar banco SQLite
touch database/database.sqlite

# Executar migrações
php artisan migrate
```

### Erro: "Permission denied"

```bash
# Corrigir permissões
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Erro: "Class not found"

```bash
# Regenerar autoload
composer dump-autoload

# Limpar cache
php artisan config:clear
php artisan cache:clear
```

### Testes E2E falhando

```bash
# Verificar se o servidor está rodando
curl http://localhost:8000/health

# Executar setup global
npx playwright install

# Verificar configuração
npx playwright test --list
```

## 📊 Monitoramento

### Performance

```bash
# Verificar uso de memória
php artisan about

# Verificar rotas
php artisan route:list

# Verificar cache
php artisan cache:show
```

### Banco de Dados

```bash
# Verificar conexão
php artisan tinker
>>> DB::connection()->getPdo()

# Verificar tabelas
php artisan migrate:status

# Ver dados de teste
php artisan tinker
>>> App\Models\Station::count()
```

## 🎉 Checklist de Teste

### ✅ Funcionalidades Básicas
- [ ] Servidor inicia sem erros
- [ ] Página principal carrega
- [ ] Navegação funciona
- [ ] APIs respondem
- [ ] Banco de dados conecta

### ✅ Funcionalidades Avançadas
- [ ] Gráficos renderizam
- [ ] Busca e filtros funcionam
- [ ] Responsividade mobile
- [ ] Exportação de dados
- [ ] Atualizações em tempo real

### ✅ Qualidade
- [ ] Testes E2E passam
- [ ] ESLint sem erros
- [ ] TypeScript compila
- [ ] Performance adequada
- [ ] Acessibilidade básica

## 🚀 Deploy Local para Produção

### Usando Docker

```bash
# Construir imagem de produção
docker build -t monitor-rio .

# Executar container
docker run -p 8000:80 monitor-rio
```

### Usando Nginx + PHP-FPM

```bash
# Usar scripts de produção
sudo ./scripts/install-server.sh
sudo ./scripts/configure-nginx.sh
sudo ./scripts/deploy.sh
```

## 📞 Suporte

Se encontrar problemas:

1. **Verificar logs:** `storage/logs/laravel.log`
2. **Verificar status:** `php artisan about`
3. **Limpar cache:** `php artisan optimize:clear`
4. **Reinstalar dependências:** `composer install && npm install`

---

**Status do Projeto:** ✅ Pronto para teste local
**Última atualização:** Setembro 2025
**Versão:** Laravel 10.49.0 + React + TypeScript
