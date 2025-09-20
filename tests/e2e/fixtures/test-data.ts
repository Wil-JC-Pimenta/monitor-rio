/**
 * Dados de teste para os testes E2E do Monitor Rio Piracicaba
 */

export const testStations = [
  {
    name: 'Rio Piracicaba - Estação Centro',
    code: 'TEST001',
    location: 'Centro, Piracicaba - SP',
    latitude: -22.7253,
    longitude: -47.6493,
    status: 'active'
  },
  {
    name: 'Rio Piracicaba - Estação Zona Rural',
    code: 'TEST002',
    location: 'Zona Rural, Piracicaba - SP',
    latitude: -22.7500,
    longitude: -47.6000,
    status: 'active'
  }
];

export const testRiverData = [
  {
    station_id: 1,
    nivel: 2.5,
    vazao: 15.8,
    chuva: 12.5,
    data_medicao: new Date().toISOString()
  },
  {
    station_id: 1,
    nivel: 2.3,
    vazao: 14.2,
    chuva: 8.0,
    data_medicao: new Date(Date.now() - 3600000).toISOString() // 1 hora atrás
  },
  {
    station_id: 2,
    nivel: 1.8,
    vazao: 12.1,
    chuva: 5.5,
    data_medicao: new Date().toISOString()
  }
];

export const testUser = {
  name: 'Teste QA',
  email: 'teste.qa@monitor-rio.com',
  password: 'password123',
  password_confirmation: 'password123'
};

export const expectedPageTitles = {
  dashboard: 'Dashboard - Monitor Rio Piracicaba',
  stations: 'Estações - Monitor Rio Piracicaba',
  data: 'Dados Hidrológicos - Monitor Rio Piracicaba',
  analytics: 'Análises - Monitor Rio Piracicaba',
  login: 'Login - Monitor Rio Piracicaba'
};

export const apiEndpoints = {
  health: '/health',
  stations: '/api/stations',
  riverData: '/api/river-data',
  analytics: '/api/analytics'
};
