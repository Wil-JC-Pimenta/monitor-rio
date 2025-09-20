<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AnaApiService;
use App\Models\Station;
use App\Models\RiverData;
use Carbon\Carbon;

class UpdateHourlyData extends Command
{
    protected $signature = 'data:update-hourly';
    protected $description = 'Atualiza dados hidrológicos a cada hora';

    public function handle()
    {
        $this->info('Iniciando atualização de dados por hora...');
        
        try {
            $anaService = new AnaApiService();
            $stations = Station::all();
            
            $totalRecords = 0;
            
            foreach ($stations as $station) {
                $this->info("Atualizando dados para estação: {$station->name}");
                
                // Buscar dados da última hora
                $lastUpdate = RiverData::where('station_id', $station->id)
                    ->orderBy('data_medicao', 'desc')
                    ->first();
                
                $startDate = $lastUpdate 
                    ? Carbon::parse($lastUpdate->data_medicao)->addHour()
                    : Carbon::now()->subDay();
                
                $endDate = Carbon::now();
                
                // Buscar dados da API ANA
                $data = $anaService->fetchStationData($station->code, $startDate, $endDate);
                
                if ($data && count($data) > 0) {
                    foreach ($data as $record) {
                        RiverData::updateOrCreate(
                            [
                                'station_id' => $station->id,
                                'data_medicao' => $record['data_medicao']
                            ],
                            $record
                        );
                    }
                    
                    $totalRecords += count($data);
                    $this->info("Salvos " . count($data) . " registros para {$station->name}");
                } else {
                    $this->warn("Nenhum dado encontrado para {$station->name}");
                }
            }
            
            $this->info("Atualização concluída! Total de registros: {$totalRecords}");
            
        } catch (\Exception $e) {
            $this->error("Erro na atualização: " . $e->getMessage());
            // Não usar logs por enquanto devido a problemas de permissão
            return 1;
        }
        
        return 0;
    }
}
