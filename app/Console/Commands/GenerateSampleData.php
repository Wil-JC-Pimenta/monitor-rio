<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Station;
use App\Models\RiverData;
use Carbon\Carbon;

class GenerateSampleData extends Command
{
    protected $signature = 'data:generate {--count=100 : Number of records to generate}';
    protected $description = 'Generate sample river data for testing';

    public function handle()
    {
        $this->info('🌊 Generating sample data...');
        
        $count = $this->option('count');
        
        // Create sample stations if they don't exist
        $stations = [
            [
                'code' => 'PIR001',
                'name' => 'Rio Piracicaba - Vale do Aço',
                'location' => 'Ipatinga, MG',
                'latitude' => -19.4708,
                'longitude' => -42.5369,
                'status' => 'active'
            ],
            [
                'code' => 'PIR002',
                'name' => 'Rio Piracicaba - Timóteo',
                'location' => 'Timóteo, MG',
                'latitude' => -19.5833,
                'longitude' => -42.6333,
                'status' => 'active'
            ],
            [
                'code' => 'PIR003',
                'name' => 'Rio Piracicaba - Coronel Fabriciano',
                'location' => 'Coronel Fabriciano, MG',
                'latitude' => -19.5167,
                'longitude' => -42.6167,
                'status' => 'active'
            ]
        ];
        
        foreach ($stations as $stationData) {
            Station::updateOrCreate(
                ['code' => $stationData['code']],
                $stationData
            );
        }
        
        $this->info("✅ Created/updated " . count($stations) . " stations");
        
        // Generate sample data
        $stations = Station::all();
        $recordsCreated = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $station = $stations->random();
            $date = now()->subHours(rand(0, 168)); // Last week
            
            // Generate realistic river data
            $baseNivel = 1.5 + (sin($date->hour * 0.26) * 0.5); // Daily variation
            $nivel = $baseNivel + (rand(-20, 20) / 100); // Add some randomness
            $nivel = max(0.5, min(4.0, $nivel)); // Keep within realistic bounds
            
            $vazao = $nivel * 10 + rand(-5, 5); // Flow related to level
            $vazao = max(5, $vazao); // Minimum flow
            
            $chuva = rand(0, 100) < 30 ? rand(1, 15) : 0; // 30% chance of rain
            
            RiverData::create([
                'station_id' => $station->id,
                'nivel' => round($nivel, 2),
                'vazao' => round($vazao, 1),
                'chuva' => $chuva,
                'data_medicao' => $date
            ]);
            
            $recordsCreated++;
        }
        
        $this->info("✅ Generated {$recordsCreated} sample records");
        
        // Update station last_measurement
        foreach ($stations as $station) {
            $lastData = RiverData::where('station_id', $station->id)
                ->orderBy('data_medicao', 'desc')
                ->first();
            
            if ($lastData) {
                $station->update(['last_measurement' => $lastData->data_medicao]);
            }
        }
        
        $this->info('🎉 Sample data generation completed!');
        
        return 0;
    }
}

