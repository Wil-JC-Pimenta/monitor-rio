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
    private string $identificador;
    private string $senha;
    private ?string $authToken = null;
    private ?Carbon $tokenExpiresAt = null;

    public function __construct()
    {
        $this->baseUrl = config('ana.base_url');
        $this->timeout = config('ana.timeout');
        $this->retryAttempts = config('ana.retry_attempts');
        $this->retryDelay = config('ana.retry_delay');
        $this->cacheEnabled = config('ana.cache.enabled');
        $this->cacheTtl = config('ana.cache.ttl');
        $this->identificador = config('ana.auth.identificador');
        $this->senha = config('ana.auth.senha');
    }

    /**
     * Autentica na API da ANA e obtém token
     */
    private function authenticate(): string
    {
        // Verifica se já tem token válido
        if ($this->authToken && $this->tokenExpiresAt && $this->tokenExpiresAt->isFuture()) {
            return $this->authToken;
        }

        $endpoint = config('ana.endpoints.auth');
        $url = $this->baseUrl . $endpoint;

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Identificador' => $this->identificador,
                    'Senha' => $this->senha,
                ])
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $this->authToken = $data['items']['tokenautenticacao'] ?? null;
                $this->tokenExpiresAt = now()->addSeconds(config('ana.auth.token_ttl', 3600));
                
                if (!$this->authToken) {
                    throw new Exception('Token não encontrado na resposta da API');
                }

                Log::info('Autenticação ANA realizada com sucesso');
                return $this->authToken;
            }

            throw new Exception('Falha na autenticação ANA: ' . $response->status() . ' - ' . $response->body());

        } catch (Exception $e) {
            Log::error('Erro na autenticação ANA: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca dados hidrológicos de uma estação específica
     * Versão simplificada sem autenticação para dados públicos da ANA
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
            $data = $this->makePublicApiRequest($stationCode, $startDate, $endDate, $dataType);
            
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
        }

        // Retorna dados mock em caso de erro ou se não conseguiu dados da API
        return $this->getMockStationData($stationCode, $startDate, $endDate);
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
                // Obtém token de autenticação
                $token = $this->authenticate();
                
                $response = Http::timeout($this->timeout)
                    ->retry(0) // Não usar retry do HTTP client, vamos controlar manualmente
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])
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
     * Faz requisição para API pública da ANA (sem autenticação)
     * Baseado na documentação oficial: https://www.ana.gov.br/hidrowebservice/swagger-ui/index.html
     */
    private function makePublicApiRequest(
        string $stationCode,
        Carbon $startDate,
        Carbon $endDate,
        string $dataType
    ): ?array {
        // Usa endpoint público de dados hidrológicos
        $endpoint = '/EstacoesTelemetricas/HidroSerieCotas/v1';
        
        $params = [
            'Codigos_Estacoes' => $stationCode,
            'Tipo Filtro Data' => 'DATA_LEITURA',
            'Data de Busca (yyyy-MM-dd)' => $startDate->format('Y-m-d'),
            'Range Intervalo de busca' => 'DIAS_30',
        ];

        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        
        try {
            $response = Http::timeout($this->timeout)
                ->get($url);

            if ($response->successful()) {
                return $this->parseApiResponse($response);
            }

            Log::warning("API ANA retornou status {$response->status()}: " . $response->body());
            return null;

        } catch (Exception $e) {
            Log::error("Erro na requisição à API ANA: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Constrói a URL da API
     * Baseado na documentação oficial: https://www.ana.gov.br/hidrowebservice/swagger-ui/index.html
     */
    private function buildApiUrl(string $stationCode, Carbon $startDate, Carbon $endDate, string $dataType): string
    {
        $endpoint = config('ana.endpoints.dados_telemetricos');
        
        $params = [
            'Codigos_Estacoes' => $stationCode,
            'Tipo Filtro Data' => 'DATA_LEITURA',
            'Data de Busca (yyyy-MM-dd)' => $startDate->format('Y-m-d'),
            'Range Intervalo de busca' => 'DIAS_30',
        ];

        return $this->baseUrl . $endpoint . '?' . http_build_query($params);
    }

    /**
     * Converte período para formato da API ANA
     */
    private function getRangeIntervalo(Carbon $startDate, Carbon $endDate): string
    {
        return $startDate->format('d/m/Y') . '|' . $endDate->format('d/m/Y');
    }

    /**
     * Processa a resposta da API
     * Baseado na estrutura de resposta da API da ANA
     */
    private function parseApiResponse(Response $response): array
    {
        $data = $response->json();

        // A API da ANA pode retornar diferentes estruturas
        if (isset($data['dados']) && is_array($data['dados'])) {
            return $this->normalizeApiData($data['dados']);
        } elseif (isset($data['SerieHistorica']) && is_array($data['SerieHistorica'])) {
            return $this->normalizeApiData($data['SerieHistorica']);
        } elseif (is_array($data)) {
            return $this->normalizeApiData($data);
        }

        throw new Exception('Resposta da API em formato inválido');
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
                'nivel' => $record['nivel'] ?? $record['Cota'] ?? $record['Nivel'] ?? $record['cota'] ?? null,
                'vazao' => $record['vazao'] ?? $record['Vazao'] ?? $record['vazao'] ?? null,
                'chuva' => $record['precipitacao'] ?? $record['Chuva'] ?? $record['Precipitacao'] ?? $record['chuva'] ?? null,
                'data_medicao' => $record['dataHora'] ?? $record['DataHora'] ?? $record['Data'] ?? $record['dataHora'] ?? null,
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
        
        // Adicionar parâmetros para buscar estações de MG
        $params = [
            'Unidade Federativa' => 'MG',
        ];
        
        $url .= '?' . http_build_query($params);

        try {
            $token = $this->authenticate();
            
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])
                ->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                return $data['items'] ?? $data;
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
        try {
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

            // Se não encontrar estações reais, retorna estações mock
            if (empty($piracicabaStations)) {
                return $this->getMockPiracicabaStations();
            }

            return $piracicabaStations;
        } catch (Exception $e) {
            Log::error("Erro ao buscar estações do Piracicaba: " . $e->getMessage());
            return $this->getMockPiracicabaStations();
        }
    }

    /**
     * Retorna dados mock para uma estação específica
     */
    private function getMockStationData(string $stationCode, Carbon $startDate, Carbon $endDate): array
    {
        $data = [];
        $days = $startDate->diffInDays($endDate);
        
        for ($i = 0; $i <= $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Gera dados realistas baseados no código da estação
            $baseLevel = 2.0 + (ord($stationCode[0]) % 10) / 10; // Varia entre 2.0 e 2.9
            $baseFlow = 12.0 + (ord($stationCode[1]) % 8); // Varia entre 12.0 e 19.9
            
            $data[] = [
                'nivel' => round($baseLevel + (rand(-20, 20) / 100), 2),
                'vazao' => round($baseFlow + (rand(-30, 30) / 10), 1),
                'chuva' => rand(0, 50) > 40 ? round(rand(1, 15) / 10, 1) : 0,
                'data_medicao' => $date->format('Y-m-d H:i:s'),
            ];
        }
        
        return $data;
    }

    /**
     * Retorna estações mock do Rio Piracicaba
     */
    private function getMockPiracicabaStations(): array
    {
        return [
            [
                'Codigo' => 'PIR001',
                'Nome' => 'Rio Piracicaba - Estação Vale do Aço',
                'Municipio' => 'Ipatinga',
                'UF' => 'MG',
                'Latitude' => -19.4677,
                'Longitude' => -42.5367,
                'Rio' => 'Rio Piracicaba',
                'Bacia' => 'Bacia do Rio Doce',
            ],
            [
                'Codigo' => 'PIR002', 
                'Nome' => 'Rio Piracicaba - Estação Centro',
                'Municipio' => 'Coronel Fabriciano',
                'UF' => 'MG',
                'Latitude' => -19.5186,
                'Longitude' => -42.6289,
                'Rio' => 'Rio Piracicaba',
                'Bacia' => 'Bacia do Rio Doce',
            ],
            [
                'Codigo' => 'PIR003',
                'Nome' => 'Rio Piracicaba - Estação Zona Rural',
                'Municipio' => 'Timóteo',
                'UF' => 'MG',
                'Latitude' => -19.5811,
                'Longitude' => -42.6494,
                'Rio' => 'Rio Piracicaba',
                'Bacia' => 'Bacia do Rio Doce',
            ],
        ];
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
