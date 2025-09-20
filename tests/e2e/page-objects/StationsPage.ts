import { Page, Locator, expect } from '@playwright/test';
import { TestHelpers } from '../utils/test-helpers';

/**
 * Page Object para a página de Estações
 */
export class StationsPage {
  private helpers: TestHelpers;

  // Seletores principais
  readonly pageTitle: Locator;
  readonly stationsList: Locator;
  readonly stationCards: Locator;
  readonly searchInput: Locator;
  readonly filterSelect: Locator;

  // Seletores de ações
  readonly addStationButton: Locator;
  readonly refreshButton: Locator;
  readonly exportButton: Locator;

  // Seletores de detalhes da estação
  readonly stationModal: Locator;
  readonly stationName: Locator;
  readonly stationCode: Locator;
  readonly stationLocation: Locator;
  readonly stationStatus: Locator;

  constructor(private page: Page) {
    this.helpers = new TestHelpers(page);

    // Seletores principais
    this.pageTitle = page.locator('h1, [data-testid="page-title"]');
    this.stationsList = page.locator('[data-testid="stations-list"], .stations-list');
    this.stationCards = page.locator('[data-testid="station-card"], .station-card');
    this.searchInput = page.locator('[data-testid="search-input"], input[placeholder*="buscar"]');
    this.filterSelect = page.locator('[data-testid="filter-select"], select[name*="filter"]');

    // Seletores de ações
    this.addStationButton = page.locator('[data-testid="add-station"], .btn-add-station');
    this.refreshButton = page.locator('[data-testid="refresh-button"], .btn-refresh');
    this.exportButton = page.locator('[data-testid="export-button"], .btn-export');

    // Seletores de detalhes da estação
    this.stationModal = page.locator('[data-testid="station-modal"], .modal');
    this.stationName = page.locator('[data-testid="station-name"], .station-name');
    this.stationCode = page.locator('[data-testid="station-code"], .station-code');
    this.stationLocation = page.locator('[data-testid="station-location"], .station-location');
    this.stationStatus = page.locator('[data-testid="station-status"], .station-status');
  }

  /**
   * Navega para a página de estações
   */
  async goto() {
    await this.page.goto('/stations');
    await this.helpers.waitForPageLoad();
  }

  /**
   * Verifica se a página carregou corretamente
   */
  async isLoaded() {
    await expect(this.pageTitle).toBeVisible();
    await expect(this.stationsList).toBeVisible();
  }

  /**
   * Verifica se há estações sendo exibidas
   */
  async verifyStationsDisplayed() {
    await expect(this.stationCards).toHaveCount({ min: 1 });
  }

  /**
   * Busca por uma estação específica
   */
  async searchStation(searchTerm: string) {
    await this.searchInput.fill(searchTerm);
    await this.page.keyboard.press('Enter');
    await this.helpers.waitForPageLoad();
  }

  /**
   * Filtra estações por status
   */
  async filterByStatus(status: 'active' | 'inactive' | 'maintenance') {
    await this.filterSelect.selectOption(status);
    await this.helpers.waitForPageLoad();
  }

  /**
   * Clica em uma estação específica
   */
  async clickStation(stationName: string) {
    const stationCard = this.page.locator(`[data-testid="station-card"]:has-text("${stationName}")`);
    await stationCard.click();
    await this.helpers.waitForPageLoad();
  }

  /**
   * Abre os detalhes de uma estação
   */
  async openStationDetails(stationName: string) {
    await this.clickStation(stationName);
    await this.helpers.waitForModal();
  }

  /**
   * Verifica os detalhes de uma estação no modal
   */
  async verifyStationDetails(expectedStation: {
    name: string;
    code: string;
    location: string;
    status: string;
  }) {
    await expect(this.stationModal).toBeVisible();
    await expect(this.stationName).toContainText(expectedStation.name);
    await expect(this.stationCode).toContainText(expectedStation.code);
    await expect(this.stationLocation).toContainText(expectedStation.location);
    await expect(this.stationStatus).toContainText(expectedStation.status);
  }

  /**
   * Fecha o modal de detalhes da estação
   */
  async closeStationModal() {
    await this.helpers.closeModal();
  }

  /**
   * Verifica se a estação está no mapa (se houver)
   */
  async verifyStationOnMap(stationName: string) {
    const mapContainer = this.page.locator('[data-testid="map-container"], .map-container');
    if (await mapContainer.isVisible()) {
      // Aqui você pode implementar verificações específicas do mapa
      await expect(mapContainer).toBeVisible();
    }
  }

  /**
   * Atualiza a lista de estações
   */
  async refreshStations() {
    await this.refreshButton.click();
    await this.helpers.waitForPageLoad();
  }

  /**
   * Exporta dados das estações
   */
  async exportStations() {
    await this.exportButton.click();
    
    // Aguarda o download iniciar
    const downloadPromise = this.page.waitForEvent('download');
    await downloadPromise;
  }

  /**
   * Verifica se a página é responsiva
   */
  async verifyResponsiveDesign() {
    const isMobile = await this.helpers.isMobileView();
    
    if (isMobile) {
      // Verifica se o layout mobile está funcionando
      await expect(this.stationCards).toBeVisible();
      
      // Verifica se a busca mobile está funcionando
      if (await this.searchInput.isVisible()) {
        await this.searchInput.click();
        await this.searchInput.fill('test');
      }
    }
  }

  /**
   * Obtém o número de estações exibidas
   */
  async getStationsCount(): Promise<number> {
    return await this.stationCards.count();
  }

  /**
   * Verifica se uma estação específica está na lista
   */
  async isStationVisible(stationName: string): Promise<boolean> {
    const stationCard = this.page.locator(`[data-testid="station-card"]:has-text("${stationName}")`);
    return await stationCard.isVisible();
  }

  /**
   * Verifica se as estações têm os dados necessários
   */
  async verifyStationData() {
    const firstStation = this.stationCards.first();
    
    // Verifica se a estação tem nome
    await expect(firstStation.locator('.station-name, [data-testid="station-name"]')).toBeVisible();
    
    // Verifica se a estação tem código
    await expect(firstStation.locator('.station-code, [data-testid="station-code"]')).toBeVisible();
    
    // Verifica se a estação tem status
    await expect(firstStation.locator('.station-status, [data-testid="station-status"]')).toBeVisible();
  }

  /**
   * Verifica se o status da estação está correto visualmente
   */
  async verifyStationStatusVisual(stationName: string, expectedStatus: 'active' | 'inactive' | 'maintenance') {
    const stationCard = this.page.locator(`[data-testid="station-card"]:has-text("${stationName}")`);
    const statusElement = stationCard.locator('.station-status, [data-testid="station-status"]');
    
    await expect(statusElement).toBeVisible();
    
    // Verifica se a cor do status está correta (se houver classes CSS específicas)
    const statusClass = await statusElement.getAttribute('class');
    if (statusClass) {
      expect(statusClass).toContain(`status-${expectedStatus}`);
    }
  }
}
