<?php

namespace App\Listeners;

use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendLogToDiscord
{
    public function handle(MessageLogged $event): void
    {
        try {
            $level = strtolower($event->level);

            // Define qual webhook será usado
            $webhookUrl = match ($level) {
                'error', 'critical', 'alert', 'emergency' =>
                'https://discord.com/api/webhooks/1434564585330835649/mfB_A9IigR8pCgxUxQoJJXWF5b1-KeF0xOUsTSK9rVHkOjpEO0oDfAMdpYS1NlOYSbxC', // erros
                'info', 'notice' =>
                'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ', // informações de usuário
                'payment', 'billing', 'transaction' =>
                'https://discord.com/api/webhooks/1434564823001333791/ZfcIOO_aF0CA4QXc2YyKwrSFsMqUKnMbC58ymkhX37pD2QFi0q8IivFqObkkuOfKfMRg', // pagamentos
                default => env('DISCORD_WEBHOOK_URL'), // fallback geral
            };

            if (!$webhookUrl) {
                Log::debug("Webhook Discord não definido para nível: {$level}");
                return;
            }

            // Ícones e cores personalizados por tipo
            [$emoji, $color] = match ($level) {
                'error', 'critical', 'alert', 'emergency' => ['💥', 0xFF0000],
                'warning' => ['⚠️', 0xFFA500],
                'info', 'notice' => ['🧍', 0x00BFFF],
                'payment', 'billing', 'transaction' => ['💰', 0x32CD32],
                'debug' => ['🐛', 0x808080],
                default => ['⚙️', 0x7289DA], // azul padrão do Discord
            };

            $appName = config('app.name');
            $env = config('app.env');
            $timestamp = now()->toIso8601String();

            // Monta embed detalhado e visualmente limpo
            $embed = [
                'title' => "{$emoji} " . strtoupper($level) . " • {$appName}",
                'description' => "**Mensagem:**\n```{$event->message}```",
                'color' => $color,
                'fields' => [
                    [
                        'name' => '📅 Data/Hora',
                        'value' => now()->format('d/m/Y H:i:s'),
                        'inline' => true,
                    ],
                    [
                        'name' => '🌐 Ambiente',
                        'value' => strtoupper($env),
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => "Laravel • {$appName}",
                    'icon_url' => 'https://laravel.com/img/logomark.min.svg',
                ],
                'timestamp' => $timestamp,
            ];

            // Contexto adicional (ex: user_id, rota, payload, etc.)
            if (!empty($event->context)) {
                $contextJson = json_encode(
                    $event->context,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                );

                $embed['fields'][] = [
                    'name' => '🧩 Contexto',
                    'value' => "```json\n{$contextJson}\n```",
                    'inline' => false,
                ];
            }

            // Stack trace em erros
            if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
                $exception = $event->context['exception'];

                $embed['fields'][] = [
                    'name' => '💀 Exceção',
                    'value' => sprintf(
                        "**%s:** %s\n```%s```",
                        get_class($exception),
                        $exception->getMessage(),
                        substr($exception->getTraceAsString(), 0, 1500)
                    ),
                    'inline' => false,
                ];
            }

            // Envia para o Discord
            Http::withOptions(['verify' => false])
                ->timeout(8)
                ->post($webhookUrl, [
                    'username' => "{$appName} Logs",
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [$embed],
                ]);
        } catch (\Throwable $e) {
            Log::debug('Erro ao enviar log para Discord: ' . $e->getMessage());
        }
    }
}
