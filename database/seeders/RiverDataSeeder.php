<?php

namespace Database\Seeders;

use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RiverDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = Station::all();
        
        if ($stations->isEmpty()) {
            $this->command->info('Nenhuma estação encontrada. Execute o StationSeeder primeiro.');
            return;
        }

        // Gerar dados para os últimos 30 dias
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            foreach ($stations as $station) {
                // Gerar múltiplas medições por dia (a cada 6 horas)
                for ($hour = 0; $hour < 24; $hour += 6) {
                    $measurementTime = $date->copy()->addHours($hour);
                    
                    // Simular variações sazonais e aleatórias
                    $baseLevel = 2.5 + (sin($i * 0.2) * 0.5) + (rand(-10, 10) / 100);
                    $baseFlow = 150 + (sin($i * 0.15) * 30) + (rand(-20, 20));
                    $baseRain = max(0, rand(0, 15) / 10); // Chuva ocasional
                    
                    // Adicionar variações por estação
                    if ($station->code === 'RDV001') {
                        $baseLevel += 0.3; // Rio das Velhas tem nível mais alto
                        $baseFlow += 50;
                    } elseif ($station->code === 'RSF001') {
                        $baseLevel += 0.8; // São Francisco é mais profundo
                        $baseFlow += 200;
                    }
                    
                    RiverData::create([
                        'station_id' => $station->id,
                        'nivel' => round($baseLevel, 3),
                        'vazao' => round($baseFlow, 3),
                        'chuva' => round($baseRain, 2),
                        'data_medicao' => $measurementTime,
                    ]);
                }
            }
        }

        // Atualizar last_measurement das estações
        foreach ($stations as $station) {
            $latestData = $station->riverData()->latest('data_medicao')->first();
            if ($latestData) {
                $station->update(['last_measurement' => $latestData->data_medicao]);
            }
        }

        $this->command->info('Dados do rio criados com sucesso!');
    }
}
