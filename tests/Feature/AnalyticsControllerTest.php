<?php

namespace Tests\Feature;

use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar estações de teste
        $this->piracicabaStation = Station::create([
            'name' => 'Rio Piracicaba - Ipatinga',
            'code' => 'PIR001',
            'location' => 'Ipatinga, MG',
            'status' => 'active',
        ]);

        $this->doceStation = Station::create([
            'name' => 'Rio Doce - Governador Valadares',
            'code' => 'RDO001',
            'location' => 'Governador Valadares, MG',
            'status' => 'active',
        ]);

        $this->inactiveStation = Station::create([
            'name' => 'Estação Inativa',
            'code' => 'INACT001',
            'location' => 'Teste, MG',
            'status' => 'inactive',
        ]);

        // Criar dados de teste
        $this->createTestData();
    }

    private function createTestData()
    {
        // Dados do Rio Piracicaba
        RiverData::create([
            'station_id' => $this->piracicabaStation->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $this->piracicabaStation->id,
            'nivel' => 3.8,
            'vazao' => 160.0,
            'chuva' => 2.5,
            'data_medicao' => now()->subHours(2),
        ]);

        RiverData::create([
            'station_id' => $this->piracicabaStation->id,
            'nivel' => 4.2,
            'vazao' => 180.5,
            'chuva' => 5.0,
            'data_medicao' => now()->subHours(3),
        ]);

        // Dados do Rio Doce
        RiverData::create([
            'station_id' => $this->doceStation->id,
            'nivel' => 2.8,
            'vazao' => 120.5,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $this->doceStation->id,
            'nivel' => 3.0,
            'vazao' => 130.0,
            'chuva' => 1.5,
            'data_medicao' => now()->subHours(2),
        ]);

        // Dados históricos (mais de 7 dias)
        RiverData::create([
            'station_id' => $this->piracicabaStation->id,
            'nivel' => 2.5,
            'vazao' => 100.0,
            'chuva' => 0.0,
            'data_medicao' => now()->subDays(10),
        ]);
    }

    public function test_analytics_page_loads_successfully()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewIs('analytics');
    }

    public function test_analytics_page_has_required_data()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewHas('maxNivel');
        $response->assertViewHas('minNivel');
        $response->assertViewHas('maxVazao');
        $response->assertViewHas('totalChuva');
        $response->assertViewHas('stations');
        $response->assertViewHas('avgNivel');
    }

    public function test_analytics_calculates_correct_statistics()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        
        // Verificar estatísticas calculadas corretamente
        $response->assertViewHas('maxNivel', 4.2);
        $response->assertViewHas('minNivel', 2.5);
        $response->assertViewHas('maxVazao', 180.5);
        $response->assertViewHas('totalChuva', 9.0); // 0+2.5+5.0+0+1.5
    }

    public function test_analytics_stations_data_structure()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewHas('stations');
        
        $stations = $response->viewData('stations');
        $this->assertIsIterable($stations);
        $this->assertCount(3, $stations); // 3 estações criadas
        
        // Verificar estrutura dos dados das estações
        $firstStation = $stations->first();
        $this->assertArrayHasKey('id', $firstStation);
        $this->assertArrayHasKey('name', $firstStation);
        $this->assertArrayHasKey('code', $firstStation);
        $this->assertArrayHasKey('location', $firstStation);
        $this->assertArrayHasKey('status', $firstStation);
        $this->assertArrayHasKey('river_data_count', $firstStation);
        $this->assertArrayHasKey('avg_nivel', $firstStation);
        $this->assertArrayHasKey('avg_vazao', $firstStation);
        $this->assertArrayHasKey('total_chuva', $firstStation);
    }

    public function test_analytics_stations_statistics_calculation()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        
        $stations = $response->viewData('stations');
        
        // Encontrar estação do Piracicaba
        $piracicabaStation = $stations->firstWhere('name', 'Rio Piracicaba - Ipatinga');
        $this->assertNotNull($piracicabaStation);
        $this->assertEquals(4, $piracicabaStation['river_data_count']); // 4 registros
        $this->assertEquals(3.5, $piracicabaStation['avg_nivel']); // (3.5+3.8+4.2+2.5)/4
        $this->assertEqualsWithDelta(147.675, $piracicabaStation['avg_vazao'], 0.1); // (150.2+160+180.5+100)/4
        $this->assertEquals(7.5, $piracicabaStation['total_chuva']); // 0+2.5+5.0+0
    }

    public function test_analytics_handles_empty_data()
    {
        // Limpar todos os dados
        RiverData::truncate();
        Station::truncate();

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewHas('maxNivel', 0);
        $response->assertViewHas('minNivel', 0);
        $response->assertViewHas('maxVazao', 0);
        $response->assertViewHas('totalChuva', 0);
        $response->assertViewHas('stations');
        
        $stations = $response->viewData('stations');
        $this->assertCount(0, $stations);
    }

    public function test_analytics_performance_with_large_dataset()
    {
        // Criar dados em massa para testar performance
        $station = Station::create([
            'name' => 'Estação Performance',
            'code' => 'PERF001',
            'location' => 'Teste, MG',
            'status' => 'active',
        ]);

        // Criar 1000 registros
        for ($i = 0; $i < 1000; $i++) {
            RiverData::create([
                'station_id' => $station->id,
                'nivel' => 3.0 + ($i % 10) * 0.1,
                'vazao' => 150.0 + ($i % 50) * 2,
                'chuva' => ($i % 5) * 0.5,
                'data_medicao' => now()->subHours($i),
            ]);
        }

        $startTime = microtime(true);
        $response = $this->get('/analytics');
        $endTime = microtime(true);

        $response->assertStatus(200);
        
        // Verificar que a resposta foi rápida (menos de 2 segundos)
        $this->assertLessThan(2.0, $endTime - $startTime);
    }

    public function test_analytics_filters_active_stations_only()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        
        $stations = $response->viewData('stations');
        
        // Verificar que apenas estações ativas têm dados
        foreach ($stations as $station) {
            if ($station['status'] === 'active') {
                $this->assertGreaterThan(0, $station['river_data_count']);
            }
        }
    }

    public function test_analytics_handles_null_values()
    {
        // Criar dados com valores nulos
        $station = Station::create([
            'name' => 'Estação Nulos',
            'code' => 'NULL001',
            'location' => 'Teste, MG',
            'status' => 'active',
        ]);

        RiverData::create([
            'station_id' => $station->id,
            'nivel' => null,
            'vazao' => 150.0,
            'chuva' => null,
            'data_medicao' => now(),
        ]);

        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => null,
            'chuva' => 5.0,
            'data_medicao' => now()->subHours(1),
        ]);

        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertViewHas('maxNivel', 4.2);
        $response->assertViewHas('maxVazao', 180.5);
        $response->assertViewHas('totalChuva', 5.0);
    }

    public function test_analytics_error_handling()
    {
        // Simular erro no banco de dados
        $this->mock(\Illuminate\Database\Connection::class, function ($mock) {
            $mock->shouldReceive('table')->andThrow(new \Exception('Database error'));
        });

        $response = $this->get('/analytics');

        // Deve retornar view de erro ou dados vazios
        $response->assertStatus(200);
    }

    public function test_analytics_view_renders_correctly()
    {
        $response = $this->get('/analytics');

        $response->assertStatus(200);
        $response->assertSee('Análises - Monitor Rio Piracicaba');
        $response->assertSee('Estatísticas de Níveis');
        $response->assertSee('Resumo de Vazões');
        $response->assertSee('Análise por Estação');
    }
}
