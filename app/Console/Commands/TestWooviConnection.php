<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class TestWooviConnection extends Command
{
    protected $signature = 'test:woovi';
    protected $description = 'Test Woovi API connection';

    public function handle()
    {
        $appId = config('services.woovi.app_id');
        $baseUrl = config('services.woovi.base_url');

        $this->info("AppID: " . ($appId ? '***' . substr($appId, -4) : 'NULL'));
        $this->info("Base URL: {$baseUrl}");
        $this->info("Testing connection...");

        try {
            $client = new Client([
                'base_uri' => $baseUrl,
                'timeout' => 30,
                'force_ip_resolve' => 'v4',
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                ],
                'headers' => [
                    'Authorization' => $appId,
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'BrotaAI/1.0',
                    'Accept' => 'application/json',
                ]
            ]);

            $response = $client->get('/api/v1/account');
            $body = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            $this->info("✅ Conexão bem-sucedida! Status: {$statusCode}");
            $this->info("Response: " . $body);
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $body = $response ? $response->getBody()->getContents() : $e->getMessage();

            $this->error("❌ Erro na conexão: {$statusCode}");
            $this->error("Response: " . $body);

            // Análise detalhada do erro
            if (str_contains($body, 'não tem permissão') || str_contains($body, 'appID inválido')) {
                $this->error("\n🔍 DIAGNÓSTICO:");
                $this->error("👉 1. Verifique se o AppID está correto no .env");
                $this->error("👉 2. Confirme se o IP 186.223.179.86 está na lista de IPs permitidos");
                $this->error("👉 3. Tente regenerar o AppID no painel da Woovi");
                $this->error("👉 4. Verifique se a aplicação está 'Ativada'");
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }
}
