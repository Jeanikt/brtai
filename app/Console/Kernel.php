<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    // ✅ Aqui você registra os comandos personalizados
    protected $commands = [
        \App\Console\Commands\TestSupabaseConnection::class,
        \App\Console\Commands\TestDiscordLog::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        //
    }

    protected function commands()
    {
        // ✅ Aqui você só carrega a pasta de comandos
        $this->load(__DIR__ . '/Commands');
    }
}
