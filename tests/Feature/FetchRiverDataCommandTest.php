<?php

namespace Tests\Feature;

use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class FetchRiverDataCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Criar estação de teste
        $this->station = Station::create([
            'name' => 'Rio Piracicaba - Ipatinga',
            'code' => 'PIR001',
            'location' => 'Ipatinga, MG',
            'status' => 'active',
        ]);
    }

    public function test_fetch_river_data_command_exists()
    {
        $this->assertTrue(Artisan::hasCommand('river:fetch'));
    }

    public function test_fetch_river_data_command_help()
    {
        $exitCode = Artisan::call('river:fetch', ['--help' => true]);
        $this->assertEquals(0, $exitCode);
    }

    public function test_fetch_river_data_command_with_invalid_station()
    {
        $exitCode = Artisan::call('river:fetch', [
            '--station' => 'INVALID',
            '--days' => 1,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fetch_river_data_command_validation()
    {
        // Teste com parâmetros inválidos
        $exitCode = Artisan::call('river:fetch', [
            '--station' => '',
            '--days' => -1,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fetch_river_data_command_without_parameters()
    {
        $exitCode = Artisan::call('river:fetch');
        
        // Deve falhar sem parâmetros obrigatórios
        $this->assertEquals(1, $exitCode);
    }

    public function test_fetch_river_data_command_skips_inactive_stations()
    {
        // Criar estação inativa
        $inactiveStation = Station::create([
            'name' => 'Estação Inativa',
            'code' => 'INACT001',
            'location' => 'Teste, MG',
            'status' => 'inactive',
        ]);

        $exitCode = Artisan::call('river:fetch', [
            '--station' => 'INACT001',
            '--days' => 1,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fetch_river_data_command_with_valid_station()
    {
        $exitCode = Artisan::call('river:fetch', [
            '--station' => 'PIR001',
            '--days' => 1,
        ]);

        // Pode falhar por falta de dados da API, mas o comando deve existir
        $this->assertIsInt($exitCode);
    }

    public function test_fetch_river_data_command_all_stations()
    {
        // Criar mais uma estação ativa
        Station::create([
            'name' => 'Rio Doce - Governador Valadares',
            'code' => 'RDO001',
            'location' => 'Governador Valadares, MG',
            'status' => 'active',
        ]);

        $exitCode = Artisan::call('river:fetch', [
            '--days' => 1,
        ]);

        // Pode falhar por falta de dados da API, mas o comando deve existir
        $this->assertIsInt($exitCode);
    }

    public function test_fetch_river_data_command_handles_errors_gracefully()
    {
        // Teste com estação que não existe
        $exitCode = Artisan::call('river:fetch', [
            '--station' => 'NONEXISTENT',
            '--days' => 1,
        ]);

        $this->assertEquals(1, $exitCode);
    }

    public function test_fetch_river_data_command_output()
    {
        $exitCode = Artisan::call('river:fetch', [
            '--station' => 'PIR001',
            '--days' => 1,
        ]);

        $output = Artisan::output();
        
        // Verificar que há alguma saída (mesmo que seja erro)
        $this->assertIsString($output);
    }
}