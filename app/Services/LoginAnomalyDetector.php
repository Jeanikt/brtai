<?php
// app/Services/LoginAnomalyDetector.php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoginAnomalyDetector
{
    private const DISCORD_SECURITY_WEBHOOK = 'https://discord.com/api/webhooks/1434564999999999999/example_security_hook';

    public static function checkLogin(string $userId, string $ip): void
    {
        try {
            $cacheKey = "user_ip_history:{$userId}";
            $ips = Cache::get($cacheKey, []);

            if (!in_array($ip, $ips)) {
                if (count($ips) >= 3) {
                    Http::withOptions(['verify' => false])->post(self::DISCORD_SECURITY_WEBHOOK, [
                        'username' => 'BrotaAI Security 🧱',
                        'embeds' => [[
                            'title' => '⚠️ Login suspeito detectado',
                            'color' => 0xFF4500,
                            'fields' => [
                                ['name' => '👤 User ID', 'value' => $userId],
                                ['name' => '🌍 Novo IP', 'value' => $ip],
                                ['name' => '📅 Data/Hora', 'value' => now()->toDateTimeString()],
                            ],
                            'footer' => ['text' => 'BrotaAI • Security System'],
                            'timestamp' => now()->toIso8601String(),
                        ]]
                    ]);
                }

                $ips[] = $ip;
                if (count($ips) > 5) array_shift($ips);
                Cache::put($cacheKey, $ips, 86400);
            }
        } catch (\Throwable $e) {
            Log::debug('Erro ao verificar login suspeito: ' . $e->getMessage());
        }
    }
}
