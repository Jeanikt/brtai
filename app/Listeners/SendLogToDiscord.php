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
            $message = $event->message;

            // Define o webhook conforme o tipo de log (prioridade para pagamentos)
            $webhookUrl = $this->getWebhookForLog($level, $message);

            if (!$webhookUrl) {
                Log::debug("Webhook Discord não definido para nível: {$level}");
                return;
            }

            // Monta o embed
            $embed = $this->createEmbed($level, $message, $event->context);

            // Envia para Discord
            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($webhookUrl, [
                    'username' => 'BrotaAI Logs 🧱',
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [$embed],
                ]);
        } catch (\Throwable $e) {
            Log::debug('Erro ao enviar log para Discord: ' . $e->getMessage());
        }
    }

    private function getWebhookForLog(string $level, string $message): ?string
    {
        // PRIORIDADE: Logs de pagamento vão para webhook de pagamentos
        if ($this->isPaymentLog($message)) {
            return 'https://discord.com/api/webhooks/1434564823001333791/ZfcIOO_aF0CA4QXc2YyKwrSFsMqUKnMbC58ymkhX37pD2QFi0q8IivFqObkkuOfKfMRg';
        }

        // Logs de erro vão para webhook de erros
        if (in_array($level, ['error', 'critical', 'alert', 'emergency'])) {
            return 'https://discord.com/api/webhooks/1434564585330835649/mfB_A9IigR8pCgxUxQoJJXWF5b1-KeF0xOUsTSK9rVHkOjpEO0oDfAMdpYS1NlOYSbxC';
        }

        // Logs de info/notice vão para webhook geral
        if (in_array($level, ['info', 'notice'])) {
            return 'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ';
        }

        return env('DISCORD_WEBHOOK_URL');
    }

    private function isPaymentLog(string $message): bool
    {
        $paymentKeywords = [
            'PIX',
            'payment',
            'Woovi',
            'charge',
            'transaction',
            'pagamento',
            'cobrança',
            'pix_code',
            'qr_code',
            'gerating new pix',
            'pix payment',
            'woovi charge',
            'payment status',
            'marking participant as paid'
        ];

        foreach ($paymentKeywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function createEmbed(string $level, string $message, array $context): array
    {
        // Define a cor do embed conforme o nível do log
        $color = match ($level) {
            'error', 'critical', 'alert', 'emergency' => 0xFF0000,
            'warning' => 0xFFA500,
            'info', 'notice' => 0x00BFFF,
            'payment', 'billing', 'transaction' => 0x32CD32,
            default => 0x808080,
        };

        // Se for log de pagamento, usa cor verde
        if ($this->isPaymentLog($message)) {
            $color = 0x32CD32;
        }

        $embed = [
            'title' => '📜 ' . strtoupper($level) . ' Log',
            'description' => "**Mensagem:**\n```{$message}```",
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

        // Inclui o contexto, se existir
        if (!empty($context)) {
            $contextJson = json_encode(
                $context,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );

            // Limita o tamanho do contexto para não exceder limites do Discord
            if (strlen($contextJson) > 1000) {
                $contextJson = substr($contextJson, 0, 1000) . "\n... (truncated)";
            }

            $embed['fields'][] = [
                'name' => '🧩 Contexto',
                'value' => "```json\n{$contextJson}\n```",
                'inline' => false,
            ];
        }

        // Inclui exceção (stack trace)
        if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
            $exception = $context['exception'];
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

        return $embed;
    }
}
