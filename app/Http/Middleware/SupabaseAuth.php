<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use App\Services\SupabaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SupabaseAuth
{
    protected $supabaseAuth;

    public function __construct(SupabaseAuthService $supabaseAuth)
    {
        $this->supabaseAuth = $supabaseAuth;
    }

    public function handle(Request $request, Closure $next)
    {
        // Se já estiver autenticado, continua
        if (Auth::check()) {
            return $next($request);
        }

        // Tenta autenticar via Supabase
        $profile = $this->supabaseAuth->getUserFromRequest($request);

        if ($profile) {
            Auth::login($profile);
            return $next($request);
        }

        // Em desenvolvimento, cria um usuário de teste
        if (app()->environment('local') && $request->isMethod('get')) {
            $testProfile = $this->createTestProfile();
            Auth::login($testProfile);
            return $next($request);
        }

        // Se não estiver autenticado, redireciona para login
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login');
    }

    private function createTestProfile()
    {
        return Profile::firstOrCreate(
            ['id' => 'test-user-id-12345'], 
            [
                'full_name' => 'Usuário de Teste',
                'plan_type' => 'freemium',
                'email_verified_at' => now(),
                'metadata' => ['is_test_user' => true],
            ]
        );
    }
}
