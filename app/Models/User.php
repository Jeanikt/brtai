<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $fillable = [
        'name',
        'email',
        'password',
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

    /**
     * Relação 1:1 com Profile (mesmo ID)
     */
    public function profile()
    {
        return $this->hasOne(Profile::class, 'id', 'id');
    }

    /**
     * Cria automaticamente um Profile ao criar o usuário
     */
    protected static function booted(): void
    {
        static::created(function (self $user) {
            // Evita duplicação se o perfil já existir
            if (!$user->profile()->exists()) {
                $user->profile()->create([
                    'full_name' => $user->name ?? 'Usuário sem nome',
                    'plan_type' => 'freemium',
                    'metadata' => [],
                ]);
            }
        });
    }
}
