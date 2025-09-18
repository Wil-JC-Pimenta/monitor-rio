<?php

namespace App\Console\Commands;

use App\Services\AnaApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchRiverData extends Command
{
    protected $signature = 'river:fetch 
                            {--mock : Use mock data instead of real API}
                            {--station= : Specific station code to fetch}
                            {--days=7 : Number of days to fetch data for}
                            {--type=niveis : Type of data (niveis, vazoes, chuvas)}';
    
    protected $description = 'Busca dados hidrológicos da ANA e salva no banco de dados';

    private AnaApiService $anaService;

    public function __construct(AnaApiService $anaService)
    {
        parent::__construct();
        $this->anaService = $anaService;
    }

    public function handle()
    {
        $this->info('🌊 Iniciando busca de dados hidrológicos...');

        if ($this->option('mock')) {
            $this->info('📊 Usando dados mock para demonstração...');
            $this->generateMockData();
        } else {
            $this->fetchRealData();
        }
    }

    private function generateMockData()
    {
        $this->info('📊 Gerando dados mock para demonstração...');
        
        // Gerar dados mock diretamente
        $stations = [
            ['code' => 'PIR001', 'name' => 'Rio Piracicaba - Ipatinga'],
            ['code' => 'PIR002', 'name' => 'Rio Piracicaba - Timóteo'],
            ['code' => 'PIR003', 'name' => 'Rio Piracicaba - Coronel Fabriciano'],
        ];

        foreach ($stations as $stationData) {
            // Criar ou atualizar estação
            $station = \App\Models\Station::firstOrCreate(
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
                
                // Simular variações realistas do Rio Piracicaba
                $baseNivel = 2.5 + (sin($i * 0.3) * 0.5);
                $baseVazao = 120 + (cos($i * 0.2) * 30);
                $baseChuva = $i < 8 ? rand(0, 3) : 0; // Chuva simulada nas primeiras 8 horas

                \App\Models\RiverData::create([
                    'station_id' => $station->id,
                    'nivel' => round($baseNivel + (rand(-15, 15) / 100), 2),
                    'vazao' => round($baseVazao + (rand(-20, 20)), 1),
                    'chuva' => $baseChuva,
                    'data_medicao' => $measurementTime,
                ]);
            }

            $this->info("✅ Dados mock gerados para: {$station->name}");
        }
        
        $this->info('🎉 Dados mock gerados com sucesso!');
        $this->line('💡 Para usar dados reais da ANA, execute: php artisan river:fetch');
    }

    private function fetchRealData()
    {
        $days = (int) $this->option('days');
        $dataType = $this->option('type');
        $specificStation = $this->option('station');
        
        $startDate = now()->subDays($days);
        $endDate = now();

        $this->info("📅 Período: {$startDate->format('d/m/Y')} a {$endDate->format('d/m/Y')}");
        $this->info("📊 Tipo de dados: {$dataType}");

        try {
            if ($specificStation) {
                $this->fetchSpecificStation($specificStation, $startDate, $endDate, $dataType);
            } else {
                $this->fetchAllPiracicabaStations($startDate, $endDate, $dataType);
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro na busca de dados: " . $e->getMessage());
            Log::error("Erro no comando FetchRiverData: " . $e->getMessage());
            
            $this->line('💡 Use --mock para dados simulados ou verifique a configuração da API');
        }
    }

    private function fetchSpecificStation(string $stationCode, Carbon $startDate, Carbon $endDate, string $dataType): void
    {
        $this->info("🔍 Buscando dados da estação: {$stationCode}");

        $data = $this->anaService->fetchStationData($stationCode, $startDate, $endDate, $dataType);

        if ($data) {
            $savedCount = $this->anaService->saveDataToDatabase($data, $stationCode);
            $this->info("✅ Salvos {$savedCount} registros para a estação {$stationCode}");
        } else {
            $this->warn("⚠️ Nenhum dado encontrado para a estação {$stationCode}");
        }
    }

    private function fetchAllPiracicabaStations(Carbon $startDate, Carbon $endDate, string $dataType): void
    {
        $this->info("🌊 Buscando dados de todas as estações do Rio Piracicaba...");

        $stations = config('ana.stations.piracicaba.codes');
        $totalSaved = 0;

        foreach ($stations as $stationCode) {
            $this->line("🔍 Processando estação: {$stationCode}");
            
            try {
                $data = $this->anaService->fetchStationData($stationCode, $startDate, $endDate, $dataType);
                
                if ($data) {
                    $savedCount = $this->anaService->saveDataToDatabase($data, $stationCode);
                    $totalSaved += $savedCount;
                    $this->info("  ✅ {$savedCount} registros salvos");
                } else {
                    $this->warn("  ⚠️ Nenhum dado encontrado");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Erro: " . $e->getMessage());
            }
        }

        $this->info("🎉 Processamento concluído! Total de registros salvos: {$totalSaved}");
    }
}

