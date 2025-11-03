<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordMonitorService
{
    public const WEBHOOK_SESSIONS = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

    public static function sessionEvent(
        string $action,
        $user,
        string $ip,
        string $browser,
        string $os,
        string $loginType = 'manual',
        ?array $coords = null
    ): void {
        try {
            $geo = $coords
                ? self::getGeoInfoFromCoords($coords['latitude'], $coords['longitude'])
                : self::getGeoInfo($ip);

            $activeSessions = Cache::get('active_sessions', []);
            $activeCount = count($activeSessions);
            $color = $action === 'login' ? 0x00FF00 : 0xFF0000;
            $title = $action === 'login' ? '🟢 Login Detectado' : '🔴 Logout Detectado';
            $sessionDuration = '-';

            if ($action === 'logout' && isset($activeSessions[$user->id]['started_at'])) {
                $start = $activeSessions[$user->id]['started_at'];
                $sessionDuration = now()->diffInMinutes($start) . ' min';
                unset($activeSessions[$user->id]);
            } elseif ($action === 'login') {
                $activeSessions[$user->id] = ['started_at' => now()];
            }

            Cache::put('active_sessions', $activeSessions, 86400);

            $fields = [
                ['name' => '👤 Usuário', 'value' => $user?->email ?? 'Desconhecido', 'inline' => true],
                ['name' => '🆔 ID', 'value' => (string)($user?->id ?? '-'), 'inline' => true],
                ['name' => '🔐 Tipo de Login', 'value' => ucfirst($loginType), 'inline' => true],
                ['name' => '🌐 IP', 'value' => $ip, 'inline' => true],
                ['name' => '💻 SO', 'value' => $os, 'inline' => true],
                ['name' => '🧭 Navegador', 'value' => $browser, 'inline' => true],
                ['name' => '👥 Sessões Ativas', 'value' => (string)$activeCount, 'inline' => true],
                ['name' => '⏱️ Duração', 'value' => $sessionDuration, 'inline' => true],
            ];

            if ($geo) {
                $fields[] = [
                    'name' => '📍 Localização Real',
                    'value' => "{$geo['city']}, {$geo['region']}, {$geo['country']} \n[🌎 Ver no mapa]({$geo['map_link']})",
                    'inline' => false,
                ];
            }

            self::sendEmbed(self::WEBHOOK_SESSIONS, [
                'title' => $title,
                'color' => $color,
                'fields' => $fields,
                'footer' => ['text' => 'BrotaAI • Sessões'],
            ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar log de sessão: ' . $e->getMessage());
        }
    }

    public static function getGeoInfo(string $ip): ?array
    {
        try {
            if (in_array($ip, ['127.0.0.1', '::1'])) {
                return ['city' => 'Localhost', 'region' => '-', 'country' => '-', 'map_link' => 'https://maps.google.com'];
            }

            return Cache::remember("geo_ip_{$ip}", now()->addHours(6), function () use ($ip) {
                $res = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");
                if ($res->ok()) {
                    $d = $res->json();
                    return [
                        'city' => $d['city'] ?? '-',
                        'region' => $d['region'] ?? '-',
                        'country' => $d['country_name'] ?? '-',
                        'map_link' => isset($d['latitude'], $d['longitude'])
                            ? "https://www.google.com/maps?q={$d['latitude']},{$d['longitude']}"
                            : 'https://maps.google.com',
                    ];
                }
                return null;
            });
        } catch (Throwable) {
            return null;
        }
    }

    public static function getGeoInfoFromCoords(float $lat, float $lng): ?array
    {
        return [
            'city' => 'Localização informada pelo usuário',
            'region' => 'GPS',
            'country' => '',
            'map_link' => "https://www.google.com/maps?q={$lat},{$lng}",
        ];
    }

    public static function sendEmbed(string $webhook, array $embed): void
    {
        try {
            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($webhook, [
                    'username' => 'BrotaAI Monitor 🧱',
                    'embeds' => [$embed],
                ]);
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar embed para o Discord: ' . $e->getMessage());
        }
    }
}
