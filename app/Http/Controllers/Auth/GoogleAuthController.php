<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            Log::info('Google User Data:', [
                'id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar()
            ]);

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'id' => (string) Str::uuid(),
                    'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Usuário Google',
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(24)),
                    'email_verified_at' => now(),
                ]);

                // O Profile é criado automaticamente pelo booted method do User
                // Agora apenas atualizamos os dados do Google
                if ($user->profile) {
                    $user->profile->update([
                        'full_name' => $googleUser->getName() ?? $user->name,
                        'avatar_url' => $googleUser->getAvatar(),
                        'metadata' => array_merge(
                            $user->profile->metadata ?? [],
                            ['google_id' => $googleUser->getId()]
                        ),
                    ]);
                }

                Log::info('Novo usuário criado via Google OAuth:', ['user_id' => $user->id]);
            } else {
                // Para usuário existente, apenas atualizamos o profile
                if ($user->profile) {
                    $user->profile->update([
                        'full_name' => $googleUser->getName() ?? $user->profile->full_name,
                        'avatar_url' => $googleUser->getAvatar() ?? $user->profile->avatar_url,
                        'metadata' => array_merge(
                            $user->profile->metadata ?? [],
                            ['google_id' => $googleUser->getId()]
                        ),
                    ]);
                } else {
                    // Caso raro: usuário existe mas não tem profile
                    Profile::create([
                        'id' => $user->id,
                        'full_name' => $googleUser->getName() ?? $user->name,
                        'avatar_url' => $googleUser->getAvatar(),
                        'plan_type' => 'freemium',
                        'metadata' => [
                            'google_id' => $googleUser->getId(),
                        ],
                    ]);
                }

                Log::info('Usuário existente autenticado via Google OAuth:', ['user_id' => $user->id]);
            }

            Auth::login($user, true);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            Log::error('Google OAuth Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('login')->withErrors(['error' => 'Falha ao autenticar com o Google.']);
        }
    }
}
