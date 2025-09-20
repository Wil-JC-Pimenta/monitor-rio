import { FullConfig } from '@playwright/test';

async function globalTeardown(config: FullConfig) {
  console.log('🧹 Iniciando teardown global dos testes E2E...');

  try {
    // Aqui podemos adicionar limpeza de dados de teste se necessário
    // Por exemplo, remover dados criados durante os testes
    
    console.log('✅ Teardown global concluído com sucesso!');
  } catch (error) {
    console.error('❌ Erro durante o teardown global:', error);
    throw error;
  }
}

export default globalTeardown;
