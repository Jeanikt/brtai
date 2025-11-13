<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const DISCORD_SESSION_WEBHOOK = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $googleUser->getName() ?? 'Usuário Google',
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]
            );

            Profile::updateOrCreate(
                ['id' => $user->id],
                [
                    'full_name' => $googleUser->getName(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'plan_type' => 'freemium',
                    'metadata' => ['google_id' => $googleUser->getId()],
                ]
            );

            Auth::login($user, true);
            $this->addActiveSession($user->id);
            $this->sendDiscordSessionLog($request, $user, 'login');

            return redirect()->intended('/events-public');
        } catch (\Throwable $e) {
            Log::error('Erro no login Google: ' . $e->getMessage());
            $this->sendDiscordSessionLog($request, null, 'error');
            return redirect()->route('login')->withErrors(['error' => 'Falha no login Google.']);
        }
    }

    private function addActiveSession(string $userId): void
    {
        $sessions = Cache::get('active_sessions', []);
        $sessions[$userId] = now()->toDateTimeString();
        Cache::put('active_sessions', $sessions, 3600);
    }

    private function countActiveSessions(): int
    {
        return count(Cache::get('active_sessions', []));
    }

    private function sendDiscordSessionLog(Request $request, ?User $user, string $action): void
    {
        try {
            // ✅ Captura do IP real do usuário
            $ip = $request->header('X-Forwarded-For') ?? $request->ip();

            $userAgent = $request->header('User-Agent', 'Desconhecido');
            $browser = $this->detectBrowser($userAgent);
            $os = $this->detectOS($userAgent);
            $activeCount = $this->countActiveSessions();

            $title = $action === 'login' ? '🟢 Login via Google OAuth' : '❌ Erro Login Google OAuth';
            $color = $action === 'login' ? 0x00FF00 : 0xFF0000;

            $embed = [
                'title' => $title,
                'color' => $color,
                'fields' => [
                    ['name' => '🆔 User ID', 'value' => $user?->id ?? 'Desconhecido', 'inline' => true],
                    ['name' => '👤 Usuário', 'value' => $user?->email ?? 'Desconhecido', 'inline' => true],
                    ['name' => '🌐 IP', 'value' => $ip, 'inline' => true],
                    ['name' => '💻 Sistema Operacional', 'value' => $os, 'inline' => true],
                    ['name' => '🧭 Navegador', 'value' => $browser, 'inline' => true],
                    ['name' => '👥 Sessões Ativas', 'value' => (string) $activeCount, 'inline' => true],
                    ['name' => '📅 Data/Hora', 'value' => now()->toDateTimeString(), 'inline' => true],
                ],
                'footer' => ['text' => 'BrotaAI • Sessões Google OAuth'],
                'timestamp' => now()->toIso8601String(),
            ];

            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post(self::DISCORD_SESSION_WEBHOOK, [
                    'username' => 'BrotaAI Sessions 🧱',
                    'embeds' => [$embed],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar log Discord: ' . $e->getMessage());
        }
    }

    private function detectBrowser(?string $ua): string
    {
        if (!$ua) return 'Desconhecido';
        return match (true) {
            str_contains($ua, 'Opera') || str_contains($ua, 'OPR/') => 'Opera',
            str_contains($ua, 'Edge') => 'Edge',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Safari') => 'Safari',
            str_contains($ua, 'Firefox') => 'Firefox',
            default => 'Outro',
        };
    }

    private function detectOS(?string $ua): string
    {
        if (!$ua) return 'Desconhecido';
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac') => 'MacOS',
            str_contains($ua, 'Linux') => 'Linux',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            default => 'Outro',
        };
    }
}
