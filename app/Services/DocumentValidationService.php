<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentValidationService
{
    public function validateCNPJ($cnpj)
    {
        try {
            $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

            if (strlen($cnpj) !== 14) {
                return [
                    'valid' => false,
                    'message' => 'CNPJ deve conter 14 dígitos'
                ];
            }

            $response = Http::timeout(10)->get("https://api.opencnpj.org/{$cnpj}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'valid' => true,
                    'data' => $data,
                    'message' => 'CNPJ válido e ativo'
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'CNPJ não encontrado ou inválido'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erro na validação de CNPJ: ' . $e->getMessage());

            return [
                'valid' => false,
                'message' => 'Erro temporário na validação. Tente novamente.'
            ];
        }
    }

    public function validateCPF($cpf)
    {
        try {
            $cpf = preg_replace('/[^0-9]/', '', $cpf);

            if (strlen($cpf) !== 11) {
                return [
                    'valid' => false,
                    'message' => 'CPF deve conter 11 dígitos'
                ];
            }

            $response = Http::withHeaders([
                'X-API-KEY' => '44ff59a1f2dff4c3f513d8c51ee3e5775a982ad07e1c775f173e0aae56c1ab86'
            ])->timeout(10)->get("https://apicpf.com/api/consulta?cpf={$cpf}");

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'VALID') {
                    return [
                        'valid' => true,
                        'data' => $data,
                        'message' => 'CPF válido'
                    ];
                } else {
                    return [
                        'valid' => false,
                        'message' => 'CPF inválido ou não encontrado'
                    ];
                }
            } else {
                return [
                    'valid' => false,
                    'message' => 'Erro na validação do CPF'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erro na validação de CPF: ' . $e->getMessage());

            return [
                'valid' => false,
                'message' => 'Erro temporário na validação. Tente novamente.'
            ];
        }
    }

    public function validateDocument($type, $document)
    {
        if ($type === 'CNPJ') {
            return $this->validateCNPJ($document);
        } else {
            return $this->validateCPF($document);
        }
    }
}
