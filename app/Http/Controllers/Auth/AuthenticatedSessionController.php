<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    private const DISCORD_SESSION_WEBHOOK = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

    /**
     * Exibe o formulário de login (via Inertia)
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    /**
     * Login do usuário
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('Invalid credentials.'),
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        $this->addActiveSession($user->id);
        $this->sendDiscordSessionLog($request, $user, 'login');

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Logout do usuário
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();
        $this->removeActiveSession($user?->id);

        $this->sendDiscordSessionLog($request, $user, 'logout');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * === Sessões ativas (Cache) ===
     */
    private function addActiveSession(string $userId): void
    {
        $key = 'active_sessions';
        $sessions = Cache::get($key, []);
        $sessions[$userId] = now()->toDateTimeString();
        Cache::put($key, $sessions, 3600);
    }

    private function removeActiveSession(?string $userId): void
    {
        if (!$userId) return;
        $key = 'active_sessions';
        $sessions = Cache::get($key, []);
        unset($sessions[$userId]);
        Cache::put($key, $sessions, 3600);
    }

    private function countActiveSessions(): int
    {
        return count(Cache::get('active_sessions', []));
    }

    /**
     * === Log para Discord ===
     */
    private function sendDiscordSessionLog(Request $request, ?User $user, string $action): void
    {
        try {
            $ip = $request->ip();
            $userAgent = $request->header('User-Agent', 'Desconhecido');
            $browser = $this->detectBrowser($userAgent);
            $os = $this->detectOS($userAgent);
            $activeCount = $this->countActiveSessions();

            $title = $action === 'login' ? '🟢 Novo Login Detectado' : '🔴 Logout Realizado';
            $color = $action === 'login' ? 0x00FF00 : 0xFF0000;

            $embed = [
                'title' => $title,
                'color' => $color,
                'fields' => [
                    ['name' => '🆔 User ID', 'value' => $user?->id ?? 'Desconhecido', 'inline' => true],
                    ['name' => '👤 Usuário', 'value' => $user?->email ?? 'Desconhecido', 'inline' => true],
                    ['name' => '📅 Data/Hora', 'value' => now()->toDateTimeString(), 'inline' => true],
                    ['name' => '🌐 IP', 'value' => $ip, 'inline' => true],
                    ['name' => '🧭 Navegador', 'value' => $browser, 'inline' => true],
                    ['name' => '💻 Sistema Operacional', 'value' => $os, 'inline' => true],
                    ['name' => '👥 Sessões Ativas', 'value' => (string) $activeCount, 'inline' => true],
                ],
                'footer' => ['text' => 'BrotaAI • Sessões'],
                'timestamp' => now()->toIso8601String(),
            ];

            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post(self::DISCORD_SESSION_WEBHOOK, [
                    'username' => 'BrotaAI Sessions 🧱',
                    'embeds' => [$embed],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar log do Discord: ' . $e->getMessage());
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
            str_contains($ua, 'MSIE') || str_contains($ua, 'Trident') => 'Internet Explorer',
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
