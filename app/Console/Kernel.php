<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('broadcast:time')->everySecond();

        // Cierre semanal de la liga (lunes 00:00 UTC): ascensos/descensos de
        // división y reinicio de los puntos semanales. withoutOverlapping evita
        // que se solape si una corrida tardara.
        $schedule->command('leagues:rollover')
            ->weeklyOn(1, '00:00')
            ->timezone('UTC')
            ->withoutOverlapping();
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
