import { Page, expect } from '@playwright/test';

/**
 * Utilitários para testes E2E do Monitor Rio Piracicaba
 */

export class TestHelpers {
  constructor(private page: Page) {}

  /**
   * Aguarda o carregamento completo da página
   */
  async waitForPageLoad() {
    await this.page.waitForLoadState('networkidle');
    await this.page.waitForSelector('body');
  }

  /**
   * Verifica se um elemento está visível e clicável
   */
  async isElementVisible(selector: string): Promise<boolean> {
    try {
      const element = await this.page.locator(selector);
      await element.waitFor({ state: 'visible', timeout: 5000 });
      return await element.isVisible();
    } catch {
      return false;
    }
  }

  /**
   * Aguarda e clica em um elemento
   */
  async clickAndWait(selector: string, timeout = 10000) {
    const element = this.page.locator(selector);
    await element.waitFor({ state: 'visible', timeout });
    await element.click();
    await this.waitForPageLoad();
  }

  /**
   * Preenche um campo de input
   */
  async fillInput(selector: string, value: string) {
    const input = this.page.locator(selector);
    await input.waitFor({ state: 'visible' });
    await input.clear();
    await input.fill(value);
  }

  /**
   * Verifica se uma notificação de sucesso aparece
   */
  async expectSuccessMessage(message?: string) {
    const notification = this.page.locator('[data-testid="success-notification"], .alert-success, .notification-success');
    await expect(notification).toBeVisible();
    
    if (message) {
      await expect(notification).toContainText(message);
    }
  }

  /**
   * Verifica se uma notificação de erro aparece
   */
  async expectErrorMessage(message?: string) {
    const notification = this.page.locator('[data-testid="error-notification"], .alert-error, .notification-error');
    await expect(notification).toBeVisible();
    
    if (message) {
      await expect(notification).toContainText(message);
    }
  }

  /**
   * Aguarda o carregamento de um gráfico
   */
  async waitForChart(selector = 'canvas') {
    const chart = this.page.locator(selector);
    await chart.waitFor({ state: 'visible' });
    
    // Aguarda um pouco mais para garantir que o gráfico foi renderizado
    await this.page.waitForTimeout(1000);
  }

  /**
   * Verifica se um valor numérico está dentro de uma faixa esperada
   */
  async expectNumericValueInRange(selector: string, min: number, max: number) {
    const element = this.page.locator(selector);
    const text = await element.textContent();
    const value = parseFloat(text?.replace(/[^\d.-]/g, '') || '0');
    
    expect(value).toBeGreaterThanOrEqual(min);
    expect(value).toBeLessThanOrEqual(max);
  }

  /**
   * Aguarda o carregamento de dados da API
   */
  async waitForApiResponse(url: string) {
    await this.page.waitForResponse(response => 
      response.url().includes(url) && response.status() === 200
    );
  }

  /**
   * Simula scroll para baixo na página
   */
  async scrollToBottom() {
    await this.page.evaluate(() => {
      window.scrollTo(0, document.body.scrollHeight);
    });
  }

  /**
   * Tira screenshot da página atual
   */
  async takeScreenshot(name: string) {
    await this.page.screenshot({ 
      path: `test-results/screenshots/${name}.png`,
      fullPage: true 
    });
  }

  /**
   * Verifica se a página está responsiva (mobile)
   */
  async isMobileView() {
    const viewport = this.page.viewportSize();
    return viewport && viewport.width < 768;
  }

  /**
   * Aguarda o carregamento de um modal ou popup
   */
  async waitForModal(selector = '[role="dialog"], .modal') {
    const modal = this.page.locator(selector);
    await modal.waitFor({ state: 'visible' });
  }

  /**
   * Fecha um modal ou popup
   */
  async closeModal(selector = '[role="dialog"] button[aria-label="Close"], .modal .close') {
    const closeButton = this.page.locator(selector);
    if (await closeButton.isVisible()) {
      await closeButton.click();
    }
  }
}
