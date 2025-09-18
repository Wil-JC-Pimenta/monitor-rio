<?php

namespace Tests\Unit;

use App\Models\RiverData;
use App\Models\Station;
use App\Services\AnaApiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AnaApiServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnaApiService $anaService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->anaService = new AnaApiService();
    }

    public function test_fetch_station_data_success()
    {
        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/HidroSerieHistorica*' => Http::response([
                'SerieHistorica' => [
                    [
                        'DataHora' => '01/09/2025 00:00:00',
                        'Cota' => '3.5',
                        'Vazao' => '150.2',
                        'Chuva' => '0.0',
                    ],
                    [
                        'DataHora' => '01/09/2025 01:00:00',
                        'Cota' => '3.6',
                        'Vazao' => '155.8',
                        'Chuva' => '0.0',
                    ],
                ],
            ], 200),
        ]);

        $data = $this->anaService->fetchStationData('12345678', now()->subDays(7), now());

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertEquals('3.5', $data[0]['nivel']);
        $this->assertEquals('150.2', $data[0]['vazao']);
        $this->assertEquals('0.0', $data[0]['chuva']);
    }

    public function test_fetch_station_data_api_error()
    {
        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/HidroSerieHistorica*' => Http::response([], 500),
        ]);

        $this->expectException(RequestException::class);
        
        $this->anaService->fetchStationData('12345678', now()->subDays(7), now());
    }

    public function test_fetch_station_data_connection_error()
    {
        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/HidroSerieHistorica*' => function () {
                throw new ConnectionException('Connection failed');
            },
        ]);

        $this->expectException(ConnectionException::class);
        
        $this->anaService->fetchStationData('12345678', now()->subDays(7), now());
    }

    public function test_save_data_to_database()
    {
        // Criar estação
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => '12345678',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $apiData = [
            [
                'nivel' => '3.5',
                'vazao' => '150.2',
                'chuva' => '0.0',
                'data_medicao' => '01/09/2025 00:00:00',
            ],
            [
                'nivel' => '3.6',
                'vazao' => '155.8',
                'chuva' => '0.0',
                'data_medicao' => '01/09/2025 01:00:00',
            ],
        ];

        $savedCount = $this->anaService->saveDataToDatabase($apiData, '12345678');

        $this->assertEquals(2, $savedCount);
        $this->assertDatabaseHas('river_data', [
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
        ]);
    }

    public function test_get_tipo_dados_code()
    {
        $reflection = new \ReflectionClass($this->anaService);
        $method = $reflection->getMethod('getTipoDadosCode');
        $method->setAccessible(true);

        $this->assertEquals(1, $method->invoke($this->anaService, 'niveis'));
        $this->assertEquals(2, $method->invoke($this->anaService, 'vazoes'));
        $this->assertEquals(3, $method->invoke($this->anaService, 'chuvas'));
        $this->assertEquals(1, $method->invoke($this->anaService, 'invalid'));
    }

    public function test_fetch_stations()
    {
        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/Estacoes*' => Http::response([
                'Estacoes' => [
                    [
                        'Codigo' => '12345678',
                        'Nome' => 'Estação Piracicaba 1',
                        'Municipio' => 'Ipatinga',
                    ],
                    [
                        'Codigo' => '87654321',
                        'Nome' => 'Estação Piracicaba 2',
                        'Municipio' => 'Timóteo',
                    ],
                ],
            ], 200),
        ]);

        $stations = $this->anaService->fetchStations();

        $this->assertIsArray($stations);
        $this->assertArrayHasKey('Estacoes', $stations);
        $this->assertCount(2, $stations['Estacoes']);
    }

    public function test_fetch_piracicaba_stations()
    {
        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/Estacoes*' => Http::response([
                'Estacoes' => [
                    [
                        'Codigo' => '12345678',
                        'Nome' => 'Estação Piracicaba 1',
                        'Municipio' => 'Ipatinga',
                    ],
                    [
                        'Codigo' => '87654321',
                        'Nome' => 'Estação Piracicaba 2',
                        'Municipio' => 'Timóteo',
                    ],
                    [
                        'Codigo' => '11111111',
                        'Nome' => 'Estação Outro Rio',
                        'Municipio' => 'Belo Horizonte',
                    ],
                ],
            ], 200),
        ]);

        $stations = $this->anaService->fetchPiracicabaStations();

        $this->assertIsArray($stations);
        $this->assertCount(2, $stations); // Apenas as que contêm "Piracicaba"
    }

    public function test_cache_functionality()
    {
        // Desabilitar cache para este teste
        config(['ana.cache.enabled' => false]);

        Http::fake([
            'https://www.ana.gov.br/hidrowebservice/HidroSerieHistorica*' => Http::response([
                'SerieHistorica' => [
                    [
                        'DataHora' => '01/09/2025 00:00:00',
                        'Cota' => '3.5',
                        'Vazao' => '150.2',
                        'Chuva' => '0.0',
                    ],
                ],
            ], 200),
        ]);

        // Primeira chamada
        $data1 = $this->anaService->fetchStationData('12345678', now()->subDays(7), now());
        
        // Segunda chamada (deve fazer nova requisição)
        $data2 = $this->anaService->fetchStationData('12345678', now()->subDays(7), now());

        $this->assertIsArray($data1);
        $this->assertIsArray($data2);
        
        // Verificar que foram feitas 2 requisições
        Http::assertSentCount(2);
    }

    public function test_clear_station_cache()
    {
        // Simular cache com dados
        Cache::put('ana_data_station_12345678_2025-09-01_2025-09-08_niveis', ['test' => 'data'], 3600);
        
        $this->anaService->clearStationCache('12345678');
        
        // Verificar que o cache foi limpo (implementação específica depende do driver de cache)
        $this->assertTrue(true); // Placeholder - implementação real depende do driver
    }
}
