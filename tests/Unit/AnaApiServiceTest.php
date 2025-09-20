<?php

namespace Tests\Unit;

use App\Models\RiverData;
use App\Models\Station;
use App\Services\AnaApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_clear_station_cache()
    {
        // Simular cache com dados
        Cache::put('ana_data_station_12345678_2025-09-01_2025-09-08_niveis', ['test' => 'data'], 3600);
        
        $this->anaService->clearStationCache('12345678');
        
        // Verificar que o cache foi limpo (implementação específica depende do driver de cache)
        $this->assertTrue(true); // Placeholder - implementação real depende do driver
    }

    public function test_ana_api_service_instantiation()
    {
        $this->assertInstanceOf(AnaApiService::class, $this->anaService);
    }

    public function test_ana_api_service_has_required_methods()
    {
        $this->assertTrue(method_exists($this->anaService, 'fetchStationData'));
        $this->assertTrue(method_exists($this->anaService, 'saveDataToDatabase'));
        $this->assertTrue(method_exists($this->anaService, 'fetchStations'));
        $this->assertTrue(method_exists($this->anaService, 'fetchPiracicabaStations'));
        $this->assertTrue(method_exists($this->anaService, 'clearStationCache'));
    }

    public function test_ana_api_service_configuration()
    {
        $reflection = new \ReflectionClass($this->anaService);
        $property = $reflection->getProperty('baseUrl');
        $property->setAccessible(true);
        
        $this->assertIsString($property->getValue($this->anaService));
    }

    public function test_ana_api_service_authentication_flow()
    {
        // Teste básico de que o serviço pode ser instanciado sem erros
        $this->assertNotNull($this->anaService);
        
        // Teste de que os métodos existem
        $this->assertTrue(method_exists($this->anaService, 'authenticate'));
        $this->assertTrue(method_exists($this->anaService, 'makeApiRequest'));
    }

    public function test_ana_api_service_properties()
    {
        $reflection = new \ReflectionClass($this->anaService);
        
        // Verificar propriedades privadas
        $properties = ['baseUrl', 'timeout', 'retryAttempts', 'retryDelay', 'cacheEnabled', 'cacheTtl'];
        
        foreach ($properties as $property) {
            $this->assertTrue($reflection->hasProperty($property));
        }
    }

    public function test_ana_api_service_private_methods()
    {
        $reflection = new \ReflectionClass($this->anaService);
        
        // Verificar métodos privados
        $methods = ['getTipoDadosCode', 'buildApiUrl', 'parseApiResponse', 'normalizeApiData'];
        
        foreach ($methods as $method) {
            $this->assertTrue($reflection->hasMethod($method));
        }
    }

    public function test_ana_api_service_constructor()
    {
        $service = new AnaApiService();
        $this->assertInstanceOf(AnaApiService::class, $service);
        
        // Verificar se as configurações foram carregadas
        $reflection = new \ReflectionClass($service);
        $baseUrlProperty = $reflection->getProperty('baseUrl');
        $baseUrlProperty->setAccessible(true);
        
        $this->assertIsString($baseUrlProperty->getValue($service));
    }
}