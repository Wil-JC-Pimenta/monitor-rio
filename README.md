# 🌊 Monitor Rio Piracicaba

Sistema de monitoramento hidrológico em tempo real para o Rio Piracicaba e seus afluentes, desenvolvido com Laravel e integração com a API da ANA (Agência Nacional de Águas).

## 🚀 Funcionalidades

### 📊 Dashboard Principal
- **Métricas em tempo real**: Total de estações, medições, níveis máximos e vazões
- **Estatísticas do Rio Piracicaba**: Nível atual, médio, variação e status
- **Resumo das estações**: Lista das principais estações com status e medições
- **Dados recentes**: Tabela com as últimas medições hidrológicas

### 📍 Gestão de Estações
- **10 estações ativas** monitorando rios da região
- **Dados dinâmicos**: Nível médio, vazão média, chuva total por estação
- **Status em tempo real**: Estações ativas/inativas
- **Localização geográfica**: Códigos e localizações das estações

### 📈 Análise de Dados
- **Filtros avançados**: Por estação, data inicial e final
- **Paginação otimizada**: 50 registros por página
- **Dados hidrológicos**: Nível, vazão, chuva com timestamps
- **Exportação**: Dados organizados em tabelas responsivas

### 📋 Analytics e Estatísticas
- **Métricas principais**: Níveis máximos/mínimos, vazões, chuva acumulada
- **Estatísticas visuais**: Nível médio, variação, tendências
- **Análise por estação**: Dados consolidados de cada estação
- **Alertas inteligentes**: Baseados em níveis e condições climáticas

## 🛠️ Tecnologias

- **Backend**: Laravel 11.x
- **Frontend**: Blade Templates + Tailwind CSS
- **Banco de Dados**: SQLite
- **API Externa**: ANA (Agência Nacional de Águas)
- **Cache**: Redis/File Cache
- **Logs**: Monolog

## 📋 Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- NPM
- SQLite

## 🔧 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/seu-usuario/monitor-rio-piracicaba.git
cd monitor-rio-piracicaba
```

### 2. Instale as dependências
```bash
composer install
npm install
```

### 3. Configure o ambiente
```bash
cp config.example.php .env
# Edite o arquivo .env com suas credenciais da ANA API
```

### 4. Configure o banco de dados
```bash
php artisan migrate
php artisan db:seed
```

### 5. Gere dados de exemplo (opcional)
```bash
php artisan generate:realistic-data
```

### 6. Inicie o servidor
```bash
php artisan serve
```

## 🔑 Configuração da API ANA

Para usar dados reais da ANA, configure no arquivo `.env`:

```env
ANA_API_IDENTIFICADOR=seu_identificador_ana
ANA_API_SENHA=sua_senha_ana
PIRACICABA_STATIONS=56690000,56690001,56690002,56690003,56690004
```

## 📊 Dados do Sistema

### Estações Monitoradas
- **Rio Piracicaba** (Ipatinga, Timóteo, Coronel Fabriciano)
- **Rio Doce** (Governador Valadares, Resplendor)
- **Rio das Velhas** (Belo Horizonte)
- **Rio São Francisco** (Pirapora)
- **Afluentes** (Rio Suaçuí, Rio Santo Antônio, Rio Corrente)

### Tipos de Dados
- **Nível da água** (metros)
- **Vazão** (m³/s)
- **Precipitação** (mm)
- **Timestamps** (data/hora das medições)

## 🚀 Comandos Disponíveis

### Dados da ANA
```bash
# Buscar dados reais da ANA
php artisan ana:fetch

# Descobrir estações da ANA
php artisan ana:discover

# Atualizar dados por hora
php artisan data:update-hourly
```

### Dados de Exemplo
```bash
# Gerar dados realistas
php artisan generate:realistic-data

# Limpar e recriar dados
php artisan migrate:fresh --seed
```

## 📁 Estrutura do Projeto

```
monitor-rio/
├── app/
│   ├── Console/Commands/     # Comandos Artisan
│   ├── Http/Controllers/     # Controllers
│   ├── Models/              # Modelos Eloquent
│   └── Services/            # Serviços (ANA API)
├── database/
│   ├── migrations/          # Migrações
│   └── seeders/            # Seeders
├── resources/
│   └── views/              # Templates Blade
├── routes/
│   ├── web.php            # Rotas web
│   └── api.php            # Rotas API
└── config/
    └── ana.php            # Configuração ANA API
```

## 🔒 Segurança

- **Dados sensíveis**: Credenciais da ANA não são commitadas
- **Cache**: Dados da API são cacheados para performance
- **Validação**: Todos os inputs são validados
- **Logs**: Sistema de logs para monitoramento

## 📈 Performance

- **Carregamento**: Páginas carregam em < 0.1s
- **Cache**: Dados da ANA cacheados por 1 hora
- **Otimização**: Consultas otimizadas, sem gráficos pesados
- **Responsivo**: Interface adaptável a todos os dispositivos

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo [LICENSE](LICENSE) para detalhes.

## 📞 Suporte

Para suporte ou dúvidas, abra uma [issue](https://github.com/seu-usuario/monitor-rio-piracicaba/issues) no GitHub.

## 🙏 Agradecimentos

- **ANA** - Agência Nacional de Águas pela API de dados hidrológicos
- **Laravel** - Framework PHP
- **Tailwind CSS** - Framework CSS
- **Comunidade** - Contribuições e feedback

---

**Desenvolvido com ❤️ para monitoramento hidrológico sustentável**