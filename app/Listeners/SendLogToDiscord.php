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
            $webhookUrl = env('DISCORD_WEBHOOK_URL') ??
                "https://discord.com/api/webhooks/1433984396494508193/IINi7WkTVJsGdvtz2hLvJEsguFnp9lfcqiwsmkfh00Nv3f6BNjHluczZ5KjHYmLhAxgt";

            if (!$webhookUrl) {
                Log::debug('Webhook Discord não configurado.');
                return;
            }

            Http::withOptions(['verify' => false]) // apenas para teste local
                ->timeout(5)
                ->post($webhookUrl, [
                    'username' => 'BrotaAI Logs 🧱',
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [[
                        'title' => strtoupper($event->level) . ' Log',
                        'description' => $event->message,
                        'color' => $event->level === 'error' ? 16711680 : 65280, // vermelho/verde
                        'timestamp' => now()->toIso8601String(),
                    ]],
                ]);
        } catch (\Throwable $e) {
            Log::debug('Erro ao enviar log para Discord: ' . $e->getMessage());
        }
    }
}
