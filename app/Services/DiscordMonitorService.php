<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class DiscordMonitorService
{
    /** 🧱 Webhooks principais */
    public const WEBHOOK_ALERTAS     = 'https://discord.com/api/webhooks/1434928480809648310/YUnBBjti_eRuX6pyXOQhmgXz8RXHKxYYCSoYgYRhMrug6lNwNKtZ0JDpvNqaA7O_rIS6';
    public const WEBHOOK_ERRORS      = 'https://discord.com/api/webhooks/1434564585330835649/mfB_A9IigR8pCgxUxQoJJXWF5b1-KeF0xOUsTSK9rVHkOjpEO0oDfAMdpYS1NlOYSbxC';
    public const WEBHOOK_PERFORMANCE = 'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ';
    public const WEBHOOK_UPGRADE     = 'https://discord.com/api/webhooks/1434564823001333791/ZfcIOO_aF0CA4QXc2YyKwrSFsMqUKnMbC58ymkhX37pD2QFi0q8IivFqObkkuOfKfMRg';
    public const WEBHOOK_SESSIONS    = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

    /** ============================================
     *  🔹 LOGIN / LOGOUT MONITORAMENTO + SEGURANÇA
     * ============================================ */
    public static function sessionEvent(string $action, $user, string $ip, string $browser, string $os, string $loginType = 'manual'): void
    {
        try {
            $geo = self::getGeoInfo($ip);
            $activeSessions = Cache::get('active_sessions', []);
            $activeCount = count($activeSessions);
            $color = $action === 'login' ? 0x00FF00 : 0xFF0000;
            $title = $action === 'login' ? '🟢 Login Detectado' : '🔴 Logout Detectado';
            $sessionDuration = '-';

            if ($action === 'logout' && isset($activeSessions[$user->id]['started_at'])) {
                $start = $activeSessions[$user->id]['started_at'];
                $sessionDuration = now()->diffInMinutes($start) . ' min';
                unset($activeSessions[$user->id]);
                Cache::put('active_sessions', $activeSessions, 86400);
            } elseif ($action === 'login') {
                $activeSessions[$user->id] = ['started_at' => now()];
                Cache::put('active_sessions', $activeSessions, 86400);
            }

            // Envia log da sessão
            self::sendEmbed(self::WEBHOOK_SESSIONS, [
                'title' => $title,
                'color' => $color,
                'fields' => [
                    ['name' => '👤 Usuário', 'value' => $user?->email ?? 'Desconhecido', 'inline' => true],
                    ['name' => '🆔 ID', 'value' => (string)($user?->id ?? '-'), 'inline' => true],
                    ['name' => '🔐 Tipo de Login', 'value' => ucfirst($loginType), 'inline' => true],
                    ['name' => '🌐 IP', 'value' => $ip, 'inline' => true],
                    ['name' => '📍 Localização', 'value' => $geo ?? 'Indisponível', 'inline' => true],
                    ['name' => '💻 SO', 'value' => $os, 'inline' => true],
                    ['name' => '🧭 Navegador', 'value' => $browser, 'inline' => true],
                    ['name' => '👥 Sessões Ativas', 'value' => (string)$activeCount, 'inline' => true],
                    ['name' => '⏱️ Duração', 'value' => $sessionDuration, 'inline' => true],
                ],
                'footer' => ['text' => 'BrotaAI • Sessões'],
            ]);

            // Detecta login suspeito
            if ($action === 'login') {
                self::detectSuspiciousLogin($user, $ip, $geo, $browser, $os);
            }
        } catch (Throwable $e) {
            Log::warning('Falha ao enviar log de sessão: ' . $e->getMessage());
        }
    }

    /** ⚠️ Detecta logins suspeitos */
    protected static function detectSuspiciousLogin($user, string $ip, ?string $location, string $browser, string $os): void
    {
        try {
            $userKey = "user_login_history_{$user->id}";
            $history = Cache::get($userKey, []);
            $recent = collect($history)->filter(fn($t) => now()->diffInMinutes($t['time']) <= 5)->values();

            $suspicious = false;
            $reason = '';

            if ($recent->isNotEmpty() && $recent->where('ip', '!=', $ip)->count() > 0) {
                $suspicious = true;
                $reason = 'Login de múltiplos IPs em curto intervalo.';
            }

            if (strpos(strtolower($location ?? ''), 'brazil') === false && strpos(strtolower($location ?? ''), 'brasil') === false) {
                $suspicious = true;
                $reason = $reason ? "$reason / Fora do Brasil." : 'Localização fora do Brasil.';
            }

            $history[] = ['ip' => $ip, 'location' => $location, 'time' => now()];
            Cache::put($userKey, array_slice($history, -10), 86400);

            if ($suspicious) {
                self::sendEmbed(self::WEBHOOK_ALERTAS, [
                    'title' => '⚠️ Login Suspeito Detectado',
                    'color' => 0xFFA500,
                    'fields' => [
                        ['name' => '👤 Usuário', 'value' => $user->email ?? 'Desconhecido', 'inline' => true],
                        ['name' => '🆔 ID', 'value' => (string)$user->id ?? '-', 'inline' => true],
                        ['name' => '🌍 Localização', 'value' => $location ?? 'Indisponível', 'inline' => true],
                        ['name' => '🔐 IP', 'value' => $ip, 'inline' => true],
                        ['name' => '💻 SO', 'value' => $os, 'inline' => true],
                        ['name' => '🧭 Navegador', 'value' => $browser, 'inline' => true],
                        ['name' => '🕒 Horário', 'value' => now()->toDateTimeString(), 'inline' => false],
                        ['name' => '⚠️ Motivo', 'value' => $reason, 'inline' => false],
                    ],
                    'footer' => ['text' => 'BrotaAI • Segurança'],
                ]);
            }
        } catch (Throwable $e) {
            Log::warning("Falha na detecção de login suspeito: {$e->getMessage()}");
        }
    }

    /** 🌍 Geolocalização via ipapi.co */
    public static function getGeoInfo(string $ip): ?string
    {
        try {
            if (in_array($ip, ['127.0.0.1', '::1'])) {
                return 'Localhost';
            }

            return Cache::remember("geo_{$ip}", now()->addHours(6), function () use ($ip) {
                $response = Http::timeout(3)->get("https://ipapi.co/{$ip}/json/");
                if ($response->ok()) {
                    $data = $response->json();
                    return "{$data['city']}, {$data['region']}, {$data['country_name']}";
                }
                return null;
            });
        } catch (Throwable $e) {
            Log::info("Geolocalização falhou para IP {$ip}");
            return null;
        }
    }

    /** 💥 Reporta exceções automaticamente */
    public static function reportException(Throwable $exception): void
    {
        try {
            $message = $exception->getMessage();
            $file = $exception->getFile();
            $line = $exception->getLine();

            self::sendEmbed(self::WEBHOOK_ERRORS, [
                'title' => '💥 Exceção Detectada',
                'color' => 0xFF0000,
                'fields' => [
                    ['name' => '🧩 Tipo', 'value' => get_class($exception), 'inline' => false],
                    ['name' => '📄 Arquivo', 'value' => "{$file}:{$line}", 'inline' => false],
                    ['name' => '💬 Mensagem', 'value' => substr($message, 0, 1000), 'inline' => false],
                    ['name' => '🕒 Data/Hora', 'value' => now()->toDateTimeString(), 'inline' => false],
                    ['name' => '📂 Ambiente', 'value' => config('app.env'), 'inline' => true],
                ],
                'footer' => ['text' => 'BrotaAI • Monitoramento de Erros'],
            ]);
        } catch (Throwable $e) {
            Log::error('Falha ao enviar log de erro ao Discord: ' . $e->getMessage());
        }
    }

    /** 📊 Relatório diário (24h) */
    public static function sendDailyReport(): void
    {
        try {
            $since = now()->subDay();
            $totalUsers = DB::table('users')->count();
            $newUsers = DB::table('users')->where('created_at', '>=', $since)->count();
            $totalAccess = DB::table('sessions')->where('last_activity', '>=', $since->timestamp)->count();
            $topCountry = DB::table('users')->select('country', DB::raw('COUNT(*) as total'))
                ->groupBy('country')->orderByDesc('total')->limit(1)->value('country');
            $plans = DB::table('profiles')->select('plan_type', DB::raw('COUNT(*) as total'))
                ->groupBy('plan_type')->pluck('total', 'plan_type')->toArray();

            self::sendEmbed(self::WEBHOOK_ALERTAS, [
                'title' => '📅 Relatório Diário - BrotaAI',
                'color' => 0x0099FF,
                'fields' => [
                    ['name' => '👥 Usuários Totais', 'value' => (string)$totalUsers, 'inline' => true],
                    ['name' => '🆕 Novos (24h)', 'value' => (string)$newUsers, 'inline' => true],
                    ['name' => '🌎 País mais ativo', 'value' => $topCountry ?? 'N/A', 'inline' => true],
                    ['name' => '📈 Acessos (24h)', 'value' => (string)$totalAccess, 'inline' => true],
                    ['name' => '🏷️ Planos', 'value' => json_encode($plans, JSON_PRETTY_PRINT), 'inline' => false],
                ],
                'footer' => ['text' => 'BrotaAI • Relatório Diário'],
            ]);
        } catch (Throwable $e) {
            Log::error('Erro ao enviar relatório diário: ' . $e->getMessage());
        }
    }

    /** ✅ Envio universal de embeds ao Discord */
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
