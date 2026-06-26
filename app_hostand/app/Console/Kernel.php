<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Invia il PDF riepilogativo dei servizi giornalieri ogni sera alle 18:35
        $schedule->command('hostand:send-daily-pdf')
                 ->dailyAt('18:35')
                 ->timezone('Europe/Paris')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/daily-pdf.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
