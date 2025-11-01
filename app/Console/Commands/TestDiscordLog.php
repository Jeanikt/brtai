<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestDiscordLog extends Command
{
    protected $signature = 'test-discord-log';
    protected $description = 'Envia um log de teste para o Discord.';

    public function handle()
    {
        Log::error('🚨 Teste de envio de log para o Discord!');
        $this->info('Log de teste enviado. Verifique o canal do Discord.');
    }
}
