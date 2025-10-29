<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'full_name',
        'phone',
        'avatar_url',
        'plan_type',
        'email_verified_at',
        'phone_verified_at',
        'metadata',
        'subscription_id',
        'trial_ends_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'organizer_id');
    }

    public function aiSuggestions()
    {
        return $this->hasMany(AISuggestion::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function isPro()
    {
        return $this->plan_type === 'pro';
    }

    public function isEnterprise()
    {
        return $this->plan_type === 'enterprise';
    }

    public function getEventLimit()
    {
        return match ($this->plan_type) {
            'freemium' => 1,
            'pro' => null,
            'enterprise' => null,
            default => 1
        };
    }

    public function getParticipantLimit()
    {
        return match ($this->plan_type) {
            'freemium' => 70,
            'pro' => null,
            'enterprise' => null,
            default => 70
        };
    }

    public static function createFromSupabaseUser($supabaseUser)
    {
        return self::updateOrCreate(
            ['id' => $supabaseUser->id],
            [
                'full_name' => $supabaseUser->user_metadata['full_name'] ?? $supabaseUser->email,
                'email_verified_at' => $supabaseUser->email_confirmed_at ? now() : null,
                'phone' => $supabaseUser->phone,
                'avatar_url' => $supabaseUser->user_metadata['avatar_url'] ?? null,
            ]
        );
    }
}
