<?php

namespace App\Services;

use App\Models\Profile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseAuthService
{
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = config('services.supabase.url');
        $this->supabaseKey = config('services.supabase.key');
    }

    /**
     * Obtém o usuário atual do Supabase Auth a partir do request
     */
    public function getUserFromRequest($request)
    {
        // Para desenvolvimento, retorna um perfil de teste
        if (app()->environment('local')) {
            return Profile::where('metadata->is_test_user', true)->first();
        }

        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return null;
        }

        $userData = $this->getCurrentUser($token);

        if (!$userData) {
            return null;
        }

        return $this->syncProfile($userData);
    }

    /**
     * Extrai o token do request
     */
    private function getTokenFromRequest($request)
    {
        return $request->bearerToken() ?: $request->cookie('sb-access-token');
    }

    /**
     * Obtém o usuário do Supabase Auth
     */
    public function getCurrentUser($accessToken)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'apikey' => $this->supabaseKey,
            ])->timeout(10)->get($this->supabaseUrl . '/auth/v1/user');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Falha ao obter usuário do Supabase', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao obter usuário do Supabase: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Sincroniza o perfil local com os dados do Supabase Auth
     */
    public function syncProfile($supabaseUser)
    {
        $userData = $supabaseUser['user'] ?? $supabaseUser;

        if (!isset($userData['id'])) {
            return null;
        }

        return Profile::updateOrCreate(
            ['id' => $userData['id']],
            [
                'full_name' => $userData['user_metadata']['full_name'] ??
                    $userData['user_metadata']['name'] ??
                    $userData['email'] ?? 'Usuário',
                'email_verified_at' => $userData['email_confirmed_at'] ? now() : null,
                'phone' => $userData['phone'] ?? $userData['user_metadata']['phone'] ?? null,
                'avatar_url' => $userData['user_metadata']['avatar_url'] ?? null,
                'metadata' => [
                    'supabase_user' => $userData,
                    'last_sync_at' => now()->toISOString(),
                ],
            ]
        );
    }
}
