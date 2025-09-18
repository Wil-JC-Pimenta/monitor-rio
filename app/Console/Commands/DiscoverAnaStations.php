<?php

namespace App\Console\Commands;

use App\Services\AnaApiService;
use Illuminate\Console\Command;

class DiscoverAnaStations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ana:discover-stations {--region=MG : Região para buscar estações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descobre estações reais da ANA para usar no sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Descobrindo estações da ANA...');

        $anaService = new AnaApiService();

        try {
            // Buscar todas as estações
            $this->info('📡 Conectando com a API da ANA...');
            $stations = $anaService->fetchStations();

            if (!$stations) {
                $this->error('❌ Não foi possível conectar com a API da ANA');
                return Command::FAILURE;
            }

            $this->info('✅ Conectado com sucesso!');

            // Filtrar estações por região
            $region = $this->option('region');
            $filteredStations = [];

            if (isset($stations['Estacoes']) && is_array($stations['Estacoes'])) {
                foreach ($stations['Estacoes'] as $station) {
                    if (isset($station['Estado']) && $station['Estado'] === $region) {
                        $filteredStations[] = $station;
                    }
                }
            }

            $this->info("📊 Encontradas " . count($filteredStations) . " estações em {$region}");

            // Mostrar algumas estações como exemplo
            $this->info("\n📋 Primeiras 10 estações encontradas:");
            $this->table(
                ['Código', 'Nome', 'Município', 'Estado', 'Rio'],
                array_slice(array_map(function($station) {
                    return [
                        $station['Codigo'] ?? 'N/A',
                        $station['Nome'] ?? 'N/A',
                        $station['Municipio'] ?? 'N/A',
                        $station['Estado'] ?? 'N/A',
                        $station['Rio'] ?? 'N/A',
                    ];
                }, $filteredStations), 0, 10)
            );

            // Buscar estações do Rio Piracicaba especificamente
            $this->info("\n🌊 Buscando estações do Rio Piracicaba...");
            $piracicabaStations = $anaService->fetchPiracicabaStations();

            if (!empty($piracicabaStations)) {
                $this->info("✅ Encontradas " . count($piracicabaStations) . " estações do Rio Piracicaba:");
                $this->table(
                    ['Código', 'Nome', 'Município', 'Estado'],
                    array_map(function($station) {
                        return [
                            $station['Codigo'] ?? 'N/A',
                            $station['Nome'] ?? 'N/A',
                            $station['Municipio'] ?? 'N/A',
                            $station['Estado'] ?? 'N/A',
                        ];
                    }, $piracicabaStations)
                );

                // Sugerir códigos para usar
                $codes = array_column($piracicabaStations, 'Codigo');
                $this->info("\n💡 Sugestão de códigos para configurar:");
                $this->info("PIRACICABA_STATIONS=" . implode(',', array_slice($codes, 0, 5)));
            } else {
                $this->warn("⚠️  Nenhuma estação do Rio Piracicaba encontrada");
                $this->info("💡 Usando primeiras 5 estações de MG como exemplo:");
                $codes = array_column(array_slice($filteredStations, 0, 5), 'Codigo');
                $this->info("PIRACICABA_STATIONS=" . implode(',', $codes));
            }

        } catch (\Exception $e) {
            $this->error("❌ Erro: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

