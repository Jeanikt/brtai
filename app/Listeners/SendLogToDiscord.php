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
                'https://discord.com/api/webhooks/1434564585330835649/mfB_A9IigR8pCgxUxQoJJXWF5b1-KeF0xOUsTSK9rVHkOjpEO0oDfAMdpYS1NlOYSbxC',
                'info', 'notice' =>
                'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ',
                'payment', 'billing', 'transaction' =>
                'https://discord.com/api/webhooks/1434564823001333791/ZfcIOO_aF0CA4QXc2YyKwrSFsMqUKnMbC58ymkhX37pD2QFi0q8IivFqObkkuOfKfMRg',
                default => env('DISCORD_WEBHOOK_URL'),
            };

            if (!$webhookUrl) {
                Log::debug("Webhook Discord não definido para nível: {$level}");
                return;
            }

            $color = match ($level) {
                'error', 'critical', 'alert', 'emergency' => 0xFF0000,
                'warning' => 0xFFA500,
                'info', 'notice' => 0x00BFFF,
                'payment', 'billing', 'transaction' => 0x32CD32,
                default => 0x808080,
            };

            $embed = [
                'title' => '📜 ' . strtoupper($level) . ' Log',
                'description' => "**Mensagem:**\n```{$event->message}```",
                'color' => $color,
                'fields' => [
                    [
                        'name' => '📅 Data/Hora',
                        'value' => now()->toDateTimeString(),
                        'inline' => true,
                    ],
                    [
                        'name' => '📂 Ambiente',
                        'value' => config('app.env'),
                        'inline' => true,
                    ],
                ],
                'footer' => [
                    'text' => 'Laravel Logs • ' . config('app.name'),
                    'icon_url' => 'https://laravel.com/img/logomark.min.svg',
                ],
                'timestamp' => now()->toIso8601String(),
            ];

            if (!empty($event->context)) {
                $contextJson = json_encode($event->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                $embed['fields'][] = [
                    'name' => '🧩 Contexto',
                    'value' => "```json\n{$contextJson}\n```",
                    'inline' => false,
                ];
            }

            if (isset($event->context['exception']) && $event->context['exception'] instanceof \Throwable) {
                $exception = $event->context['exception'];
                $embed['fields'][] = [
                    'name' => '💥 Exceção',
                    'value' => sprintf(
                        "**%s:** %s\n```%s```",
                        get_class($exception),
                        $exception->getMessage(),
                        substr($exception->getTraceAsString(), 0, 1000)
                    ),
                    'inline' => false,
                ];
            }

            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($webhookUrl, [
                    'username' => 'BrotaAI Logs 🧱',
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [$embed],
                ]);

            // Envia notificação de deploy (se aplicável)
            if ($level === 'info' && str_contains($event->message, 'deploy')) {
                Http::post(env('DEPLOY_TRIGGER_URL'), [
                    'status' => 'success',
                    'project' => config('app.name'),
                    'timestamp' => now()->toIso8601String(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::debug('Erro ao enviar log para Discord: ' . $e->getMessage());
        }
    }
}
