<?php

// app/Console/Commands/MonitorServerHealth.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorServerHealth extends Command
{
protected $signature = 'monitor:health';
protected $description = 'Verifica uso de CPU e memória e envia alerta ao Discord';

private const DISCORD_PERFORMANCE_WEBHOOK = 'https://discord.com/api/webhooks/1434564710359105648/X1gOYhzOwwAM1fcnZK58MTL8cypgQRGs_VGDdmupZHoJWjOQ_pP0wi7MZN41kN6sgzqQ';

public function handle()
{
try {
$load = sys_getloadavg()[0] ?? 0;
$memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);

if ($load > 5 || $memoryUsage > 500) {
Http::withOptions(['verify' => false])->post(self::DISCORD_PERFORMANCE_WEBHOOK, [
'username' => 'BrotaAI Monitor 🧱',
'embeds' => [[
'title' => '🚨 Alerta de Saúde do Servidor',
'color' => 0xFF0000,
'fields' => [
['name' => '💻 Carga Média (1min)', 'value' => (string)$load, 'inline' => true],
['name' => '🧠 Memória (MB)', 'value' => "{$memoryUsage} MB", 'inline' => true],
['name' => '📅 Data/Hora', 'value' => now()->toDateTimeString(), 'inline' => false],
],
'footer' => ['text' => 'BrotaAI • Server Health'],
'timestamp' => now()->toIso8601String(),
]]
]);
}
} catch (\Throwable $e) {
Log::debug('Erro ao verificar saúde do servidor: ' . $e->getMessage());
}
}
}
