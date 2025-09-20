<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/*
|--------------------------------------------------------------------------
| Health Check Routes
|--------------------------------------------------------------------------
|
| Rotas para verificação de saúde da aplicação no Fly.io
|
*/

Route::get('/health', function () {
    try {
        // Verificar conexão com banco de dados
        DB::connection()->getPdo();
        
        // Verificar cache
        Cache::put('health_check', 'ok', 10);
        $cacheStatus = Cache::get('health_check') === 'ok';
        
        // Verificar storage
        $storageWritable = is_writable(storage_path());
        
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'database' => 'connected',
            'cache' => $cacheStatus ? 'working' : 'error',
            'storage' => $storageWritable ? 'writable' : 'readonly',
            'version' => config('app.version', '1.2.0'),
            'environment' => app()->environment(),
        ];
        
        return response()->json($status, 200);
        
    } catch (Exception $e) {
        $status = [
            'status' => 'unhealthy',
            'timestamp' => now()->toISOString(),
            'error' => $e->getMessage(),
            'version' => config('app.version', '1.2.0'),
            'environment' => app()->environment(),
        ];
        
        return response()->json($status, 503);
    }
});

Route::get('/health/detailed', function () {
    try {
        $checks = [
            'database' => false,
            'cache' => false,
            'storage' => false,
            'config' => false,
            'routes' => false,
        ];
        
        // Verificar banco de dados
        try {
            DB::connection()->getPdo();
            $checks['database'] = true;
        } catch (Exception $e) {
            $checks['database'] = false;
        }
        
        // Verificar cache
        try {
            Cache::put('health_check', 'ok', 10);
            $checks['cache'] = Cache::get('health_check') === 'ok';
        } catch (Exception $e) {
            $checks['cache'] = false;
        }
        
        // Verificar storage
        $checks['storage'] = is_writable(storage_path()) && is_writable(bootstrap_path('cache'));
        
        // Verificar configuração
        $checks['config'] = config('app.key') !== null && config('app.url') !== null;
        
        // Verificar rotas
        $checks['routes'] = Route::has('dashboard.index');
        
        $allHealthy = collect($checks)->every(fn($check) => $check === true);
        
        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'version' => config('app.version', '1.2.0'),
            'environment' => app()->environment(),
        ], $allHealthy ? 200 : 503);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'timestamp' => now()->toISOString(),
            'error' => $e->getMessage(),
            'version' => config('app.version', '1.2.0'),
            'environment' => app()->environment(),
        ], 500);
    }
});
