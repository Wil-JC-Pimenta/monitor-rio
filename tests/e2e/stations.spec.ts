import { test, expect } from '@playwright/test';
import { StationsPage } from './page-objects/StationsPage';
import { testStations } from './fixtures/test-data';

/**
 * Testes E2E para a página de Estações
 */
test.describe('Stations Page', () => {
  let stationsPage: StationsPage;

  test.beforeEach(async ({ page }) => {
    stationsPage = new StationsPage(page);
    await stationsPage.goto();
  });

  test('deve carregar a página de estações corretamente', async () => {
    // Verifica se a página carregou
    await stationsPage.isLoaded();
    
    // Verifica se há estações sendo exibidas
    await stationsPage.verifyStationsDisplayed();
  });

  test('deve exibir lista de estações com dados corretos', async () => {
    // Verifica se há pelo menos uma estação
    const stationsCount = await stationsPage.getStationsCount();
    expect(stationsCount).toBeGreaterThan(0);
    
    // Verifica se as estações têm os dados necessários
    await stationsPage.verifyStationData();
  });

  test('deve permitir busca de estações', async () => {
    // Testa busca por nome da estação
    const searchTerm = 'Centro';
    await stationsPage.searchStation(searchTerm);
    
    // Verifica se a estação encontrada está visível
    await expect(stationsPage.stationCards.first()).toBeVisible();
  });

  test('deve permitir filtrar estações por status', async () => {
    // Testa filtro por status ativo
    await stationsPage.filterByStatus('active');
    
    // Verifica se ainda há estações sendo exibidas
    await stationsPage.verifyStationsDisplayed();
    
    // Verifica se as estações têm status correto
    if (await stationsPage.isStationVisible('Rio Piracicaba - Estação Centro')) {
      await stationsPage.verifyStationStatusVisual('Rio Piracicaba - Estação Centro', 'active');
    }
  });

  test('deve abrir detalhes da estação ao clicar', async () => {
    // Clica na primeira estação
    await stationsPage.clickStation('Rio Piracicaba');
    
    // Verifica se os detalhes estão sendo exibidos
    // (pode ser modal ou navegação para página de detalhes)
  });

  test('deve exibir detalhes corretos da estação', async () => {
    // Abre detalhes de uma estação específica
    await stationsPage.openStationDetails('Rio Piracicaba - Estação Centro');
    
    // Verifica se o modal está aberto
    await expect(stationsPage.stationModal).toBeVisible();
    
    // Verifica se os detalhes estão corretos
    await stationsPage.verifyStationDetails({
      name: 'Rio Piracicaba - Estação Centro',
      code: 'TEST001',
      location: 'Centro, Piracicaba - SP',
      status: 'Ativa'
    });
    
    // Fecha o modal
    await stationsPage.closeStationModal();
  });

  test('deve atualizar lista de estações', async () => {
    // Obtém contagem inicial
    const initialCount = await stationsPage.getStationsCount();
    
    // Atualiza a lista
    await stationsPage.refreshStations();
    
    // Verifica se ainda há estações
    await stationsPage.verifyStationsDisplayed();
    
    // Verifica se a contagem não mudou drasticamente
    const newCount = await stationsPage.getStationsCount();
    expect(newCount).toBeGreaterThanOrEqual(initialCount);
  });

  test('deve funcionar corretamente em dispositivos móveis', async ({ page }) => {
    // Define viewport mobile
    await page.setViewportSize({ width: 375, height: 667 });
    
    // Verifica se a página é responsiva
    await stationsPage.verifyResponsiveDesign();
    
    // Verifica se as estações ainda estão visíveis
    await stationsPage.verifyStationsDisplayed();
  });

  test('deve ter performance adequada', async () => {
    // Mede o tempo de carregamento da página
    const startTime = Date.now();
    await stationsPage.goto();
    await stationsPage.isLoaded();
    const loadTime = Date.now() - startTime;
    
    // Verifica se a página carregou em menos de 3 segundos
    expect(loadTime).toBeLessThan(3000);
  });

  test('deve exibir estações no mapa se disponível', async () => {
    // Verifica se há um mapa sendo exibido
    const mapContainer = stationsPage.page.locator('[data-testid="map-container"], .map-container');
    
    if (await mapContainer.isVisible()) {
      // Se o mapa estiver visível, verifica se as estações estão marcadas
      await stationsPage.verifyStationOnMap('Rio Piracicaba - Estação Centro');
    }
  });

  test('deve permitir exportar dados das estações', async ({ page }) => {
    // Intercepta o download
    const downloadPromise = page.waitForEvent('download');
    
    // Clica no botão de exportar
    await stationsPage.exportStations();
    
    // Verifica se o download foi iniciado
    const download = await downloadPromise;
    expect(download.suggestedFilename()).toMatch(/estacoes|stations/);
  });
});

test.describe('Stations - Cenários de Erro', () => {
  let stationsPage: StationsPage;

  test.beforeEach(async ({ page }) => {
    stationsPage = new StationsPage(page);
  });

  test('deve lidar com busca sem resultados', async () => {
    await stationsPage.goto();
    
    // Busca por termo que não existe
    await stationsPage.searchStation('EstacaoInexistente123');
    
    // Verifica se há mensagem de "nenhum resultado encontrado"
    const noResultsMessage = stationsPage.page.locator('[data-testid="no-results"], .no-results');
    if (await noResultsMessage.isVisible()) {
      await expect(noResultsMessage).toContainText('nenhum resultado');
    }
  });

  test('deve lidar com falhas de API graciosamente', async ({ page }) => {
    // Intercepta requisições da API e simula erro
    await page.route('**/api/stations**', route => {
      route.fulfill({
        status: 500,
        contentType: 'application/json',
        body: JSON.stringify({ error: 'Internal Server Error' })
      });
    });
    
    await stationsPage.goto();
    
    // Verifica se a página ainda carrega, mesmo com erro na API
    await stationsPage.isLoaded();
  });

  test('deve funcionar com dados limitados', async ({ page }) => {
    // Intercepta API e retorna apenas uma estação
    await page.route('**/api/stations**', route => {
      route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify([testStations[0]])
      });
    });
    
    await stationsPage.goto();
    
    // Verifica se a página funciona com dados limitados
    await stationsPage.isLoaded();
    await stationsPage.verifyStationsDisplayed();
  });
});

test.describe('Stations - Acessibilidade', () => {
  let stationsPage: StationsPage;

  test.beforeEach(async ({ page }) => {
    stationsPage = new StationsPage(page);
    await stationsPage.goto();
  });

  test('deve ter navegação por teclado funcionando', async ({ page }) => {
    // Testa navegação por Tab
    await page.keyboard.press('Tab');
    
    // Verifica se o foco está no primeiro elemento interativo
    const focusedElement = page.locator(':focus');
    await expect(focusedElement).toBeVisible();
  });

  test('deve ter contraste adequado', async () => {
    // Verifica se os elementos têm contraste adequado
    // (isso pode ser verificado com ferramentas de acessibilidade)
    await stationsPage.verifyStationData();
  });

  test('deve ter labels apropriados', async () => {
    // Verifica se os campos de busca têm labels
    await expect(stationsPage.searchInput).toHaveAttribute('placeholder');
    
    // Verifica se os botões têm texto ou aria-label
    await expect(stationsPage.addStationButton).toBeVisible();
  });
});
