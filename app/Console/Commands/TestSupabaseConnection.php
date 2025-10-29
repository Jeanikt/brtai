<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ImageService;

class TestSupabaseConnection extends Command
{
    protected $signature = 'supabase:test';
    protected $description = 'Testar conexão com Supabase Storage';

    public function handle(ImageService $imageService)
    {
        $this->info('Testando conexão com Supabase Storage...');

        $this->line('Verificando configurações...');
        $this->line('SUPABASE_S3_ACCESS_KEY: ' . (env('SUPABASE_S3_ACCESS_KEY') ? '✅ Configurado' : '❌ Não configurado'));
        $this->line('SUPABASE_S3_SECRET: ' . (env('SUPABASE_S3_SECRET') ? '✅ Configurado' : '❌ Não configurado'));
        $this->line('SUPABASE_ENDPOINT: ' . env('SUPABASE_ENDPOINT'));
        $this->line('SUPABASE_BUCKET: ' . env('SUPABASE_BUCKET'));

        if ($imageService->testConnection()) {
            $this->info('✅ Conexão com Supabase Storage funcionando!');
        } else {
            $this->error('❌ Falha na conexão com Supabase Storage');
            $this->line('');
            $this->line('Possíveis soluções:');
            $this->line('1. Verifique se o bucket "events" existe no Supabase Dashboard');
            $this->line('2. Verifique as políticas (policies) do bucket no Supabase');
            $this->line('3. Confirme as credenciais S3 no Supabase Dashboard > Storage > Settings');
            $this->line('4. Verifique se a região está correta (us-east-1)');
        }
    }
}
