import { test, expect } from '@playwright/test';
import { DashboardPage } from './page-objects/DashboardPage';
import { expectedPageTitles } from './fixtures/test-data';

/**
 * Testes E2E para a página de Dashboard
 */
test.describe('Dashboard Page', () => {
  let dashboardPage: DashboardPage;

  test.beforeEach(async ({ page }) => {
    dashboardPage = new DashboardPage(page);
    await dashboardPage.goto();
  });

  test('deve carregar a página de dashboard corretamente', async () => {
    // Verifica se a página carregou
    await dashboardPage.isLoaded();
    
    // Verifica se o título da página está correto
    await expect(dashboardPage.pageTitle).toContainText('Dashboard');
  });

  test('deve exibir as métricas principais', async () => {
    // Verifica se as métricas principais estão visíveis
    await dashboardPage.verifyMainMetrics();
    
    // Verifica se os valores das métricas são numéricos
    const nivelMaximo = await dashboardPage.getMetricValue('nivel-maximo');
    const nivelMinimo = await dashboardPage.getMetricValue('nivel-minimo');
    const vazaoMaxima = await dashboardPage.getMetricValue('vazao-maxima');
    const chuvaAcumulada = await dashboardPage.getMetricValue('chuva-acumulada');
    
    expect(nivelMaximo).toBeGreaterThanOrEqual(0);
    expect(nivelMinimo).toBeGreaterThanOrEqual(0);
    expect(vazaoMaxima).toBeGreaterThanOrEqual(0);
    expect(chuvaAcumulada).toBeGreaterThanOrEqual(0);
  });

  test('deve carregar os gráficos corretamente', async () => {
    // Verifica se os gráficos estão carregados
    await dashboardPage.verifyChartsLoaded();
    
    // Verifica se os gráficos têm conteúdo
    await expect(dashboardPage.nivelChart).toBeVisible();
    await expect(dashboardPage.vazaoChart).toBeVisible();
    await expect(dashboardPage.chuvaChart).toBeVisible();
  });

  test('deve exibir o status do sistema', async () => {
    // Verifica se o status do sistema está sendo exibido
    await dashboardPage.verifySystemStatus();
    
    // Verifica se há um indicador de status
    await expect(dashboardPage.statusIndicator).toBeVisible();
    await expect(dashboardPage.lastUpdate).toBeVisible();
  });

  test('deve navegar para outras seções corretamente', async ({ page }) => {
    // Testa navegação para estações
    await dashboardPage.navigateToStations();
    await expect(page).toHaveURL(/.*stations.*/);
    
    // Volta para dashboard
    await dashboardPage.goto();
    
    // Testa navegação para dados
    await dashboardPage.navigateToData();
    await expect(page).toHaveURL(/.*data.*/);
    
    // Volta para dashboard
    await dashboardPage.goto();
    
    // Testa navegação para análises
    await dashboardPage.navigateToAnalytics();
    await expect(page).toHaveURL(/.*analytics.*/);
  });

  test('deve funcionar corretamente em dispositivos móveis', async ({ page }) => {
    // Define viewport mobile
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Verifica se a página é responsiva
    await dashboardPage.verifyResponsiveDesign();
    
    // Verifica se as métricas ainda estão visíveis
    await dashboardPage.verifyMainMetrics();
  });

  test('deve atualizar dados quando solicitado', async () => {
    // Simula atualização de dados
    await dashboardPage.refreshData();
    
    // Verifica se a página ainda está funcionando após refresh
    await dashboardPage.isLoaded();
    await dashboardPage.verifyMainMetrics();
  });

  test('deve exibir dados recentes', async () => {
    // Verifica se os dados são recentes (últimos 10 minutos)
    await dashboardPage.verifyDataIsRecent(10);
  });

  test('deve ter performance adequada', async ({ page }) => {
    // Mede o tempo de carregamento da página
    const startTime = Date.now();
    await dashboardPage.goto();
    await dashboardPage.isLoaded();
    const loadTime = Date.now() - startTime;
    
    // Verifica se a página carregou em menos de 5 segundos
    expect(loadTime).toBeLessThan(5000);
  });

  test('deve funcionar com diferentes navegadores', async ({ page }) => {
    // Este teste será executado em diferentes navegadores automaticamente
    // devido à configuração do Playwright
    
    await dashboardPage.isLoaded();
    await dashboardPage.verifyMainMetrics();
    await dashboardPage.verifyChartsLoaded();
  });
});

test.describe('Dashboard - Cenários de Erro', () => {
  let dashboardPage: DashboardPage;

  test.beforeEach(async ({ page }) => {
    dashboardPage = new DashboardPage(page);
  });

  test('deve lidar com falhas de API graciosamente', async ({ page }) => {
    // Intercepta requisições da API e simula erro
    await page.route('**/api/**', route => {
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: 'Internal Server Error' })
      });
    });
    
    await dashboardPage.goto();
    
    // Verifica se a página ainda carrega, mesmo com erro na API
    await dashboardPage.isLoaded();
    
    // Verifica se há mensagem de erro apropriada
    // (isso depende de como sua aplicação lida com erros)
  });

  test('deve funcionar offline', async ({ page }) => {
    // Simula conexão offline
    await page.context().setOffline(true);
    
    await dashboardPage.goto();
    
    // Verifica se a página ainda carrega (dados em cache)
    await dashboardPage.isLoaded();
    
    // Restaura conexão
    await page.context().setOffline(false);
  });
});
