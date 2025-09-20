<?php

namespace Tests\Feature;

use App\Models\RiverData;
use App\Models\Station;
use App\Services\AnaApiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class RiverDataApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar estação de teste
        $this->station = Station::create([
            'name' => 'Estação Teste Piracicaba',
            'code' => '12345678',
            'location' => 'Rio Piracicaba - Vale do Aço',
            'status' => 'active',
        ]);

        // Criar alguns dados de teste
        RiverData::create([
            'station_id' => $this->station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $this->station->id,
            'nivel' => 3.6,
            'vazao' => 155.8,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(2),
        ]);
    }

    public function test_api_index_returns_river_data()
    {
        $response = $this->getJson('/api/river-data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'station_id',
                        'nivel',
                        'vazao',
                        'chuva',
                        'data_medicao',
                        'station' => [
                            'id',
                            'name',
                            'code',
                        ],
                    ],
                ],
                'meta' => [
                    'total',
                    'period',
                    'filters',
                ],
            ]);
    }

    public function test_api_index_filters_by_station_code()
    {
        $response = $this->getJson('/api/river-data?station_code=12345678');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_api_index_filters_by_days()
    {
        $response = $this->getJson('/api/river-data?days=1');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_api_index_returns_404_for_invalid_station()
    {
        $response = $this->getJson('/api/river-data?station_code=invalid');

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Estação não encontrada',
            ]);
    }

    public function test_api_stats_returns_statistics()
    {
        $response = $this->getJson('/api/river-data/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_measurements',
                    'total_stations',
                    'active_stations',
                    'latest_measurement',
                    'measurements_today',
                    'measurements_this_week',
                    'max_nivel',
                    'max_vazao',
                    'max_chuva',
                ],
            ]);
    }

    public function test_api_stations_returns_stations()
    {
        $response = $this->getJson('/api/stations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'code',
                        'location',
                        'status',
                        'river_data_count',
                    ],
                ],
                'meta' => [
                    'total',
                    'active_stations',
                ],
            ]);
    }

    public function test_api_fetch_from_ana_success()
    {
        // Mock do serviço ANA
        $mockAnaService = Mockery::mock(AnaApiService::class);
        $mockAnaService->shouldReceive('fetchStationData')
            ->once()
            ->andReturn([
                [
                    'nivel' => '3.7',
                    'vazao' => '160.0',
                    'chuva' => '0.0',
                    'data_medicao' => now()->format('d/m/Y H:i:s'),
                ],
            ]);
        $mockAnaService->shouldReceive('saveDataToDatabase')
            ->once()
            ->andReturn(1);

        $this->app->instance(AnaApiService::class, $mockAnaService);

        $response = $this->postJson('/api/ana/fetch', [
            'station_code' => '12345678',
            'days' => 7,
            'type' => 'niveis',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'station_code',
                    'records_saved',
                    'period',
                    'type',
                ],
            ]);
    }

    public function test_api_fetch_from_ana_validation_error()
    {
        $response = $this->postJson('/api/ana/fetch', [
            'station_code' => '', // Inválido
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['station_code']);
    }

    public function test_api_fetch_from_ana_no_data_found()
    {
        // Mock do serviço ANA retornando null
        $mockAnaService = Mockery::mock(AnaApiService::class);
        $mockAnaService->shouldReceive('fetchStationData')
            ->once()
            ->andReturn(null);

        $this->app->instance(AnaApiService::class, $mockAnaService);

        $response = $this->postJson('/api/ana/fetch', [
            'station_code' => '12345678',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Nenhum dado encontrado na ANA para esta estação',
            ]);
    }

    public function test_api_fetch_from_ana_service_exception()
    {
        // Mock do serviço ANA lançando exceção
        $mockAnaService = Mockery::mock(AnaApiService::class);
        $mockAnaService->shouldReceive('fetchStationData')
            ->once()
            ->andThrow(new \Exception('API Error'));

        $this->app->instance(AnaApiService::class, $mockAnaService);

        $response = $this->postJson('/api/ana/fetch', [
            'station_code' => '12345678',
        ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Erro ao buscar dados da ANA',
            ]);
    }

    public function test_api_discover_piracicaba_stations()
    {
        // Mock do serviço ANA
        $mockAnaService = Mockery::mock(AnaApiService::class);
        $mockAnaService->shouldReceive('fetchPiracicabaStations')
            ->once()
            ->andReturn([
                [
                    'Codigo' => '12345678',
                    'Nome' => 'Estação Piracicaba 1',
                    'Municipio' => 'Ipatinga',
                ],
            ]);

        $this->app->instance(AnaApiService::class, $mockAnaService);

        $response = $this->getJson('/api/stations/discover-piracicaba');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
            ]);
    }

    public function test_api_refresh_station()
    {
        // Mock do serviço ANA
        $mockAnaService = Mockery::mock(AnaApiService::class);
        $mockAnaService->shouldReceive('clearStationCache')
            ->once();
        $mockAnaService->shouldReceive('fetchStationData')
            ->once()
            ->andReturn([
                [
                    'nivel' => '3.8',
                    'vazao' => '165.0',
                    'chuva' => '0.0',
                    'data_medicao' => now()->format('d/m/Y H:i:s'),
                ],
            ]);
        $mockAnaService->shouldReceive('saveDataToDatabase')
            ->once()
            ->andReturn(1);

        $this->app->instance(AnaApiService::class, $mockAnaService);

        $response = $this->postJson('/api/ana/refresh-station', [
            'station_code' => '12345678',
            'clear_cache' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'station_code',
                    'records_saved',
                    'cache_cleared',
                ],
            ]);
    }

    public function test_chart_data_endpoint()
    {
        $response = $this->getJson('/api/river-data/chart?station_id=' . $this->station->id . '&days=7');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'nivel',
                        'vazao',
                        'chuva',
                        'data_medicao',
                    ],
                ],
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
