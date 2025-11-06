<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WooviService
{
    private $appId;
    private $baseUrl;
    private $client;

    public function __construct()
    {
        $this->appId = config('services.woovi.app_id');
        $this->baseUrl = config('services.woovi.base_url', 'https://api.woovi.com');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'force_ip_resolve' => 'v4',
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
            ],
            'headers' => [
                'Authorization' => $this->appId,
                'Content-Type' => 'application/json',
                'User-Agent' => 'BrotaAI/1.0',
                'Accept' => 'application/json',
            ]
        ]);

        Log::info('WooviService initialized', [
            'app_id' => $this->appId ? '***' . substr($this->appId, -4) : 'NULL',
            'base_url' => $this->baseUrl
        ]);
    }

    public function createCharge($data)
    {
        $payload = [
            'correlationID' => $data['correlation_id'],
            'value' => $data['value'],
            'comment' => $data['comment'],
            'additionalInfo' => $data['additional_info'] ?? [],
        ];

        if (isset($data['customer'])) {
            $payload['customer'] = $data['customer'];
        }

        Log::info('Creating Woovi charge', [
            'correlation_id' => $data['correlation_id'],
            'value' => $data['value'],
            'endpoint' => '/api/v1/charge'
        ]);

        try {
            $response = $this->client->post('/api/v1/charge', [
                'json' => $payload
            ]);

            $body = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            Log::info('Woovi API Response', [
                'status' => $statusCode,
                'body' => $body
            ]);

            return [
                'success' => true,
                'data' => json_decode($body, true)
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? $response->getBody()->getContents() : $e->getMessage();
            $statusCode = $response ? $response->getStatusCode() : 0;

            Log::error('Woovi API Error', [
                'status' => $statusCode,
                'error' => $errorBody,
                'app_id' => $this->appId ? '***' . substr($this->appId, -4) : 'NULL'
            ]);

            $errorData = json_decode($errorBody, true) ?? ['errors' => [['message' => $e->getMessage()]]];

            return [
                'success' => false,
                'error' => $errorData['errors'][0]['message'] ?? 'Erro ao criar cobrança'
            ];
        } catch (\Exception $e) {
            Log::error('Woovi Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ];
        }
    }

    public function getCharge($correlationID)
    {
        try {
            $response = $this->client->get("/api/v1/charge/{$correlationID}");
            $body = $response->getBody()->getContents();

            return [
                'success' => true,
                'data' => json_decode($body, true)
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? $response->getBody()->getContents() : $e->getMessage();

            Log::error('Erro ao consultar cobrança Woovi', [
                'correlation_id' => $correlationID,
                'error' => $errorBody
            ]);

            return [
                'success' => false,
                'error' => 'Erro ao consultar cobrança'
            ];
        } catch (\Exception $e) {
            Log::error('Exception Woovi getCharge: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão'
            ];
        }
    }

    public function createWithdrawal($data)
    {
        $payload = [
            'value' => $data['value'],
            'destinationAlias' => $data['pixKey'],
            'correlationID' => $data['correlationID'],
            'comment' => 'Saque BrotaAI - ' . now()->format('d/m/Y H:i'),
        ];

        try {
            $response = $this->client->post('/api/v1/payment', [
                'json' => $payload
            ]);

            $body = $response->getBody()->getContents();

            return [
                'success' => true,
                'data' => json_decode($body, true)
            ];
        } catch (RequestException $e) {
            $response = $e->getResponse();
            $errorBody = $response ? $response->getBody()->getContents() : $e->getMessage();

            Log::error('Erro Woovi saque: ' . $errorBody);
            return [
                'success' => false,
                'error' => 'Erro ao processar saque'
            ];
        } catch (\Exception $e) {
            Log::error('Exception Woovi saque: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro de conexão'
            ];
        }
    }

    public function generateQrCodeImageUrl($correlationID)
    {
        return $this->baseUrl . "/openpix/charge/brcode/image/{$correlationID}.png";
    }
}
