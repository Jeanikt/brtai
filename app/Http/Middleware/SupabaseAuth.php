<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use App\Models\User;
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
        if (Auth::check()) {
            return $next($request);
        }

        $profile = $this->supabaseAuth->getUserFromRequest($request);

        if ($profile) {
            $user = User::firstOrCreate(
                ['id' => $profile->id],
                ['name' => $profile->full_name, 'email' => $profile->email ?? '']
            );

            Auth::login($user);
            return $next($request);
        }

        if (app()->environment('local') && $request->isMethod('get')) {
            $testProfile = $this->createTestProfile();
            $user = User::firstOrCreate(
                ['id' => $testProfile->id],
                ['name' => $testProfile->full_name, 'email' => 'test@example.com']
            );

            Auth::login($user);
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login');
    }

    private function createTestProfile()
    {
        return Profile::firstOrCreate(
            ['metadata->is_test_user' => true],
            [
                'id' => (string) Str::uuid(),
                'full_name' => 'Usuário de Teste',
                'plan_type' => 'freemium',
                'email_verified_at' => now(),
                'metadata' => ['is_test_user' => true],
            ]
        );
    }
}
