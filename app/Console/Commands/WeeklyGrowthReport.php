<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\DiscordMonitorService;

class WeeklyGrowthReport extends Command
{
    protected $signature = 'monitor:weekly-growth';
    protected $description = 'Envia relatório semanal de crescimento ao Discord.';

    public function handle()
    {
        try {
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();

            $totalUsers = DB::table('users')->count();
            $newUsers = DB::table('users')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $lastWeekUsers = DB::table('users')
                ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                ->count();

            $growthRate = $lastWeekUsers > 0
                ? round(($newUsers - $lastWeekUsers) / $lastWeekUsers * 100, 2)
                : 100;

            $planStats = DB::table('profiles')
                ->select('plan_type', DB::raw('COUNT(*) as total'))
                ->groupBy('plan_type')
                ->pluck('total', 'plan_type')
                ->toArray();

            $revenue = DB::table('payment_transactions')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount');

            $activeUsers = DB::table('sessions')
                ->where('last_activity', '>=', now()->subWeek()->timestamp)
                ->distinct('user_id')
                ->count();

            DiscordMonitorService::sendEmbed(
                DiscordMonitorService::WEBHOOK_ALERTAS,
                [
                    'title' => '📈 Relatório Semanal de Crescimento - BrotaAI',
                    'color' => 0x0099FF,
                    'fields' => [
                        ['name' => '👥 Usuários Totais', 'value' => (string)$totalUsers, 'inline' => true],
                        ['name' => '🆕 Novos Usuários (semana)', 'value' => (string)$newUsers, 'inline' => true],
                        ['name' => '📊 Crescimento', 'value' => "{$growthRate}%", 'inline' => true],
                        ['name' => '💳 Receita Semana', 'value' => 'R$ ' . number_format($revenue, 2, ',', '.'), 'inline' => true],
                        ['name' => '🔥 Usuários Ativos', 'value' => (string)$activeUsers, 'inline' => true],
                        ['name' => '🏷️ Planos', 'value' => json_encode($planStats, JSON_PRETTY_PRINT), 'inline' => false],
                        ['name' => '📅 Semana', 'value' => "{$weekStart->format('d/m')} - {$weekEnd->format('d/m')}", 'inline' => false],
                    ],
                    'footer' => ['text' => 'BrotaAI • Relatório Semanal Automático'],
                ]
            );

            $this->info('✅ Relatório semanal enviado com sucesso!');
        } catch (\Throwable $e) {
            Log::error('Erro no relatório semanal: ' . $e->getMessage());
            $this->error('❌ Falha no relatório semanal: ' . $e->getMessage());
        }
    }
}
