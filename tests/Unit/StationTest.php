<?php

namespace Tests\Unit;

use App\Models\Station;
use App\Models\RiverData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_station()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'latitude' => -19.5,
            'longitude' => -42.5,
            'description' => 'Estação de teste',
            'status' => 'active',
        ]);

        $this->assertInstanceOf(Station::class, $station);
        $this->assertEquals('Estação Teste', $station->name);
        $this->assertEquals('TEST001', $station->code);
        $this->assertEquals('active', $station->status);
        $this->assertDatabaseHas('stations', [
            'code' => 'TEST001',
            'name' => 'Estação Teste',
        ]);
    }

    public function test_station_has_river_data_relationship()
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

        $this->assertTrue($station->riverData->contains($riverData));
        $this->assertEquals(1, $station->riverData->count());
    }

    public function test_station_scope_active()
    {
        Station::create([
            'name' => 'Estação Ativa',
            'code' => 'ACT001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        Station::create([
            'name' => 'Estação Inativa',
            'code' => 'INACT001',
            'location' => 'Rio Piracicaba',
            'status' => 'inactive',
        ]);

        $activeStations = Station::active()->get();
        
        $this->assertCount(1, $activeStations);
        $this->assertEquals('Estação Ativa', $activeStations->first()->name);
    }

    public function test_station_scope_with_recent_data()
    {
        $station = Station::create([
            'name' => 'Estação Recente',
            'code' => 'REC001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
            'last_measurement' => now()->subHours(2),
        ]);

        Station::create([
            'name' => 'Estação Antiga',
            'code' => 'OLD001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
            'last_measurement' => now()->subDays(2),
        ]);

        $recentStations = Station::withRecentData(24)->get();
        
        $this->assertCount(1, $recentStations);
        $this->assertEquals('Estação Recente', $recentStations->first()->name);
    }

    public function test_station_is_online()
    {
        $onlineStation = Station::create([
            'name' => 'Estação Online',
            'code' => 'ON001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
            'last_measurement' => now()->subHours(2),
        ]);

        $offlineStation = Station::create([
            'name' => 'Estação Offline',
            'code' => 'OFF001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
            'last_measurement' => now()->subDays(2),
        ]);

        $this->assertTrue($onlineStation->isOnline());
        $this->assertFalse($offlineStation->isOnline());
    }

    public function test_station_formatted_location()
    {
        $station = Station::create([
            'name' => 'Estação Coordenadas',
            'code' => 'COORD001',
            'location' => 'Rio Piracicaba',
            'latitude' => -19.5,
            'longitude' => -42.5,
            'status' => 'active',
        ]);

        $this->assertEquals('-19.5, -42.5', $station->formatted_location);
    }

    public function test_station_formatted_location_without_coordinates()
    {
        $station = Station::create([
            'name' => 'Estação Sem Coordenadas',
            'code' => 'NOCOORD001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $this->assertEquals('Rio Piracicaba', $station->formatted_location);
    }

    public function test_station_status_color()
    {
        $activeStation = Station::create([
            'name' => 'Estação Ativa',
            'code' => 'ACT001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $inactiveStation = Station::create([
            'name' => 'Estação Inativa',
            'code' => 'INACT001',
            'location' => 'Rio Piracicaba',
            'status' => 'inactive',
        ]);

        $maintenanceStation = Station::create([
            'name' => 'Estação Manutenção',
            'code' => 'MAINT001',
            'location' => 'Rio Piracicaba',
            'status' => 'maintenance',
        ]);

        $this->assertEquals('green', $activeStation->status_color);
        $this->assertEquals('red', $inactiveStation->status_color);
        $this->assertEquals('yellow', $maintenanceStation->status_color);
    }

    public function test_station_latest_river_data()
    {
        $station = Station::create([
            'name' => 'Estação Teste',
            'code' => 'TEST001',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        $oldData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.0,
            'vazao' => 100.0,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(2),
        ]);

        $newData = RiverData::create([
            'station_id' => $station->id,
            'nivel' => 3.5,
            'vazao' => 150.0,
            'chuva' => 0.0,
            'data_medicao' => now()->subHours(1),
        ]);

        $latestData = $station->latestRiverData();
        
        $this->assertInstanceOf(RiverData::class, $latestData);
        $this->assertEquals($newData->id, $latestData->id);
        $this->assertEquals(3.5, $latestData->nivel);
    }

    public function test_station_validation_rules()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Tentar criar estação sem código único
        Station::create([
            'name' => 'Estação 1',
            'code' => 'DUPLICATE',
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);

        Station::create([
            'name' => 'Estação 2',
            'code' => 'DUPLICATE', // Código duplicado
            'location' => 'Rio Piracicaba',
            'status' => 'active',
        ]);
    }

    public function test_station_casts()
    {
        $station = Station::create([
            'name' => 'Estação Casts',
            'code' => 'CAST001',
            'location' => 'Rio Piracicaba',
            'latitude' => -19.5,
            'longitude' => -42.5,
            'status' => 'active',
            'last_measurement' => now(),
        ]);

        $this->assertIsFloat($station->latitude);
        $this->assertIsFloat($station->longitude);
        $this->assertInstanceOf(\Carbon\Carbon::class, $station->last_measurement);
    }
}
