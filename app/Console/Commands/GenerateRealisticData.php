<?php

namespace App\Console\Commands;

use App\Models\RiverData;
use App\Models\Station;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateRealisticData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ana:generate-realistic {--days=30 : Número de dias de dados para gerar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera dados realistas simulando a API da ANA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("🌊 Gerando dados realistas para {$days} dias...");

        // Limpar dados existentes
        RiverData::truncate();
        Station::truncate();

        // Criar estações do Rio Piracicaba
        $stations = [
            [
                'name' => 'Rio Piracicaba - Ipatinga',
                'code' => 'PIR001',
                'location' => 'Vale do Aço - MG',
                'latitude' => -19.4708,
                'longitude' => -42.5369,
                'description' => 'Estação principal do Rio Piracicaba em Ipatinga',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Piracicaba - Timóteo',
                'code' => 'PIR002',
                'location' => 'Vale do Aço - MG',
                'latitude' => -19.5831,
                'longitude' => -42.6442,
                'description' => 'Estação do Rio Piracicaba em Timóteo',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Piracicaba - Coronel Fabriciano',
                'code' => 'PIR003',
                'location' => 'Vale do Aço - MG',
                'latitude' => -19.5189,
                'longitude' => -42.6289,
                'description' => 'Estação do Rio Piracicaba em Coronel Fabriciano',
                'status' => 'active',
            ],
            [
                'name' => 'Rio das Velhas - BH',
                'code' => 'RDV001',
                'location' => 'Belo Horizonte, MG',
                'latitude' => -19.9167,
                'longitude' => -43.9345,
                'description' => 'Estação de monitoramento do Rio das Velhas em Belo Horizonte',
                'status' => 'active',
            ],
            [
                'name' => 'Rio São Francisco - Pirapora',
                'code' => 'RSF001',
                'location' => 'Pirapora, MG',
                'latitude' => -17.345,
                'longitude' => -44.9419,
                'description' => 'Estação de monitoramento do Rio São Francisco em Pirapora',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Doce - Governador Valadares',
                'code' => 'RDO001',
                'location' => 'Governador Valadares, MG',
                'latitude' => -18.8500,
                'longitude' => -41.9500,
                'description' => 'Estação de monitoramento do Rio Doce em Governador Valadares',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Doce - Resplendor',
                'code' => 'RDO002',
                'location' => 'Resplendor, MG',
                'latitude' => -19.3167,
                'longitude' => -41.2500,
                'description' => 'Estação de monitoramento do Rio Doce em Resplendor',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Doce - Conselheiro Pena',
                'code' => 'RDO003',
                'location' => 'Conselheiro Pena, MG',
                'latitude' => -19.1667,
                'longitude' => -41.4667,
                'description' => 'Estação de monitoramento do Rio Doce em Conselheiro Pena',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Doce - Itueta',
                'code' => 'RDO004',
                'location' => 'Itueta, MG',
                'latitude' => -19.4000,
                'longitude' => -41.1667,
                'description' => 'Estação de monitoramento do Rio Doce em Itueta',
                'status' => 'active',
            ],
            [
                'name' => 'Rio Doce - Aimorés',
                'code' => 'RDO005',
                'location' => 'Aimorés, MG',
                'latitude' => -19.5000,
                'longitude' => -41.0667,
                'description' => 'Estação de monitoramento do Rio Doce em Aimorés',
                'status' => 'active',
            ],
        ];

        foreach ($stations as $stationData) {
            $station = Station::create($stationData);
            $this->info("✅ Estação criada: {$station->name}");

            // Gerar dados realistas para cada estação
            $this->generateStationData($station, $days);
        }

        $this->info("🎉 Dados realistas gerados com sucesso!");
        $this->info("📊 Total de estações: " . Station::count());
        $this->info("📊 Total de dados: " . RiverData::count());
    }

    private function generateStationData(Station $station, int $days)
    {
        $startDate = Carbon::now()->subDays($days);
        $endDate = Carbon::now();

        // Parâmetros baseados no tipo de estação
        $baseNivel = $this->getBaseNivel($station->code);
        $baseVazao = $this->getBaseVazao($station->code);
        $baseChuva = $this->getBaseChuva($station->code);

        $data = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            // Simular variações sazonais e diárias
            $hour = $currentDate->hour;
            $dayOfYear = $currentDate->dayOfYear;
            
            // Variação sazonal (mais chuva no verão)
            $seasonalFactor = 1 + 0.3 * sin(2 * pi() * $dayOfYear / 365);
            
            // Variação diária (mais chuva à tarde)
            $dailyFactor = 1 + 0.2 * sin(2 * pi() * $hour / 24);
            
            // Variação aleatória
            $randomFactor = 0.8 + (mt_rand(0, 40) / 100);

            // Calcular valores
            $nivel = $baseNivel * $seasonalFactor * $dailyFactor * $randomFactor;
            $vazao = $baseVazao * $seasonalFactor * $dailyFactor * $randomFactor;
            $chuva = $baseChuva * $seasonalFactor * $dailyFactor * $randomFactor;

            // Adicionar ruído realista
            $nivel += (mt_rand(-50, 50) / 1000); // ±0.05m
            $vazao += (mt_rand(-100, 100) / 100); // ±1.0 m³/s
            $chuva += (mt_rand(-20, 20) / 100); // ±0.2mm

            // Garantir valores mínimos
            $nivel = max(0.5, $nivel);
            $vazao = max(0, $vazao);
            $chuva = max(0, $chuva);

            $data[] = [
                'station_id' => $station->id,
                'nivel' => round($nivel, 2),
                'vazao' => round($vazao, 1),
                'chuva' => round($chuva, 1),
                'data_medicao' => $currentDate->copy(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $currentDate->addHour();
        }

        // Inserir em lotes para melhor performance
        RiverData::insert($data);
        
        $this->info("   📊 " . count($data) . " registros gerados para {$station->name}");
    }

    private function getBaseNivel(string $code): float
    {
        return match($code) {
            'PIR001', 'PIR002', 'PIR003' => 2.5, // Rio Piracicaba
            'RDV001' => 2.3, // Rio das Velhas
            'RSF001' => 3.0, // Rio São Francisco
            default => 2.0,
        };
    }

    private function getBaseVazao(string $code): float
    {
        return match($code) {
            'PIR001', 'PIR002', 'PIR003' => 15.0, // Rio Piracicaba
            'RDV001' => 12.0, // Rio das Velhas
            'RSF001' => 25.0, // Rio São Francisco
            default => 10.0,
        };
    }

    private function getBaseChuva(string $code): float
    {
        return match($code) {
            'PIR001', 'PIR002', 'PIR003' => 1.5, // Rio Piracicaba
            'RDV001' => 1.2, // Rio das Velhas
            'RSF001' => 0.8, // Rio São Francisco
            default => 1.0,
        };
    }
}

