<?php

namespace App\Console\Commands;

use App\Services\AnaApiService;
use Illuminate\Console\Command;

class TestAnaApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ana:test {--station= : Código da estação para testar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa a conexão com a API da ANA';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Testando conexão com a API da ANA...');

        $anaService = new AnaApiService();

        try {
            // Teste 1: Buscar estações
            $this->info('📡 Testando busca de estações...');
            $stations = $anaService->fetchStations();

            if ($stations) {
                $this->info('✅ Conexão com API estabelecida com sucesso!');
                $this->info('📊 Estrutura da resposta:');
                $this->line(json_encode($stations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->warn('⚠️  Nenhuma estação encontrada');
            }

            // Teste 2: Buscar dados de uma estação específica
            $stationCode = $this->option('station') ?? '56690000';
            $this->info("\n📊 Testando busca de dados para estação {$stationCode}...");

            $data = $anaService->fetchStationData($stationCode, now()->subDays(7), now(), 'niveis');

            if ($data && !empty($data)) {
                $this->info("✅ Dados obtidos com sucesso! ({count($data)} registros)");
                $this->info('📋 Primeiro registro:');
                $this->line(json_encode($data[0], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->warn('⚠️  Nenhum dado encontrado para esta estação');
            }

        } catch (\Exception $e) {
            $this->error('❌ Erro ao testar API: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("\n🎉 Teste concluído!");
        return Command::SUCCESS;
    }
}

