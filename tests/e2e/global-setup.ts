import { chromium, FullConfig } from '@playwright/test';

async function globalSetup(config: FullConfig) {
  console.log('🚀 Iniciando setup global dos testes E2E...');

  const browser = await chromium.launch();
  const page = await browser.newPage();

  try {
    // Verificar se o servidor está rodando
    const baseURL = config.projects[0].use?.baseURL || 'http://localhost:8000';
    console.log(`📡 Verificando servidor em: ${baseURL}`);
    
    await page.goto(baseURL, { waitUntil: 'networkidle', timeout: 30000 });
    
    // Verificar se a página carregou corretamente
    await page.waitForSelector('body', { timeout: 10000 });
    console.log('✅ Servidor está funcionando corretamente');

    // Verificar endpoints básicos
    const healthResponse = await page.request.get(`${baseURL}/health`);
    if (healthResponse.ok()) {
      console.log('✅ Health check passou');
    } else {
      console.warn('⚠️ Health check falhou');
    }

    // Verificar se o banco de dados está funcionando
    const dbResponse = await page.request.get(`${baseURL}/api/stations`);
    if (dbResponse.ok()) {
      console.log('✅ API de estações está funcionando');
    } else {
      console.warn('⚠️ API de estações não está funcionando');
    }

  } catch (error) {
    console.error('❌ Erro durante o setup global:', error);
    throw error;
  } finally {
    await browser.close();
  }

  console.log('🎉 Setup global concluído com sucesso!');
}

export default globalSetup;
