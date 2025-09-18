<?php

namespace App\Services;

use App\Models\RiverData;
use App\Models\Station;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnaApiService
{
    private string $baseUrl;
    private int $timeout;
    private int $retryAttempts;
    private int $retryDelay;
    private bool $cacheEnabled;
    private int $cacheTtl;

    public function __construct()
    {
        $this->baseUrl = config('ana.base_url');
        $this->timeout = config('ana.timeout');
        $this->retryAttempts = config('ana.retry_attempts');
        $this->retryDelay = config('ana.retry_delay');
        $this->cacheEnabled = config('ana.cache.enabled');
        $this->cacheTtl = config('ana.cache.ttl');
    }

    /**
     * Busca dados hidrológicos de uma estação específica
     *
     * @param string $stationCode Código da estação na ANA
     * @param Carbon|null $startDate Data de início (padrão: 7 dias atrás)
     * @param Carbon|null $endDate Data de fim (padrão: hoje)
     * @param string $dataType Tipo de dados (niveis, vazoes, chuvas)
     * @return array|null
     */
    public function fetchStationData(
        string $stationCode,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        string $dataType = 'niveis'
    ): ?array {
        $startDate = $startDate ?? now()->subDays(7);
        $endDate = $endDate ?? now();

        $cacheKey = $this->getCacheKey($stationCode, $startDate, $endDate, $dataType);

        // Verifica cache primeiro
        if ($this->cacheEnabled && Cache::has($cacheKey)) {
            Log::info("Dados da estação {$stationCode} obtidos do cache");
            return Cache::get($cacheKey);
        }

        try {
            $data = $this->makeApiRequest($stationCode, $startDate, $endDate, $dataType);
            
            if ($data) {
                // Salva no cache
                if ($this->cacheEnabled) {
                    Cache::put($cacheKey, $data, $this->cacheTtl);
                }
                
                Log::info("Dados da estação {$stationCode} obtidos com sucesso da API");
                return $data;
            }
        } catch (Exception $e) {
            Log::error("Erro ao buscar dados da estação {$stationCode}: " . $e->getMessage());
            
            // Tenta obter dados do cache em caso de erro
            if ($this->cacheEnabled && Cache::has($cacheKey)) {
                Log::warning("Usando dados em cache devido ao erro na API");
                return Cache::get($cacheKey);
            }
            
            throw $e;
        }

        return null;
    }

    /**
     * Busca dados de todas as estações do Rio Piracicaba
     *
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function fetchPiracicabaData(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $stations = config('ana.stations.piracicaba.codes');
        $results = [];

        foreach ($stations as $stationCode) {
            try {
                $data = $this->fetchStationData($stationCode, $startDate, $endDate);
                if ($data) {
                    $results[$stationCode] = $data;
                }
            } catch (Exception $e) {
                Log::error("Erro ao buscar dados da estação {$stationCode}: " . $e->getMessage());
                $results[$stationCode] = null;
            }
        }

        return $results;
    }

    /**
     * Salva dados no banco de dados
     *
     * @param array $apiData Dados da API
     * @param string $stationCode Código da estação
     * @return int Número de registros salvos
     */
    public function saveDataToDatabase(array $apiData, string $stationCode): int
    {
        $station = $this->getOrCreateStation($stationCode);
        $savedCount = 0;

        foreach ($apiData as $record) {
            try {
                $riverData = RiverData::create([
                    'station_id' => $station->id,
                    'nivel' => $record['nivel'] ?? null,
                    'vazao' => $record['vazao'] ?? null,
                    'chuva' => $record['chuva'] ?? null,
                    'data_medicao' => Carbon::parse($record['data_medicao']),
                ]);

                $savedCount++;
            } catch (Exception $e) {
                Log::error("Erro ao salvar dados da estação {$stationCode}: " . $e->getMessage());
            }
        }

        // Atualiza timestamp da última medição
        $station->update(['last_measurement' => now()]);

        Log::info("Salvos {$savedCount} registros para a estação {$stationCode}");
        return $savedCount;
    }

    /**
     * Faz a requisição à API da ANA com retry automático
     *
     * @param string $stationCode
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $dataType
     * @return array|null
     * @throws Exception
     */
    private function makeApiRequest(
        string $stationCode,
        Carbon $startDate,
        Carbon $endDate,
        string $dataType
    ): ?array {
        $url = $this->buildApiUrl($stationCode, $startDate, $endDate, $dataType);
        
        $attempt = 0;
        $lastException = null;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)
                    ->retry(0) // Não usar retry do HTTP client, vamos controlar manualmente
                    ->get($url);

                if ($response->successful()) {
                    return $this->parseApiResponse($response);
                }

                throw new RequestException($response);

            } catch (ConnectionException $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    Log::warning("Tentativa {$attempt} falhou para estação {$stationCode}. Tentando novamente em {$this->retryDelay}ms");
                    usleep($this->retryDelay * 1000); // Converte para microssegundos
                }
            } catch (RequestException $e) {
                $lastException = $e;
                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    Log::warning("Tentativa {$attempt} falhou para estação {$stationCode}. Tentando novamente em {$this->retryDelay}ms");
                    usleep($this->retryDelay * 1000);
                }
            }
        }

        throw $lastException ?? new Exception("Falha ao conectar com a API da ANA após {$this->retryAttempts} tentativas");
    }

    /**
     * Constrói a URL da API
     * Baseado na documentação oficial: https://www.ana.gov.br/hidrowebservice/swagger-ui/index.html
     */
    private function buildApiUrl(string $stationCode, Carbon $startDate, Carbon $endDate, string $dataType): string
    {
        $endpoint = config('ana.endpoints.serie_historica');
        
        $params = [
            'CodEstacao' => $stationCode,
            'dataInicio' => $startDate->format('d/m/Y'),
            'dataFim' => $endDate->format('d/m/Y'),
            'tipoDados' => $this->getTipoDadosCode($dataType),
            'nivelConsistencia' => '', // Vazio = todos os níveis
        ];

        return $this->baseUrl . $endpoint . '?' . http_build_query($params);
    }

    /**
     * Processa a resposta da API
     * Baseado na estrutura de resposta da API da ANA
     */
    private function parseApiResponse(Response $response): array
    {
        $data = $response->json();

        if (!isset($data['SerieHistorica']) || !is_array($data['SerieHistorica'])) {
            throw new Exception('Resposta da API em formato inválido');
        }

        return $this->normalizeApiData($data['SerieHistorica']);
    }

    /**
     * Normaliza os dados da API para o formato do banco
     * Baseado na estrutura de resposta da API da ANA
     */
    private function normalizeApiData(array $apiData): array
    {
        $normalized = [];

        foreach ($apiData as $record) {
            $normalized[] = [
                'nivel' => $record['Cota'] ?? null,
                'vazao' => $record['Vazao'] ?? null,
                'chuva' => $record['Chuva'] ?? null,
                'data_medicao' => $record['DataHora'] ?? null,
            ];
        }

        return $normalized;
    }

    /**
     * Obtém ou cria uma estação no banco de dados
     */
    private function getOrCreateStation(string $stationCode): Station
    {
        $station = Station::where('code', $stationCode)->first();

        if (!$station) {
            $station = Station::create([
                'name' => "Estação {$stationCode}",
                'code' => $stationCode,
                'location' => 'Rio Piracicaba - Vale do Aço',
                'status' => 'active',
            ]);

            Log::info("Nova estação criada: {$stationCode}");
        }

        return $station;
    }

    /**
     * Gera chave de cache
     */
    private function getCacheKey(string $stationCode, Carbon $startDate, Carbon $endDate, string $dataType): string
    {
        return config('ana.cache.prefix') . "station_{$stationCode}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$dataType}";
    }

    /**
     * Converte tipo de dados para código da API da ANA
     * 1 = Cota (nível), 2 = Vazão, 3 = Chuva
     */
    private function getTipoDadosCode(string $dataType): int
    {
        return match($dataType) {
            'niveis' => 1,
            'vazoes' => 2,
            'chuvas' => 3,
            default => 1,
        };
    }

    /**
     * Busca lista de estações da ANA
     * Útil para descobrir códigos de estações
     */
    public function fetchStations(): ?array
    {
        $endpoint = config('ana.endpoints.estacoes');
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)->get($url);
            
            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error("Erro ao buscar estações da ANA: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Busca estações do Rio Piracicaba especificamente
     */
    public function fetchPiracicabaStations(): array
    {
        $allStations = $this->fetchStations();
        $piracicabaStations = [];

        if ($allStations && isset($allStations['Estacoes'])) {
            foreach ($allStations['Estacoes'] as $station) {
                // Filtra estações que contenham "Piracicaba" no nome ou localização
                if (stripos($station['Nome'] ?? '', 'Piracicaba') !== false ||
                    stripos($station['Municipio'] ?? '', 'Piracicaba') !== false) {
                    $piracicabaStations[] = $station;
                }
            }
        }

        return $piracicabaStations;
    }

    /**
     * Limpa cache de uma estação específica
     */
    public function clearStationCache(string $stationCode): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $pattern = config('ana.cache.prefix') . "station_{$stationCode}_*";
        
        // Implementação simples - em produção, considere usar Redis com SCAN
        $keys = Cache::getRedis()->keys($pattern);
        
        if (!empty($keys)) {
            Cache::getRedis()->del($keys);
            Log::info("Cache limpo para estação {$stationCode}");
        }
    }
}
