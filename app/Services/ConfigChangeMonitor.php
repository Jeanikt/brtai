<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConfigChangeMonitor
{
private const DISCORD_SECURITY_WEBHOOK = 'https://discord.com/api/webhooks/1434564999999999999/example_security_hook';

public static function checkEnvChanges(): void
{
try {
$envHash = md5_file(base_path('.env'));
$lastHash = Cache::get('env_hash');

if ($lastHash && $envHash !== $lastHash) {
Http::withOptions(['verify' => false])->post(self::DISCORD_SECURITY_WEBHOOK, [
'username' => 'BrotaAI Security 🧱',
'embeds' => [[
'title' => '⚠️ Mudança Detectada no .env',
'color' => 0xFF0000,
'fields' => [
['name' => '📅 Data/Hora', 'value' => now()->toDateTimeString()],
['name' => '📂 Ambiente', 'value' => config('app.env')],
],
'footer' => ['text' => 'BrotaAI • Configuração Crítica'],
'timestamp' => now()->toIso8601String(),
]]
]);
}

Cache::put('env_hash', $envHash, 86400);
} catch (\Throwable $e) {
Log::debug('Erro ao monitorar alterações do .env: ' . $e->getMessage());
}
}
}
