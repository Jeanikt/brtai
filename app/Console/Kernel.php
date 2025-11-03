<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // ✅ Registra comandos personalizados
    protected $commands = [
        \App\Console\Commands\TestSupabaseConnection::class,
        \App\Console\Commands\TestDiscordLog::class,
        \App\Console\Commands\MonitorActiveSessions::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        // Exemplo: executa a cada hora
        // $schedule->command('monitor:active-sessions')->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
