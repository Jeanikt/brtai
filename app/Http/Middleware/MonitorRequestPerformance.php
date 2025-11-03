<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorRequestPerformance
{
    private const DISCORD_PERFORMANCE_WEBHOOK = 'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ';

    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $duration = microtime(true) - $start;

        if ($duration > 3) { // >3 segundos
            try {
                Http::withOptions(['verify' => false])->timeout(5)->post(self::DISCORD_PERFORMANCE_WEBHOOK, [
                    'username' => 'BrotaAI Monitor 🧱',
                    'embeds' => [[
                        'title' => '🐢 Requisição Lenta Detectada',
                        'color' => 0xFFA500,
                        'fields' => [
                            ['name' => '📄 URL', 'value' => $request->fullUrl(), 'inline' => false],
                            ['name' => '⏱️ Duração', 'value' => number_format($duration, 2) . 's', 'inline' => true],
                            ['name' => '👤 Usuário', 'value' => Auth::user()->email ?? 'Guest', 'inline' => true],
                        ],
                        'footer' => ['text' => 'BrotaAI • Request Performance'],
                        'timestamp' => now()->toIso8601String(),
                    ]]
                ]);
            } catch (\Throwable $e) {
                Log::debug('Falha ao enviar alerta de performance: ' . $e->getMessage());
            }
        }

        return $response;
    }
}
