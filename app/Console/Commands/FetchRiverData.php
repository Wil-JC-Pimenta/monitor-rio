<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\RiverData;

class FetchRiverData extends Command
{
    protected $signature = 'river:fetch';
    protected $description = 'Busca o nível do Rio Piracicaba (ANA) e salva no banco';

    public function handle()
    {
        $stationId = '56690000'; // código da estação (exemplo)
        $this->info("Buscando dados da estação $stationId...");

        // Exemplo de chamada HTTP à API da ANA (substitua URL e parâmetros reais)
        $response = Http::get("http://api.ana.gov.br/.../dados", [
            'codEstacao' => $stationId,
            'dataInicio' => now()->subDay()->format('d/m/Y'),
            'dataFim'    => now()->format('d/m/Y')
        ]);

        if ($response->successful()) {
            $dados = $response->json(); // use xml() se o retorno for XML
            foreach ($dados as $registro) {
                RiverData::create([
                    'station_id'   => $stationId,
                    'nivel'        => $registro['Nivel'] ?? null,
                    'vazao'        => $registro['Vazao'] ?? null,
                    'chuva'        => $registro['Chuva'] ?? null,
                    'data_medicao' => $registro['DataHora'] ?? now()
                ]);
            }

            $this->info('Dados do rio atualizados com sucesso!');
        } else {
            $this->error('Erro ao buscar dados da ANA');
        }
    }
}

