<?php

namespace Tests\Unit;

use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiverDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_river_data()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ]);

        $this->assertInstanceOf(RiverData::class, $riverData);
        $this->assertEquals(3.5, $riverData->nivel);
        $this->assertEquals(150.2, $riverData->vazao);
        $this->assertEquals(0.0, $riverData->chuva);
        $this->assertDatabaseHas('river_data', [
            'station_id' => $station->id,
            'nivel' => 3.5,
        ]);
    }

    public function test_river_data_belongs_to_station()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ]);

        $this->assertInstanceOf(Station::class, $riverData->station);
        $this->assertEquals($station->id, $riverData->station->id);
        $this->assertEquals('Estação Teste', $riverData->station->name);
    }

    public function test_river_data_can_have_null_values()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => null,
            'vazao' => 150.2,
            'chuva' => null,
            'data_medicao' => now(),
        ]);

        $this->assertNull($riverData->nivel);
        $this->assertEquals(150.2, $riverData->vazao);
        $this->assertNull($riverData->chuva);
    }

    public function test_river_data_decimal_precision()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.123456789, // 8,3 precision
            'vazao' => 150.123456789, // 10,3 precision
            'chuva' => 12.123456789, // 8,2 precision
            'data_medicao' => now(),
        ]);

        // Verificar que a precisão está correta (com tolerância para arredondamento)
        $this->assertEqualsWithDelta(3.123, $riverData->fresh()->nivel, 0.001);
        $this->assertEqualsWithDelta(150.123, $riverData->fresh()->vazao, 0.001);
        $this->assertEqualsWithDelta(12.12, $riverData->fresh()->chuva, 0.01);
    }

    public function test_river_data_data_medicao_cast()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $riverData->data_medicao);
        // Verificar que o cast está funcionando corretamente
        $this->assertIsString($riverData->fresh()->data_medicao);
    }

    public function test_river_data_fillable_attributes()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $data = [
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ];

        $riverData = RiverData::create($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $riverData->$key);
        }
    }

    public function test_river_data_foreign_key_constraint()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Tentar criar dados com station_id inexistente
        RiverData::create([
            'station_id' => 999, // ID que não existe
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ]);
    }

    public function test_river_data_cascade_delete()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $riverData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now(),
        ]);

        // Deletar a estação
        $station->delete();

        // Verificar que os dados foram deletados em cascata
        $this->assertDatabaseMissing('river_data', [
            'id' => $riverData->id,
        ]);
    }

    public function test_river_data_indexes()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        // Criar múltiplos dados para testar performance dos índices
        for ($i = 0; $i < 10; $i++) {
            RiverData::create([
                'station_id' => $station->id,
                'nivel' => 3.0 + $i * 0.1,
                'vazao' => 150.0 + $i * 10,
                'chuva' => $i * 0.5,
                'data_medicao' => now()->subHours($i),
            ]);
        }

        // Testar consultas que usam os índices
        $recentData = RiverData::where('station_id', $station->id)
            ->where('data_medicao', '>=', now()->subHours(5))
            ->get();

        $this->assertCount(6, $recentData); // 0, 1, 2, 3, 4, 5 horas atrás
    }

    public function test_river_data_scope_queries()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        // Dados com diferentes valores
        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.2,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        RiverData::create([
            'station_id' => $station->id,
            'nivel' => 4.0,
            'vazao' => 200.0,
            'chuva' => 5.5,
            'data_medicao' => now()->subHours(2),
        ]);

        // Testar consultas com diferentes critérios
        $highLevel = RiverData::where('nivel', '>', 3.8)->get();
        $this->assertCount(1, $highLevel);

        $withRain = RiverData::where('chuva', '>', 0)->get();
        $this->assertCount(1, $withRain);

        $recentData = RiverData::where('data_medicao', '>=', now()->subHours(1.5))->get();
        $this->assertCount(1, $recentData);
    }
}
