<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnaApiService;
use App\Models\Station;
use App\Models\RiverData;
use Carbon\Carbon;

class FetchAnaData extends Command
{
    protected $signature = 'ana:fetch {--hours=24 : Number of hours to fetch data for}';
    protected $description = 'Fetch river data from ANA API and store in database';

    public function handle()
    {
        $this->info('🌊 Starting ANA data fetch...');
        
        try {
            $anaService = new AnaApiService();
            $hours = $this->option('hours');
            
            // Fetch stations first
            $this->info('📡 Fetching stations...');
            $stationsData = $anaService->fetchStations();
            
            if (!$stationsData) {
                $this->error('Failed to fetch stations from ANA API');
                return 1;
            }
            
            // Get or create stations in database
            $stations = collect();
            if (isset($stationsData['Estacoes'])) {
                foreach ($stationsData['Estacoes'] as $stationData) {
                    $station = Station::updateOrCreate(
                        ['code' => $stationData['Codigo']],
                        [
                            'name' => $stationData['Nome'] ?? 'Unknown',
                            'location' => $stationData['Municipio'] ?? 'Unknown',
                            'latitude' => $stationData['Latitude'] ?? null,
                            'longitude' => $stationData['Longitude'] ?? null,
                            'status' => 'active'
                        ]
                    );
                    $stations->push($station);
                }
            }
            
            $this->info("Found {$stations->count()} stations");
            
            // Fetch data for each station
            $totalData = 0;
            foreach ($stations as $station) {
                $this->info("📊 Fetching data for station: {$station->name}");
                
                try {
                    $data = $anaService->fetchStationData($station->code, $hours);
                    $count = $data->count();
                    $totalData += $count;
                    
                    $this->info("  ✅ Fetched {$count} records");
                    
                    // Store data in database
                    foreach ($data as $record) {
                        RiverData::updateOrCreate(
                            [
                                'station_id' => $station->id,
                                'data_medicao' => $record['data_medicao']
                            ],
                            [
                                'nivel' => $record['nivel'],
                                'vazao' => $record['vazao'],
                                'chuva' => $record['chuva']
                            ]
                        );
                    }
                    
                } catch (\Exception $e) {
                    $this->error("  ❌ Error fetching data for {$station->name}: " . $e->getMessage());
                }
            }
            
            $this->info("🎉 Successfully fetched {$totalData} total records");
            
            // Update station last_measurement
            foreach ($stations as $station) {
                $lastData = RiverData::where('station_id', $station->id)
                    ->orderBy('data_medicao', 'desc')
                    ->first();
                
                if ($lastData) {
                    $station->update(['last_measurement' => $lastData->data_medicao]);
                }
            }
            
            $this->info('✅ Data fetch completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('❌ Error fetching data: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
