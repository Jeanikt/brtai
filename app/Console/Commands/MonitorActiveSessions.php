<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorActiveSessions extends Command
{
    protected $signature = 'monitor:active-sessions';
    protected $description = 'Conta quantas sessões estão ativas no Redis e envia para o Discord';

    public function handle()
    {
        try {
            // Obtém todas as chaves de sessão do Redis
            $keys = Redis::connection('default')->keys('laravel:sessions:*');
            $activeSessions = count($keys);

            $this->info("Sessões ativas: {$activeSessions}");

            // Monta o embed para enviar ao Discord
            $embed = [
                'title' => '📊 Monitoramento de Sessões Ativas',
                'description' => "Atualmente há **{$activeSessions}** usuários ativos no site 🟢",
                'color' => 0x00FF00,
                'fields' => [
                    [
                        'name' => '🕒 Data/Hora',
                        'value' => now()->toDateTimeString(),
                        'inline' => true,
                    ],
                    [
                        'name' => '🌍 Ambiente',
                        'value' => config('app.env'),
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => config('app.name') . ' • Monitoramento Redis',
                    'icon_url' => 'https://laravel.com/img/logomark.min.svg',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            // ✅ Webhook fixo para envio das sessões
            $webhookUrl = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($webhookUrl, [
                    'username' => 'BrotaAI Monitor 🧱',
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [$embed],
                ]);

            Log::info("✅ Enviado para Discord: {$activeSessions} sessões ativas.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('❌ Erro ao monitorar sessões: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
