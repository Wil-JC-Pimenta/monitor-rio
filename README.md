# Monitor Rio - Sistema de Monitoramento Hidrológico

Sistema web para monitoramento em tempo real de dados hidrológicos de rios, desenvolvido com Laravel 12 e React 19.

## 📋 Sobre o Projeto

O **Monitor Rio** é uma aplicação web que permite o monitoramento em tempo real de dados hidrológicos coletados de estações de medição. O sistema oferece visualização de dados como nível do rio, vazão e precipitação, com alertas automáticos para situações críticas.

### 🎯 Principais Funcionalidades

- **Dashboard Interativo**: Visão geral do sistema com estatísticas em tempo real
- **Monitoramento em Tempo Real**: Acompanhamento contínuo dos dados das estações
- **Gráficos Dinâmicos**: Visualização temporal dos dados com Chart.js
- **Sistema de Alertas**: Notificações automáticas para níveis críticos
- **API RESTful**: Endpoints para integração com estações de medição
- **Interface Responsiva**: Design moderno e adaptável a diferentes dispositivos

## 🛠️ Tecnologias Utilizadas

### Backend
- **Laravel 12** - Framework PHP
- **Inertia.js** - Bridge entre Laravel e React
- **SQLite** - Banco de dados
- **Laravel Wayfinder** - Sistema de navegação

### Frontend
- **React 19** - Biblioteca JavaScript
- **TypeScript** - Tipagem estática
- **Tailwind CSS 4** - Framework CSS
- **Radix UI** - Componentes acessíveis
- **Chart.js** - Gráficos interativos
- **Lucide React** - Ícones

### Ferramentas de Desenvolvimento
- **Vite** - Build tool
- **ESLint** - Linter JavaScript
- **Prettier** - Formatador de código
- **PHPUnit** - Testes PHP

## 📊 Estrutura do Banco de Dados

### Tabela `stations`
Armazena informações das estações de medição:
- `id` - Identificador único
- `name` - Nome da estação
- `code` - Código único da estação
- `location` - Localização descritiva
- `latitude/longitude` - Coordenadas geográficas
- `status` - Status da estação (active/inactive/maintenance)
- `last_measurement` - Última medição registrada

### Tabela `river_data`
Armazena os dados hidrológicos coletados:
- `id` - Identificador único
- `station_id` - Referência à estação
- `nivel` - Nível do rio em metros
- `vazao` - Vazão em m³/s
- `chuva` - Precipitação em mm
- `data_medicao` - Data e hora da medição

## 🚀 Instalação e Configuração

### Pré-requisitos
- PHP 8.2 ou superior
- Composer
- Node.js 18+ e npm
- SQLite

### Passos para Instalação

1. **Clone o repositório**
```bash
git clone <url-do-repositorio>
cd monitor-rio
```

2. **Instale as dependências PHP**
```bash
composer install
```

3. **Instale as dependências JavaScript**
```bash
npm install
```

4. **Configure o ambiente**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Configure o banco de dados**
```bash
# O SQLite já está configurado por padrão
touch database/database.sqlite
```

6. **Execute as migrações**
```bash
php artisan migrate
```

7. **Popule o banco com dados de exemplo (opcional)**
```bash
php artisan db:seed
```

## 🏃‍♂️ Executando o Projeto

### Desenvolvimento
Para executar o projeto em modo de desenvolvimento com hot reload:

```bash
composer run dev
```

Este comando irá iniciar:
- Servidor Laravel (http://localhost:8000)
- Queue worker
- Log viewer (Laravel Pail)
- Vite dev server

### Produção
```bash
npm run build
php artisan serve
```

## 📡 API Endpoints

### Dados Hidrológicos
- `GET /river/data` - Lista todos os dados
- `POST /river/data` - Cria novo registro
- `GET /river/data/{id}` - Exibe dados específicos
- `PUT /river/data/{id}` - Atualiza dados
- `DELETE /river/data/{id}` - Remove dados

### Monitoramento
- `GET /river/monitor` - Dashboard de monitoramento
- `GET /river/chart-data` - Dados para gráficos
- `POST /api/river-data` - Endpoint para estações enviarem dados

### Parâmetros da API
```json
{
  "station_id": "string",
  "nivel": "float",
  "vazao": "float", 
  "chuva": "float",
  "data_medicao": "datetime"
}
```

## 🔧 Comandos Artisan

### Busca de Dados
```bash
# Usar dados mock (recomendado para desenvolvimento)
php artisan river:fetch --mock

# Usar dados reais da API (requer chaves configuradas)
php artisan river:fetch
```
Comando para buscar dados da API da ANA (Agência Nacional de Águas) ou gerar dados mock para desenvolvimento.

### Outros Comandos Úteis
```bash
php artisan migrate:fresh --seed  # Recria banco com dados de exemplo
php artisan queue:work            # Processa filas
php artisan test                  # Executa testes
```

## 📱 Interface do Usuário

### Dashboard Principal
- Estatísticas gerais do sistema
- Status das estações
- Alertas de níveis críticos
- Acesso rápido às funcionalidades

### Página de Monitoramento
- Gráficos interativos com dados temporais
- Filtros por estação
- Visualização de dados recentes
- Indicadores de status em tempo real

### Sistema de Alertas
- **Normal**: Nível ≤ 3.0m
- **Alerta**: Nível > 3.0m e ≤ 5.0m  
- **Crítico**: Nível > 5.0m

## 🧪 Testes

Execute os testes com:
```bash
php artisan test
```

Os testes incluem:
- Testes de autenticação
- Testes de dashboard
- Testes de configurações
- Testes de funcionalidades do sistema

## 📁 Estrutura do Projeto

```
monitor-rio/
├── app/
│   ├── Console/Commands/     # Comandos Artisan
│   ├── Http/Controllers/     # Controladores
│   ├── Models/              # Modelos Eloquent
│   └── Providers/           # Service Providers
├── database/
│   ├── migrations/          # Migrações do banco
│   └── seeders/            # Seeders para dados de exemplo
├── resources/
│   ├── js/
│   │   ├── components/     # Componentes React
│   │   ├── pages/         # Páginas da aplicação
│   │   └── layouts/       # Layouts da aplicação
│   └── css/               # Estilos CSS
├── routes/                # Definição de rotas
└── tests/                # Testes automatizados
```

## 🔒 Autenticação

O sistema utiliza o sistema de autenticação padrão do Laravel com:
- Registro de usuários
- Login/logout
- Verificação de email
- Recuperação de senha

## 🌐 Deploy

### Requisitos de Produção
- PHP 8.2+
- Web server (Apache/Nginx)
- SSL/HTTPS recomendado
- Banco de dados configurado

### Passos para Deploy
1. Configure as variáveis de ambiente
2. Execute `composer install --optimize-autoloader --no-dev`
3. Execute `npm run build`
4. Configure o web server
5. Execute `php artisan migrate --force`

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📄 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 📞 Suporte

Para suporte e dúvidas:
- Abra uma issue no repositório
- Entre em contato com a equipe de desenvolvimento

## 🔧 Status do Projeto

- ✅ **Linting**: 0 erros, 0 warnings
- ✅ **Build**: Funcionando perfeitamente
- ✅ **Testes**: 34/34 passando (97 assertions)
- ✅ **CI/CD**: GitHub Actions configurado
- ✅ **Dependências**: Todas instaladas e atualizadas
- ✅ **Dados Mock**: Sistema funcionando com dados simulados
- ⏳ **APIs Externas**: Aguardando liberação das chaves da ANA

---

**Monitor Rio** - Monitoramento inteligente de recursos hídricos 🚰

