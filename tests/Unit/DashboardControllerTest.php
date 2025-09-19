<?php

namespace Tests\Unit;

use App\Http\Controllers\DashboardController;
use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\View;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private DashboardController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new DashboardController();
    }

    public function test_index_returns_view()
    {
        $response = $this->get('/');
        
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }

    public function test_index_passes_correct_data()
    {
        // Criar estações de teste
        $station1 = Station::create([
            'name' => 'Rio Piracicaba - Ipatinga',
            'code' => 'PIR001',
            'location' => 'Ipatinga, MG',
            'status' => 'active',
        ]);

        $station2 = Station::create([
            'name' => 'Rio Doce - Governador Valadares',
            'code' => 'RDO001',
            'location' => 'Governador Valadares, MG',
            'status' => 'active',
        ]);

        $station3 = Station::create([
            'name' => 'Estação Inativa',
            'code' => 'INACT001',
            'location' => 'Teste, MG',
            'status' => 'inactive',
        ]);

        // Criar dados de teste
        RiverData::create([
            'station_id' => $station1->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $station2->id,
            'nivel' => 2.8,
            'vazao' => 120.5,
            'chuva' => 5.2,
            'data_medicao' => now()->subHours(2),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('totalStations', 3);
        $response->assertViewHas('activeStations', 2);
        $response->assertViewHas('totalMeasurements', 2);
        $response->assertViewHas('maxNivel', 3.5);
        $response->assertViewHas('maxVazao', 150.2);
        $response->assertViewHas('totalChuva', 5.2);
    }

    public function test_index_includes_piracicaba_data()
    {
        // Criar estação do Rio Piracicaba
        $piracicabaStation = Station::create([
            'name' => 'Rio Piracicaba - Ipatinga',
            'code' => 'PIR001',
            'location' => 'Ipatinga, MG',
            'status' => 'active',
        ]);

        // Criar dados do Rio Piracicaba
        RiverData::create([
            'station_id' => $piracicabaStation->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $piracicabaStation->id,
            'nivel' => 3.8,
            'vazao' => 160.0,
            'chuva' => 2.5,
            'data_medicao' => now()->subHours(2),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('piracicabaStations');
        $response->assertViewHas('piracicabaData');
        $response->assertViewHas('chartData');
        
        // Verificar se os dados do Piracicaba estão presentes
        $piracicabaStations = $response->viewData('piracicabaStations');
        $this->assertCount(1, $piracicabaStations);
        $this->assertEquals('Rio Piracicaba - Ipatinga', $piracicabaStations->first()->name);
    }

    public function test_index_handles_empty_data()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('totalStations', 0);
        $response->assertViewHas('activeStations', 0);
        $response->assertViewHas('totalMeasurements', 0);
        $response->assertViewHas('maxNivel', 0);
        $response->assertViewHas('maxVazao', 0);
        $response->assertViewHas('totalChuva', 0);
    }

    public function test_index_chart_data_format()
    {
        // Criar estação do Rio Piracicaba
        $piracicabaStation = Station::create([
            'name' => 'Rio Piracicaba - Ipatinga',
            'code' => 'PIR001',
            'location' => 'Ipatinga, MG',
            'status' => 'active',
        ]);

        // Criar dados para as últimas 24 horas
        for ($i = 0; $i < 5; $i++) {
            RiverData::create([
                'station_id' => $piracicabaStation->id,
                'nivel' => 3.0 + $i * 0.1,
                'vazao' => 150.0 + $i * 10,
                'chuva' => $i * 0.5,
                'data_medicao' => now()->subHours($i),
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('chartData');
        
        $chartData = $response->viewData('chartData');
        $this->assertIsIterable($chartData);
        $this->assertLessThanOrEqual(24, $chartData->count());
    }

    public function test_index_performance_with_large_dataset()
    {
        // Criar múltiplas estações e dados para testar performance
        $stations = [];
        for ($i = 0; $i < 10; $i++) {
            $stations[] = Station::create([
                'name' => "Estação {$i}",
                'code' => "STATION{$i}",
                'location' => "Localização {$i}",
                'status' => 'active',
            ]);
        }

        // Criar dados para cada estação
        foreach ($stations as $station) {
            for ($j = 0; $j < 50; $j++) {
                RiverData::create([
                    'station_id' => $station->id,
                    'nivel' => 3.0 + ($j % 10) * 0.1,
                    'vazao' => 150.0 + ($j % 20) * 5,
                    'chuva' => ($j % 5) * 0.5,
                    'data_medicao' => now()->subHours($j),
                ]);
            }
        }

        $startTime = microtime(true);
        $response = $this->get('/');
        $endTime = microtime(true);

        $response->assertStatus(200);
        
        // Verificar que a resposta foi rápida (menos de 1 segundo)
        $this->assertLessThan(1.0, $endTime - $startTime);
    }

    public function test_index_recent_data_limit()
    {
        // Criar estação
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Teste, MG',
            'status' => 'active',
        ]);

        // Criar mais de 20 registros
        for ($i = 0; $i < 25; $i++) {
            RiverData::create([
                'station_id' => $station->id,
                'nivel' => 3.0 + $i * 0.1,
                'vazao' => 150.0 + $i * 5,
                'chuva' => $i * 0.2,
                'data_medicao' => now()->subHours($i),
            ]);
        }

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('recentData');
        
        $recentData = $response->viewData('recentData');
        $this->assertCount(20, $recentData); // Deve limitar a 20 registros
    }

    public function test_index_averages_calculation()
    {
        // Criar estação
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Teste, MG',
            'status' => 'active',
        ]);

        // Criar dados com valores conhecidos
        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 2.0,
            'vazao' => 100.0,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(3),
        ]);

        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 4.0,
            'vazao' => 200.0,
            'chuva' => 10.0,
            'data_medicao' => now()->subHours(2),
        ]);

        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 6.0,
            'vazao' => 300.0,
            'chuva' => 20.0,
            'data_medicao' => now()->subHours(1),
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('avgNivel', 4.0); // (2+4+6)/3
        $response->assertViewHas('maxNivel', 6.0);
        $response->assertViewHas('maxVazao', 300.0);
        $response->assertViewHas('totalChuva', 30.0);
    }
}
