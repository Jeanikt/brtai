<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\DiscordMonitorService;

class DailyDiscordReport extends Command
{
    protected $signature = 'monitor:daily-report {--debug}';
    protected $description = 'Envia relatório diário de performance, usuários e uptime para o Discord';

    public function handle()
    {
        try {
            /** 📊 Coleta de métricas básicas */
            $totalUsers = DB::table('users')->count();
            $newUsers = DB::table('users')->where('created_at', '>=', now()->subDay())->count();
            $planStats = DB::table('profiles')
                ->select('plan_type', DB::raw('COUNT(*) as total'))
                ->groupBy('plan_type')
                ->pluck('total', 'plan_type')
                ->toArray();

            $activeSessions = count(Cache::get('active_sessions', []));
            $failedJobs = DB::table('failed_jobs')->count();
            $queuedJobs = DB::table('jobs')->count();

            /** 🌎 Acessos recentes */
            $lastSessions = DB::table('sessions')
                ->select('ip_address', 'user_id', 'last_activity')
                ->where('last_activity', '>', now()->subDay()->timestamp)
                ->get();

            $accessCount = $lastSessions->count();

            $ipSummary = collect($lastSessions)
                ->groupBy('ip_address')
                ->map(fn($g) => $g->count())
                ->sortDesc()
                ->take(3);

            // Localização dos IPs mais ativos
            $topIps = $ipSummary->isNotEmpty()
                ? collect($ipSummary)->map(function ($c, $ip) {
                    $geo = DiscordMonitorService::getGeoInfo($ip);
                    return "{$ip} ({$c}x) - " . ($geo ?? 'Local desconhecido');
                })->implode("\n")
                : 'Sem dados recentes';

            /** 💸 Transações e eventos */
            $events = DB::table('events')->count();
            $transactions = DB::table('payment_transactions')->count();
            $revenue = DB::table('payment_transactions')->sum('amount');

            /** 📡 Uptime (UptimeRobot API) */
            $apiKey = 'm801722019-8a895d7d362184303a3839fb';
            $response = Http::withOptions(['verify' => false])
                ->asForm()
                ->post('https://api.uptimerobot.com/v2/getMonitors', [
                    'api_key' => $apiKey,
                    'format' => 'json',
                    'logs' => 1,
                    'custom_uptime_ratios' => '30',
                ]);

            $data = $response->json();
            $monitor = $data['monitors'][0] ?? null;

            $monitorName = $monitor['friendly_name'] ?? 'Servidor Principal';
            $uptime = $monitor['custom_uptime_ratio'] ?? 'N/A';
            $status = match ($monitor['status'] ?? 0) {
                2 => '🟢 Online',
                8 => '🟠 Pausado',
                9 => '🔴 Offline',
                default => '⚪ Desconhecido',
            };

            /** 📤 Envia relatório completo */
            DiscordMonitorService::sendEmbed(
                DiscordMonitorService::WEBHOOK_ALERTAS,
                [
                    'title' => '📊 Relatório Diário - BrotaAI',
                    'color' => 0x00BFFF,
                    'fields' => [
                        ['name' => '👥 Usuários Totais', 'value' => (string) $totalUsers, 'inline' => true],
                        ['name' => '🆕 Novos (24h)', 'value' => (string) $newUsers, 'inline' => true],
                        ['name' => '📦 Planos', 'value' => json_encode($planStats, JSON_PRETTY_PRINT), 'inline' => false],
                        ['name' => '🟢 Sessões Ativas', 'value' => (string) $activeSessions, 'inline' => true],
                        ['name' => '📡 Acessos (24h)', 'value' => (string) $accessCount, 'inline' => true],
                        ['name' => '🌍 IPs Mais Ativos', 'value' => $topIps, 'inline' => false],
                        ['name' => '🎫 Eventos', 'value' => (string) $events, 'inline' => true],
                        ['name' => '💳 Transações', 'value' => (string) $transactions, 'inline' => true],
                        ['name' => '💰 Receita Total', 'value' => 'R$ ' . number_format($revenue, 2, ',', '.'), 'inline' => true],
                        ['name' => '💥 Jobs com Falha', 'value' => (string) $failedJobs, 'inline' => true],
                        ['name' => '⏳ Jobs Pendentes', 'value' => (string) $queuedJobs, 'inline' => true],
                        ['name' => '📈 Uptime (30 dias)', 'value' => "{$uptime}%", 'inline' => true],
                        ['name' => '📶 Status Atual', 'value' => $status, 'inline' => true],
                        ['name' => '📅 Gerado em', 'value' => now()->toDateTimeString(), 'inline' => false],
                    ],
                    'footer' => ['text' => 'BrotaAI • Monitoramento Diário Automático'],
                ]
            );

            $this->info('✅ Relatório diário completo enviado ao Discord!');
        } catch (\Throwable $e) {
            Log::error('Erro no relatório diário: ' . $e->getMessage());
            $this->error('❌ Falha ao enviar relatório diário: ' . $e->getMessage());
        }
    }
}
