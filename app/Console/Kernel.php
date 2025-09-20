<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Aqui você registra seus comandos personalizados
        \App\Console\Commands\FetchRiverData::class,
        \App\Console\Commands\TestAnaApi::class,
        \App\Console\Commands\GenerateRealisticData::class,
        \App\Console\Commands\UpdateHourlyData::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Executa o comando river:fetch a cada hora
        $schedule->command('river:fetch')->hourly();
        
        // Executa atualização de dados por hora
        $schedule->command('data:update-hourly')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
