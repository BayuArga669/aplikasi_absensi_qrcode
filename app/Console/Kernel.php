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
        // Run the absent marking command daily at 11:59 PM to mark absent for the current day
        $schedule->command('app:mark-daily-absent --date="yesterday"')->dailyAt('23:59');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        
        require base_path('routes/console.php');
        
    }
    protected $commands = [
        \App\Console\Commands\MakeServiceCommand::class,
        \App\Console\Commands\MarkDailyAbsent::class,
    ];
}
