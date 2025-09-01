<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\RiverData;
use App\Models\Station;

class FetchRiverData extends Command
{
    protected $signature = 'river:fetch {--mock : Use mock data instead of real API}';
    protected $description = 'Busca dados hidrológicos e salva no banco (com suporte a dados mock)';

    public function handle()
    {
        if ($this->option('mock') || config('app.use_mock_data', true)) {
            $this->info('Usando dados mock (aguardando liberação das chaves da API)...');
            $this->generateMockData();
        } else {
            $this->fetchRealData();
        }
    }

    private function generateMockData()
    {
        $stations = [
            ['code' => 'RDV001', 'name' => 'Rio das Velhas - BH'],
            ['code' => 'RSF001', 'name' => 'Rio São Francisco - Pirapora'],
            ['code' => 'RD001', 'name' => 'Rio Doce - GV'],
            ['code' => 'RPS001', 'name' => 'Rio Paraíba do Sul - JF'],
            ['code' => 'RG001', 'name' => 'Rio Grande - Divinópolis'],
        ];

        foreach ($stations as $stationData) {
            // Criar ou atualizar estação
            $station = Station::firstOrCreate(
                ['code' => $stationData['code']],
                [
                    'name' => $stationData['name'],
                    'location' => $stationData['name'],
                    'status' => 'active',
                    'last_measurement' => now(),
                ]
            );

            // Gerar dados mock para as últimas 24 horas
            for ($i = 0; $i < 24; $i++) {
                $measurementTime = now()->subHours($i);
                
                // Simular variações realistas
                $baseNivel = 2.0 + (sin($i * 0.5) * 0.8);
                $baseVazao = 150 + (cos($i * 0.3) * 50);
                $baseChuva = $i < 6 ? rand(0, 5) : 0; // Chuva simulada nas primeiras 6 horas

                RiverData::create([
                    'station_id' => $station->id,
                    'nivel' => round($baseNivel + (rand(-20, 20) / 100), 2),
                    'vazao' => round($baseVazao + (rand(-30, 30)), 1),
                    'chuva' => $baseChuva,
                    'data_medicao' => $measurementTime,
                ]);
            }

            $this->info("Dados mock gerados para estação: {$station->name}");
        }

        $this->info('✅ Dados mock gerados com sucesso!');
        $this->line('💡 Para usar dados reais, execute: php artisan river:fetch --no-mock');
    }

    private function fetchRealData()
    {
        $stationId = config('app.ana_station_id', '56690000');
        $apiUrl = config('app.ana_api_url', 'http://api.ana.gov.br');
        $apiKey = config('app.ana_api_key');

        if (!$apiKey) {
            $this->error('❌ Chave da API da ANA não configurada!');
            $this->line('💡 Configure ANA_API_KEY no arquivo .env ou use --mock para dados simulados');
            return;
        }

        $this->info("Buscando dados da estação $stationId...");

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->get("$apiUrl/dados", [
                'codEstacao' => $stationId,
                'dataInicio' => now()->subDay()->format('d/m/Y'),
                'dataFim' => now()->format('d/m/Y')
            ]);

            if ($response->successful()) {
                $dados = $response->json();
                
                if (empty($dados)) {
                    $this->warn('Nenhum dado retornado pela API');
                    return;
                }

                foreach ($dados as $registro) {
                    RiverData::create([
                        'station_id' => $stationId,
                        'nivel' => $registro['Nivel'] ?? null,
                        'vazao' => $registro['Vazao'] ?? null,
                        'chuva' => $registro['Chuva'] ?? null,
                        'data_medicao' => $registro['DataHora'] ?? now()
                    ]);
                }

                $this->info('✅ Dados do rio atualizados com sucesso!');
            } else {
                $this->error('❌ Erro ao buscar dados da ANA: ' . $response->status());
                $this->line('💡 Use --mock para dados simulados enquanto aguarda a liberação das chaves');
            }
        } catch (\Exception $e) {
            $this->error('❌ Erro na requisição: ' . $e->getMessage());
            $this->line('💡 Use --mock para dados simulados');
        }
    }
}

