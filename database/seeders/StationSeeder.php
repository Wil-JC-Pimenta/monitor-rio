<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            [
                'name' => 'Estação Rio das Velhas - BH',
                'code' => 'RDV001',
                'location' => 'Belo Horizonte, MG',
                'latitude' => -19.9167,
                'longitude' => -43.9345,
                'description' => 'Estação principal de monitoramento do Rio das Velhas na região metropolitana de Belo Horizonte',
                'status' => 'active',
            ],
            [
                'name' => 'Estação Rio São Francisco - Pirapora',
                'code' => 'RSF001',
                'location' => 'Pirapora, MG',
                'latitude' => -17.3447,
                'longitude' => -44.9333,
                'description' => 'Estação de monitoramento do Rio São Francisco em Pirapora',
                'status' => 'active',
            ],
            [
                'name' => 'Estação Rio Doce - Governador Valadares',
                'code' => 'RD001',
                'location' => 'Governador Valadares, MG',
                'latitude' => -18.8511,
                'longitude' => -41.9494,
                'description' => 'Estação de monitoramento do Rio Doce',
                'status' => 'active',
            ],
            [
                'name' => 'Estação Rio Paraíba do Sul - Juiz de Fora',
                'code' => 'RPS001',
                'location' => 'Juiz de Fora, MG',
                'latitude' => -21.7645,
                'longitude' => -43.3492,
                'description' => 'Estação de monitoramento do Rio Paraíba do Sul',
                'status' => 'maintenance',
            ],
            [
                'name' => 'Estação Rio Grande - Divinópolis',
                'code' => 'RG001',
                'location' => 'Divinópolis, MG',
                'latitude' => -20.1446,
                'longitude' => -44.8936,
                'description' => 'Estação de monitoramento do Rio Grande',
                'status' => 'active',
            ],
        ];

        foreach ($stations as $station) {
            Station::create($station);
        }
    }
}
