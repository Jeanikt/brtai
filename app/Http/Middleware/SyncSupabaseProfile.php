<?php

namespace App\Http\Middleware;

use App\Services\SupabaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncSupabaseProfile
{
    protected $supabaseAuth;

    public function __construct(SupabaseAuthService $supabaseAuth)
    {
        $this->supabaseAuth = $supabaseAuth;
    }

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Se estiver usando Supabase Auth, sincroniza o perfil
            if ($user && method_exists($user, 'getSupabaseAccessToken')) {
                $supabaseUser = $this->supabaseAuth->getCurrentUser($user->getSupabaseAccessToken());

                if ($supabaseUser) {
                    $this->supabaseAuth->syncProfile($supabaseUser);
                }
            }
        }

        return $next($request);
    }
}
