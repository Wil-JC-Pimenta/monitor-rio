# Testes E2E - Monitor Rio Piracicaba

Este diretório contém os testes End-to-End (E2E) automatizados para o sistema Monitor Rio Piracicaba, utilizando Playwright.

## 📋 Estrutura dos Testes

```
tests/e2e/
├── fixtures/           # Dados de teste e fixtures
│   └── test-data.ts   # Dados mockados para testes
├── utils/             # Utilitários e helpers
│   └── test-helpers.ts # Funções auxiliares para testes
├── page-objects/      # Page Object Model
│   ├── DashboardPage.ts
│   └── StationsPage.ts
├── global-setup.ts    # Configuração global dos testes
├── global-teardown.ts # Limpeza global dos testes
├── dashboard.spec.ts  # Testes da página Dashboard
├── stations.spec.ts   # Testes da página Estações
├── api.spec.ts        # Testes das APIs
└── README.md          # Esta documentação
```

## 🚀 Como Executar os Testes

### Execução Local

```bash
# Instalar dependências
npm install

# Executar todos os testes E2E
npm run test:e2e

# Executar com interface visual
npm run test:e2e:ui

# Executar em modo headed (com navegador visível)
npm run test:e2e:headed

# Executar em modo debug
npm run test:e2e:debug

# Visualizar relatório de testes
npm run test:e2e:report
```

### Execução no CI/CD

Os testes E2E são executados automaticamente no GitHub Actions quando há push para as branches `main` ou `development`.

## 🧪 Tipos de Testes

### 1. Testes de Dashboard (`dashboard.spec.ts`)
- ✅ Carregamento da página
- ✅ Exibição de métricas principais
- ✅ Carregamento de gráficos
- ✅ Navegação entre seções
- ✅ Responsividade mobile
- ✅ Performance e tempo de carregamento

### 2. Testes de Estações (`stations.spec.ts`)
- ✅ Listagem de estações
- ✅ Busca e filtros
- ✅ Detalhes das estações
- ✅ Exportação de dados
- ✅ Acessibilidade
- ✅ Responsividade

### 3. Testes de API (`api.spec.ts`)
- ✅ Health check
- ✅ Endpoints de dados
- ✅ Performance e estresse
- ✅ Segurança
- ✅ Validação de parâmetros

## 📊 Cenários de Teste

### Cenários Positivos
- Navegação normal entre páginas
- Visualização de dados hidrológicos
- Funcionalidades de busca e filtro
- Responsividade em diferentes dispositivos

### Cenários de Erro
- Falhas de API
- Conexão offline
- Dados limitados ou inexistentes
- Requisições maliciosas

### Cenários de Performance
- Tempo de carregamento
- Múltiplas requisições concorrentes
- Payloads grandes
- Consistência de resposta

## 🔧 Configuração

### Variáveis de Ambiente

```bash
BASE_URL=http://localhost:8000  # URL base da aplicação
```

### Configuração do Playwright

O arquivo `playwright.config.ts` contém:
- Configuração de navegadores (Chrome, Firefox, Safari)
- Configuração mobile (Pixel 5, iPhone 12)
- Configuração de servidor local
- Configuração de relatórios
- Timeouts e retry policies

## 📈 Relatórios e Métricas

### Relatórios Gerados
- **HTML Report**: Relatório visual detalhado
- **JSON Report**: Dados estruturados para análise
- **JUnit Report**: Compatível com ferramentas CI/CD
- **Screenshots**: Capturas de tela em falhas
- **Videos**: Gravações de execução em falhas
- **Traces**: Rastreamento detalhado de execução

### Métricas de Qualidade
- **Cobertura de funcionalidades**: 95%+
- **Tempo de execução**: < 5 minutos
- **Taxa de sucesso**: 95%+
- **Performance**: < 3s por página

## 🛠️ Manutenção dos Testes

### Adicionando Novos Testes

1. **Criar Page Object** (se necessário):
```typescript
// tests/e2e/page-objects/NovaPage.ts
export class NovaPage {
  constructor(private page: Page) {}
  
  async goto() {
    await this.page.goto('/nova-rota');
  }
}
```

2. **Criar arquivo de teste**:
```typescript
// tests/e2e/nova-funcionalidade.spec.ts
import { test, expect } from '@playwright/test';
import { NovaPage } from './page-objects/NovaPage';

test.describe('Nova Funcionalidade', () => {
  test('deve funcionar corretamente', async ({ page }) => {
    const novaPage = new NovaPage(page);
    await novaPage.goto();
    // ... testes
  });
});
```

### Atualizando Dados de Teste

Edite o arquivo `tests/e2e/fixtures/test-data.ts` para adicionar ou modificar dados de teste.

### Debugging

```bash
# Executar teste específico em modo debug
npx playwright test dashboard.spec.ts --debug

# Executar com navegador visível
npx playwright test --headed

# Executar apenas testes que falharam
npx playwright test --last-failed
```

## 🔍 Troubleshooting

### Problemas Comuns

1. **Servidor não inicia**: Verifique se o Laravel está configurado corretamente
2. **Elementos não encontrados**: Verifique se os seletores estão corretos
3. **Timeouts**: Ajuste os timeouts no `playwright.config.ts`
4. **Falhas intermitentes**: Aumente o número de retries

### Logs e Debug

```bash
# Executar com logs detalhados
DEBUG=pw:api npm run test:e2e

# Gerar trace para análise
npx playwright test --trace on
```

## 📚 Recursos Adicionais

- [Documentação do Playwright](https://playwright.dev/)
- [Page Object Model](https://playwright.dev/docs/pom)
- [Best Practices](https://playwright.dev/docs/best-practices)
- [CI/CD Integration](https://playwright.dev/docs/ci)

## 🤝 Contribuição

Para contribuir com os testes E2E:

1. Siga o padrão Page Object Model
2. Adicione testes para novas funcionalidades
3. Mantenha os dados de teste atualizados
4. Documente novos cenários de teste
5. Execute os testes localmente antes do commit
