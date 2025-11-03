<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class, 'id', 'id');
    }

    /**
     * 🔥 Hook: quando um novo usuário é criado
     */
    protected static function booted(): void
    {
        static::created(function (self $user) {
            // Garante que o profile exista
            $profile = $user->profile()->firstOrCreate([
                'id' => $user->id,
            ], [
                'full_name' => $user->name ?? 'Usuário sem nome',
                'plan_type' => 'freemium',
                'metadata' => [],
            ]);

            // 🔔 Envia log de criação de usuário ao Discord
            self::sendDiscordUserCreatedLog($user, $profile);
        });
    }

    /**
     * 🔔 Envia log ao Discord quando um usuário é criado
     */
    private static function sendDiscordUserCreatedLog(self $user, Profile $profile): void
    {
        try {
            $webhook = 'https://discord.com/api/webhooks/1434919100185841735/zLZIi8emvR1VIpOABUDmv7aS_Qor6MDszoY8-G0XdVlDU1HTzOupAAHd017T_oOLJMxo';

            // Contagens globais
            $totalUsers = self::count();
            $freemiumCount = Profile::where('plan_type', 'freemium')->count();
            $proCount = Profile::where('plan_type', 'pro')->count();

            $embed = [
                'title' => '🆕 Novo Usuário Criado',
                'color' => 0x4CAF50,
                'fields' => [
                    ['name' => '👤 Nome', 'value' => $user->name ?? 'Sem nome', 'inline' => true],
                    ['name' => '📧 Email', 'value' => $user->email ?? 'Desconhecido', 'inline' => true],
                    ['name' => '🎟️ Plano', 'value' => ucfirst($profile->plan_type ?? 'freemium'), 'inline' => true],
                    ['name' => '📅 Criado em', 'value' => now()->toDateTimeString(), 'inline' => true],
                    ['name' => '📊 Total Usuários', 'value' => (string) $totalUsers, 'inline' => true],
                    ['name' => '💎 Freemium', 'value' => (string) $freemiumCount, 'inline' => true],
                    ['name' => '🚀 Pro', 'value' => (string) $proCount, 'inline' => true],
                ],
                'footer' => ['text' => 'BrotaAI • Registros de Usuário'],
                'timestamp' => now()->toIso8601String(),
            ];

            Http::withOptions(['verify' => false])
                ->timeout(5)
                ->post($webhook, [
                    'username' => 'BrotaAI Users 🧱',
                    'avatar_url' => 'https://laravel.com/img/logomark.min.svg',
                    'embeds' => [$embed],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar log de criação de usuário para Discord: ' . $e->getMessage());
        }
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
