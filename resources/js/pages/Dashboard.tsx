import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';

interface RiverData {
  id: number;
  station_id: number;
  nivel: number;
  vazao: number;
  chuva: number;
  data_medicao: string;
  station: {
    id: number;
    name: string;
    code: string;
    location: string;
    status: string;
  };
}

interface Station {
  id: number;
  name: string;
  code: string;
  location: string;
  status: string;
  river_data_count: number;
}

interface Stats {
  total_measurements: number;
  total_stations: number;
  active_stations: number;
  latest_measurement: RiverData;
  measurements_today: number;
  measurements_this_week: number;
  max_nivel: number;
  max_vazao: number;
  max_chuva: number;
}

export default function Dashboard() {
  const [riverData, setRiverData] = useState<RiverData[]>([]);
  const [stations, setStations] = useState<Station[]>([]);
  const [stats, setStats] = useState<Stats | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedStation, setSelectedStation] = useState<string>('all');
  const [timeRange, setTimeRange] = useState<number>(24);

  // Buscar dados da API
  const fetchData = async () => {
    try {
      setLoading(true);
      
      // Buscar dados hidrológicos
      const dataResponse = await fetch(`/api/river-data?days=${timeRange}&limit=100`);
      const data = await dataResponse.json();
      setRiverData(data.data || []);

      // Buscar estações
      const stationsResponse = await fetch('/api/stations');
      const stationsData = await stationsResponse.json();
      setStations(stationsData.data || []);

      // Buscar estatísticas
      const statsResponse = await fetch('/api/river-data/stats');
      const statsData = await statsResponse.json();
      setStats(statsData.data || null);

    } catch (error) {
      console.error('Erro ao buscar dados:', error);
    } finally {
      setLoading(false);
    }
  };

  // Atualizar dados automaticamente
  useEffect(() => {
    fetchData();
    const interval = setInterval(fetchData, 30000); // Atualizar a cada 30 segundos
    return () => clearInterval(interval);
  }, [timeRange]);

  // Filtrar dados por estação
  const filteredData = selectedStation === 'all' 
    ? riverData 
    : riverData.filter(data => data.station.code === selectedStation);

  // Agrupar dados por estação para gráficos
  const groupedData = filteredData.reduce((acc, data) => {
    const stationCode = data.station.code;
    if (!acc[stationCode]) {
      acc[stationCode] = {
        station: data.station,
        data: []
      };
    }
    acc[stationCode].data.push(data);
    return acc;
  }, {} as Record<string, { station: any; data: RiverData[] }>);

  const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleString('pt-BR');
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'text-green-600 bg-green-100';
      case 'inactive': return 'text-red-600 bg-red-100';
      case 'maintenance': return 'text-yellow-600 bg-yellow-100';
      default: return 'text-gray-600 bg-gray-100';
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Carregando dados do Rio Piracicaba...</p>
        </div>
      </div>
    );
  }

  return (
    <>
      <Head title="Monitor Rio Piracicaba - Dashboard" />
      
      <div className="min-h-screen bg-gray-50">
        {/* Header */}
        <header className="bg-white shadow-sm border-b">
          <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div className="flex justify-between items-center py-6">
              <div>
                <h1 className="text-3xl font-bold text-gray-900">🌊 Monitor Rio Piracicaba</h1>
                <p className="text-gray-600">Sistema de monitoramento hidrológico em tempo real</p>
              </div>
              <div className="text-right">
                <div className="text-sm text-gray-500">Última atualização</div>
                <div className="text-lg font-semibold text-gray-900">
                  {stats?.latest_measurement ? formatTime(stats.latest_measurement.data_medicao) : 'N/A'}
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* Controles */}
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Estação
                </label>
                <select
                  value={selectedStation}
                  onChange={(e) => setSelectedStation(e.target.value)}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="all">Todas as estações</option>
                  {stations.map(station => (
                    <option key={station.id} value={station.code}>
                      {station.name}
                    </option>
                  ))}
                </select>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Período
                </label>
                <select
                  value={timeRange}
                  onChange={(e) => setTimeRange(Number(e.target.value))}
                  className="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value={1}>Últimas 24 horas</option>
                  <option value={7}>Últimos 7 dias</option>
                  <option value={30}>Últimos 30 dias</option>
                </select>
              </div>

              <div className="flex items-end">
                <button
                  onClick={fetchData}
                  className="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  🔄 Atualizar Dados
                </button>
              </div>
            </div>
          </div>

          {/* Estatísticas */}
          {stats && (
            <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
              <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-blue-100 rounded-lg">
                    <span className="text-2xl">📊</span>
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Total de Medições</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.total_measurements.toLocaleString()}</p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-green-100 rounded-lg">
                    <span className="text-2xl">🏭</span>
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Estações Ativas</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.active_stations}</p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-yellow-100 rounded-lg">
                    <span className="text-2xl">📈</span>
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Nível Máximo</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.max_nivel?.toFixed(2)}m</p>
                  </div>
                </div>
              </div>

              <div className="bg-white rounded-lg shadow p-6">
                <div className="flex items-center">
                  <div className="p-2 bg-purple-100 rounded-lg">
                    <span className="text-2xl">💧</span>
                  </div>
                  <div className="ml-4">
                    <p className="text-sm font-medium text-gray-600">Vazão Máxima</p>
                    <p className="text-2xl font-bold text-gray-900">{stats.max_vazao?.toFixed(1)}m³/s</p>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Dados por Estação */}
          <div className="space-y-6">
            {Object.entries(groupedData).map(([stationCode, stationData]) => (
              <div key={stationCode} className="bg-white rounded-lg shadow">
                <div className="px-6 py-4 border-b border-gray-200">
                  <div className="flex items-center justify-between">
                    <div>
                      <h3 className="text-lg font-semibold text-gray-900">
                        {stationData.station.name}
                      </h3>
                      <p className="text-sm text-gray-600">{stationData.station.location}</p>
                    </div>
                    <div className="flex items-center space-x-4">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${getStatusColor(stationData.station.status)}`}>
                        {stationData.station.status}
                      </span>
                      <span className="text-sm text-gray-500">
                        {stationData.data.length} medições
                      </span>
                    </div>
                  </div>
                </div>

                <div className="px-6 py-4">
                  <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                      <thead className="bg-gray-50">
                        <tr>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data/Hora
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nível (m)
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Vazão (m³/s)
                          </th>
                          <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Chuva (mm)
                          </th>
                        </tr>
                      </thead>
                      <tbody className="bg-white divide-y divide-gray-200">
                        {stationData.data.slice(0, 10).map((data) => (
                          <tr key={data.id} className="hover:bg-gray-50">
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {formatTime(data.data_medicao)}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {data.nivel?.toFixed(2) || 'N/A'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {data.vazao?.toFixed(1) || 'N/A'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                              {data.chuva?.toFixed(1) || 'N/A'}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Footer */}
          <footer className="mt-12 text-center text-gray-500 text-sm">
            <p>🌊 Monitor Rio Piracicaba - Sistema de monitoramento hidrológico em tempo real</p>
            <p>Desenvolvido com Laravel + React + API da ANA</p>
          </footer>
        </div>
      </div>
    </>
  );
}
