<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;
use App\Models\RiverData;
use Carbon\Carbon;

class MockDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌊 Gerando dados mock para o sistema de monitoramento...');

        $stations = [
            [
                'code' => 'RDV001',
                'name' => 'Rio das Velhas - BH',
                'location' => 'Belo Horizonte, MG',
                'latitude' => -19.9167,
                'longitude' => -43.9345,
                'description' => 'Estação de monitoramento do Rio das Velhas em Belo Horizonte',
                'status' => 'active',
            ],
            [
                'code' => 'RSF001',
                'name' => 'Rio São Francisco - Pirapora',
                'location' => 'Pirapora, MG',
                'latitude' => -17.3450,
                'longitude' => -44.9419,
                'description' => 'Estação de monitoramento do Rio São Francisco em Pirapora',
                'status' => 'active',
            ],
            [
                'code' => 'RD001',
                'name' => 'Rio Doce - GV',
                'location' => 'Governador Valadares, MG',
                'latitude' => -18.8511,
                'longitude' => -41.9494,
                'description' => 'Estação de monitoramento do Rio Doce em Governador Valadares',
                'status' => 'active',
            ],
            [
                'code' => 'RPS001',
                'name' => 'Rio Paraíba do Sul - JF',
                'location' => 'Juiz de Fora, MG',
                'latitude' => -21.7595,
                'longitude' => -43.3398,
                'description' => 'Estação de monitoramento do Rio Paraíba do Sul em Juiz de Fora',
                'status' => 'maintenance',
            ],
            [
                'code' => 'RG001',
                'name' => 'Rio Grande - Divinópolis',
                'location' => 'Divinópolis, MG',
                'latitude' => -20.1394,
                'longitude' => -44.8889,
                'description' => 'Estação de monitoramento do Rio Grande em Divinópolis',
                'status' => 'active',
            ],
        ];

        foreach ($stations as $stationData) {
            $station = Station::create($stationData);
            $this->command->info("✅ Estação criada: {$station->name}");

            // Gerar dados históricos para os últimos 7 dias
            $this->generateHistoricalData($station);
        }

        $this->command->info('🎉 Dados mock gerados com sucesso!');
        $this->command->line('💡 Execute: php artisan river:fetch --mock para atualizar com dados recentes');
    }

    private function generateHistoricalData(Station $station): void
    {
        $startDate = Carbon::now()->subDays(7);
        $endDate = Carbon::now();

        $currentDate = $startDate->copy();
        $dataCount = 0;

        while ($currentDate->lte($endDate)) {
            // Gerar dados a cada 2 horas
            for ($hour = 0; $hour < 24; $hour += 2) {
                $measurementTime = $currentDate->copy()->addHours($hour);
                
                // Simular variações realistas baseadas no horário e estação
                $baseNivel = $this->getBaseLevel($station->code, $hour);
                $baseVazao = $this->getBaseFlow($station->code, $hour);
                $baseChuva = $this->getBaseRain($hour);

                // Adicionar variações aleatórias
                $nivel = $baseNivel + (rand(-15, 15) / 100);
                $vazao = $baseVazao + rand(-25, 25);
                $chuva = max(0, $baseChuva + rand(-2, 2));

                RiverData::create([
                    'station_id' => $station->id,
                    'nivel' => round($nivel, 2),
                    'vazao' => round($vazao, 1),
                    'chuva' => $chuva,
                    'data_medicao' => $measurementTime,
                ]);

                $dataCount++;
            }

            $currentDate->addDay();
        }

        // Atualizar última medição da estação
        $station->update(['last_measurement' => $endDate]);

        $this->command->line("   📊 {$dataCount} registros gerados para {$station->name}");
    }

    private function getBaseLevel(string $stationCode, int $hour): float
    {
        $baseLevels = [
            'RDV001' => 2.5, // Rio das Velhas
            'RSF001' => 1.8, // São Francisco
            'RD001' => 3.2,  // Rio Doce
            'RPS001' => 2.1, // Paraíba do Sul
            'RG001' => 2.8,  // Rio Grande
        ];

        $base = $baseLevels[$stationCode] ?? 2.5;
        
        // Simular variação diurna (mais baixo durante a madrugada)
        $diurnalVariation = sin(($hour - 6) * M_PI / 12) * 0.3;
        
        return $base + $diurnalVariation;
    }

    private function getBaseFlow(string $stationCode, int $hour): float
    {
        $baseFlows = [
            'RDV001' => 120, // Rio das Velhas
            'RSF001' => 200, // São Francisco
            'RD001' => 180,  // Rio Doce
            'RPS001' => 150, // Paraíba do Sul
            'RG001' => 160,  // Rio Grande
        ];

        $base = $baseFlows[$stationCode] ?? 150;
        
        // Simular variação diurna
        $diurnalVariation = sin(($hour - 6) * M_PI / 12) * 20;
        
        return $base + $diurnalVariation;
    }

    private function getBaseRain(int $hour): float
    {
        // Simular chuva mais comum durante a tarde/noite
        if ($hour >= 14 && $hour <= 22) {
            return rand(0, 8);
        }
        
        return rand(0, 2);
    }
}
