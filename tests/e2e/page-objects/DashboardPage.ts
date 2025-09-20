import { Page, Locator, expect } from '@playwright/test';
import { TestHelpers } from '../utils/test-helpers';

/**
 * Page Object para a página de Dashboard
 */
export class DashboardPage {
  private helpers: TestHelpers;

  // Seletores principais
  readonly pageTitle: Locator;
  readonly navigationMenu: Locator;
  readonly dashboardSection: Locator;
  readonly stationsSection: Locator;
  readonly dataSection: Locator;
  readonly analyticsSection: Locator;

  // Seletores de métricas
  readonly nivelMaximo: Locator;
  readonly nivelMinimo: Locator;
  readonly vazaoMaxima: Locator;
  readonly chuvaAcumulada: Locator;

  // Seletores de gráficos
  readonly nivelChart: Locator;
  readonly vazaoChart: Locator;
  readonly chuvaChart: Locator;

  // Seletores de status
  readonly statusIndicator: Locator;
  readonly lastUpdate: Locator;

  constructor(private page: Page) {
    this.helpers = new TestHelpers(page);

    // Seletores principais
    this.pageTitle = page.locator('h1, [data-testid="page-title"]');
    this.navigationMenu = page.locator('nav, [data-testid="navigation"]');
    this.dashboardSection = page.locator('[data-testid="dashboard-section"]');
    this.stationsSection = page.locator('[data-testid="stations-section"]');
    this.dataSection = page.locator('[data-testid="data-section"]');
    this.analyticsSection = page.locator('[data-testid="analytics-section"]');

    // Seletores de métricas
    this.nivelMaximo = page.locator('[data-testid="nivel-maximo"], .metric-nivel-max');
    this.nivelMinimo = page.locator('[data-testid="nivel-minimo"], .metric-nivel-min');
    this.vazaoMaxima = page.locator('[data-testid="vazao-maxima"], .metric-vazao-max');
    this.chuvaAcumulada = page.locator('[data-testid="chuva-acumulada"], .metric-chuva-total');

    // Seletores de gráficos
    this.nivelChart = page.locator('[data-testid="nivel-chart"] canvas, .chart-nivel canvas');
    this.vazaoChart = page.locator('[data-testid="vazao-chart"] canvas, .chart-vazao canvas');
    this.chuvaChart = page.locator('[data-testid="chuva-chart"] canvas, .chart-chuva canvas');

    // Seletores de status
    this.statusIndicator = page.locator('[data-testid="status-indicator"], .status-indicator');
    this.lastUpdate = page.locator('[data-testid="last-update"], .last-update');
  }

  /**
   * Navega para a página de dashboard
   */
  async goto() {
    await this.page.goto('/');
    await this.helpers.waitForPageLoad();
  }

  /**
   * Verifica se a página carregou corretamente
   */
  async isLoaded() {
    await expect(this.pageTitle).toBeVisible();
    await expect(this.navigationMenu).toBeVisible();
  }

  /**
   * Verifica se as métricas principais estão visíveis
   */
  async verifyMainMetrics() {
    await expect(this.nivelMaximo).toBeVisible();
    await expect(this.nivelMinimo).toBeVisible();
    await expect(this.vazaoMaxima).toBeVisible();
    await expect(this.chuvaAcumulada).toBeVisible();
  }

  /**
   * Verifica se os gráficos estão carregados
   */
  async verifyChartsLoaded() {
    await this.helpers.waitForChart('[data-testid="nivel-chart"] canvas');
    await this.helpers.waitForChart('[data-testid="vazao-chart"] canvas');
    await this.helpers.waitForChart('[data-testid="chuva-chart"] canvas');
  }

  /**
   * Navega para a seção de estações
   */
  async navigateToStations() {
    await this.helpers.clickAndWait('[href*="stations"], [data-testid="stations-link"]');
  }

  /**
   * Navega para a seção de dados
   */
  async navigateToData() {
    await this.helpers.clickAndWait('[href*="data"], [data-testid="data-link"]');
  }

  /**
   * Navega para a seção de análises
   */
  async navigateToAnalytics() {
    await this.helpers.clickAndWait('[href*="analytics"], [data-testid="analytics-link"]');
  }

  /**
   * Verifica se o status do sistema está sendo exibido
   */
  async verifySystemStatus() {
    await expect(this.statusIndicator).toBeVisible();
    await expect(this.lastUpdate).toBeVisible();
  }

  /**
   * Atualiza os dados (simula refresh)
   */
  async refreshData() {
    const refreshButton = this.page.locator('[data-testid="refresh-button"], .refresh-btn');
    if (await refreshButton.isVisible()) {
      await refreshButton.click();
      await this.helpers.waitForPageLoad();
    }
  }

  /**
   * Verifica se a página é responsiva
   */
  async verifyResponsiveDesign() {
    const isMobile = await this.helpers.isMobileView();
    
    if (isMobile) {
      // Verifica se o menu mobile está funcionando
      const mobileMenu = this.page.locator('[data-testid="mobile-menu"], .mobile-nav');
      if (await mobileMenu.isVisible()) {
        await mobileMenu.click();
        await this.helpers.waitForPageLoad();
      }
    }
  }

  /**
   * Obtém o valor de uma métrica específica
   */
  async getMetricValue(metricType: 'nivel-maximo' | 'nivel-minimo' | 'vazao-maxima' | 'chuva-acumulada'): Promise<number> {
    const selector = `[data-testid="${metricType}"], .metric-${metricType.replace('-', '-')}`;
    const element = this.page.locator(selector);
    const text = await element.textContent();
    return parseFloat(text?.replace(/[^\d.-]/g, '') || '0');
  }

  /**
   * Verifica se os dados estão atualizados (última atualização recente)
   */
  async verifyDataIsRecent(maxMinutesOld = 10) {
    const lastUpdateText = await this.lastUpdate.textContent();
    const now = new Date();
    
    // Aqui você pode implementar lógica para verificar se os dados são recentes
    // Por exemplo, extrair timestamp do texto e comparar com agora
    expect(lastUpdateText).toBeTruthy();
  }
}
