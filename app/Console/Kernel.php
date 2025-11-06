<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * ✅ Registra os comandos personalizados do monitoramento
     */
    protected $commands = [
        \App\Console\Commands\TestSupabaseConnection::class,
        \App\Console\Commands\TestDiscordLog::class,
        \App\Console\Commands\MonitorActiveSessions::class,
        \App\Console\Commands\DailyDiscordReport::class,
        \App\Console\Commands\WeeklyGrowthReport::class,
        \App\Console\Commands\TestWooviConnection::class,
        \App\Console\Commands\TestWooviCharge::class,
    ];

    /**
     * 📅 Agenda de execução automática dos comandos
     */
    protected function schedule(Schedule $schedule)
    {
        // 🔹 Executa relatório diário às 09h00
        $schedule->command('monitor:daily-report')->dailyAt('09:00');

        // 🔹 Executa o relatório semanal de crescimento aos domingos às 09h00
        $schedule->command('monitor:weekly-growth')->sundays()->at('09:00');

        // 🔹 (Opcional) monitoramento de sessões a cada hora
        // $schedule->command('monitor:active-sessions')->hourly();
    }

    /**
     * 🔄 Carrega comandos da pasta App\Console\Commands
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }
}
