<?php

namespace App\Console\Commands;

use App\Services\AnaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchAnaRealData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ana:fetch-real {--clear : Limpar dados existentes antes de buscar novos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca dados reais da API da ANA para substituir dados mockados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌊 Buscando dados reais da API da ANA...');

        $anaService = new AnaApiService();

        // Limpar dados existentes se solicitado
        if ($this->option('clear')) {
            $this->info('🗑️  Limpando dados existentes...');
            DB::table('river_data')->delete();
            DB::table('stations')->delete();
            $this->info('✅ Dados limpos com sucesso');
        }

        // Buscar estações do Rio Piracicaba
        $this->info('🔍 Buscando estações do Rio Piracicaba...');
        $stations = $anaService->fetchPiracicabaStations();

        if (empty($stations)) {
            $this->warn('⚠️  Nenhuma estação do Rio Piracicaba encontrada. Usando estações configuradas...');
            $stationCodes = config('ana.stations.piracicaba.codes');
        } else {
            $this->info("✅ Encontradas " . count($stations) . " estações do Rio Piracicaba");
            $stationCodes = array_column($stations, 'Codigo');
        }

        $totalRecords = 0;

        // Buscar dados para cada estação
        foreach ($stationCodes as $stationCode) {
            $this->info("📊 Buscando dados para estação {$stationCode}...");

            try {
                // Buscar dados de níveis
                $nivelData = $anaService->fetchStationData($stationCode, null, null, 'niveis');
                if ($nivelData) {
                    $saved = $anaService->saveDataToDatabase($nivelData, $stationCode);
                    $totalRecords += $saved;
                    $this->info("   ✅ Salvos {$saved} registros de níveis");
                }

                // Buscar dados de vazão
                $vazaoData = $anaService->fetchStationData($stationCode, null, null, 'vazoes');
                if ($vazaoData) {
                    $saved = $anaService->saveDataToDatabase($vazaoData, $stationCode);
                    $totalRecords += $saved;
                    $this->info("   ✅ Salvos {$saved} registros de vazão");
                }

                // Buscar dados de chuva
                $chuvaData = $anaService->fetchStationData($stationCode, null, null, 'chuvas');
                if ($chuvaData) {
                    $saved = $anaService->saveDataToDatabase($chuvaData, $stationCode);
                    $totalRecords += $saved;
                    $this->info("   ✅ Salvos {$saved} registros de chuva");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erro ao buscar dados da estação {$stationCode}: " . $e->getMessage());
            }
        }

        $this->info("🎉 Processo concluído! Total de registros salvos: {$totalRecords}");

        // Mostrar estatísticas finais
        $stationCount = DB::table('stations')->count();
        $dataCount = DB::table('river_data')->count();

        $this->info("📈 Estatísticas finais:");
        $this->info("   - Estações: {$stationCount}");
        $this->info("   - Registros de dados: {$dataCount}");

        return Command::SUCCESS;
    }
}

