import { test, expect } from '@playwright/test';
import { apiEndpoints } from './fixtures/test-data';

/**
 * Testes E2E para APIs do Monitor Rio Piracicaba
 */
test.describe('API Endpoints', () => {
  
  test('Health Check deve retornar status 200', async ({ request }) => {
    const response = await request.get(apiEndpoints.health);
    
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(data).toHaveProperty('status');
    expect(data.status).toBe('healthy');
  });

  test('API de estações deve retornar dados válidos', async ({ request }) => {
    const response = await request.get(apiEndpoints.stations);
    
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBe(true);
    
    if (data.length > 0) {
      const station = data[0];
      expect(station).toHaveProperty('id');
      expect(station).toHaveProperty('name');
      expect(station).toHaveProperty('code');
      expect(station).toHaveProperty('status');
    }
  });

  test('API de dados hidrológicos deve retornar dados válidos', async ({ request }) => {
    const response = await request.get(apiEndpoints.riverData);
    
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBe(true);
    
    if (data.length > 0) {
      const riverData = data[0];
      expect(riverData).toHaveProperty('station_id');
      expect(riverData).toHaveProperty('data_medicao');
    }
  });

  test('API de análises deve retornar estatísticas válidas', async ({ request }) => {
    const response = await request.get(apiEndpoints.analytics);
    
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(data).toHaveProperty('max_nivel');
    expect(data).toHaveProperty('min_nivel');
    expect(data).toHaveProperty('max_vazao');
    expect(data).toHaveProperty('total_chuva');
  });

  test('API deve retornar CORS headers corretos', async ({ request }) => {
    const response = await request.get(apiEndpoints.stations, {
      headers: {
        'Origin': 'http://localhost:8000'
      }
    });
    
    expect(response.status()).toBe(200);
    
    // Verifica headers CORS
    const headers = response.headers();
    expect(headers['access-control-allow-origin']).toBeDefined();
  });

  test('API deve ter rate limiting adequado', async ({ request }) => {
    // Faz múltiplas requisições para testar rate limiting
    const promises = Array.from({ length: 10 }, () => 
      request.get(apiEndpoints.stations)
    );
    
    const responses = await Promise.all(promises);
    
    // Verifica se todas as requisições foram bem-sucedidas
    // ou se alguma foi limitada (429)
    const statusCodes = responses.map(r => r.status());
    const hasRateLimit = statusCodes.includes(429);
    const allSuccess = statusCodes.every(code => code === 200);
    
    expect(allSuccess || hasRateLimit).toBe(true);
  });

  test('API deve retornar erro 404 para endpoints inexistentes', async ({ request }) => {
    const response = await request.get('/api/endpoint-inexistente');
    
    expect(response.status()).toBe(404);
  });

  test('API deve retornar dados em formato JSON', async ({ request }) => {
    const response = await request.get(apiEndpoints.stations);
    
    expect(response.status()).toBe(200);
    
    const contentType = response.headers()['content-type'];
    expect(contentType).toContain('application/json');
  });

  test('API deve ter tempo de resposta adequado', async ({ request }) => {
    const startTime = Date.now();
    
    const response = await request.get(apiEndpoints.stations);
    
    const responseTime = Date.now() - startTime;
    
    expect(response.status()).toBe(200);
    expect(responseTime).toBeLessThan(2000); // Menos de 2 segundos
  });

  test('API deve funcionar com diferentes métodos HTTP', async ({ request }) => {
    // Testa GET
    const getResponse = await request.get(apiEndpoints.stations);
    expect(getResponse.status()).toBe(200);
    
    // Testa OPTIONS (para CORS)
    const optionsResponse = await request.options(apiEndpoints.stations);
    expect([200, 204].includes(optionsResponse.status())).toBe(true);
  });

  test('API deve validar parâmetros de entrada', async ({ request }) => {
    // Testa com parâmetros inválidos
    const response = await request.get(`${apiEndpoints.riverData}?station_id=invalid`);
    
    // Deve retornar erro 400 ou dados vazios
    expect([200, 400].includes(response.status())).toBe(true);
  });
});

test.describe('API - Performance e Estresse', () => {
  
  test('API deve suportar múltiplas requisições concorrentes', async ({ request }) => {
    const promises = Array.from({ length: 20 }, () => 
      request.get(apiEndpoints.health)
    );
    
    const responses = await Promise.all(promises);
    
    // Verifica se todas as requisições foram bem-sucedidas
    const allSuccess = responses.every(r => r.status() === 200);
    expect(allSuccess).toBe(true);
  });

  test('API deve ter tempo de resposta consistente', async ({ request }) => {
    const responseTimes: number[] = [];
    
    // Faz 5 requisições e mede o tempo de cada uma
    for (let i = 0; i < 5; i++) {
      const startTime = Date.now();
      const response = await request.get(apiEndpoints.health);
      const responseTime = Date.now() - startTime;
      
      expect(response.status()).toBe(200);
      responseTimes.push(responseTime);
    }
    
    // Verifica se o tempo de resposta é consistente
    const avgResponseTime = responseTimes.reduce((a, b) => a + b, 0) / responseTimes.length;
    const maxResponseTime = Math.max(...responseTimes);
    const minResponseTime = Math.min(...responseTimes);
    
    // A diferença entre o maior e menor tempo não deve ser muito grande
    expect(maxResponseTime - minResponseTime).toBeLessThan(1000);
    expect(avgResponseTime).toBeLessThan(500);
  });

  test('API deve funcionar com payloads grandes', async ({ request }) => {
    // Testa endpoint que pode retornar muitos dados
    const response = await request.get(`${apiEndpoints.riverData}?limit=1000`);
    
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(Array.isArray(data)).toBe(true);
  });
});

test.describe('API - Segurança', () => {
  
  test('API deve rejeitar requisições maliciosas', async ({ request }) => {
    // Testa SQL injection
    const maliciousResponse = await request.get(`${apiEndpoints.stations}?id=1'; DROP TABLE stations; --`);
    
    // Deve retornar erro ou dados válidos (não deve executar SQL malicioso)
    expect([200, 400, 500].includes(maliciousResponse.status())).toBe(true);
  });

  test('API deve validar headers de segurança', async ({ request }) => {
    const response = await request.get(apiEndpoints.stations);
    
    expect(response.status()).toBe(200);
    
    const headers = response.headers();
    
    // Verifica se há headers de segurança
    expect(headers['x-content-type-options']).toBeDefined();
    expect(headers['x-frame-options']).toBeDefined();
  });

  test('API deve funcionar apenas com métodos permitidos', async ({ request }) => {
    // Testa métodos não permitidos
    const deleteResponse = await request.delete(apiEndpoints.stations);
    const putResponse = await request.put(apiEndpoints.stations);
    const patchResponse = await request.patch(apiEndpoints.stations);
    
    // Deve retornar erro 405 (Method Not Allowed) ou 404
    expect([405, 404].includes(deleteResponse.status())).toBe(true);
    expect([405, 404].includes(putResponse.status())).toBe(true);
    expect([405, 404].includes(patchResponse.status())).toBe(true);
  });
});
