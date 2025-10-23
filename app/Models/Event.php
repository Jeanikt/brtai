<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'organizer_id',
        'name',
        'description',
        'slug',
        'event_date',
        'location',
        'location_reveal_after_payment',
        'header_image_url',
        'theme',
        'rules',
        'max_participants',
        'status',
        'is_public',
        'metadata'
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'location_reveal_after_payment' => 'boolean',
        'is_public' => 'boolean',
        'metadata' => 'array'
    ];

    protected static function booted()
    {
        static::creating(function ($event) {
            if (empty($event->id)) {
                $event->id = (string) Str::uuid();
            }
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name) . '-' . Str::random(6);
            }
        });
    }

    public function organizer()
    {
        return $this->belongsTo(Profile::class, 'organizer_id');
    }

    public function priceTiers()
    {
        return $this->hasMany(PriceTier::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function confirmedParticipants()
    {
        return $this->hasMany(Participant::class)->where('payment_status', 'paid');
    }

    public function analytics()
    {
        return $this->hasMany(EventAnalytic::class);
    }

    public function aiSuggestions()
    {
        return $this->hasMany(AISuggestion::class);
    }

    public function getTotalRevenueAttribute()
    {
        return $this->confirmedParticipants()->sum('payment_amount');
    }

    public function getConfirmedCountAttribute()
    {
        return $this->confirmedParticipants()->count();
    }

    public function getConversionRateAttribute()
    {
        $totalParticipants = $this->participants()->count();
        if ($totalParticipants === 0) return 0;

        return ($this->confirmed_count / $totalParticipants) * 100;
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->event_date > now();
    }

    public function canAcceptMoreParticipants()
    {
        if ($this->max_participants === null) return true;

        return $this->confirmed_count < $this->max_participants;
    }

    public function getAvailableSlots()
    {
        if ($this->max_participants === null) return null;

        return $this->max_participants - $this->confirmed_count;
    }
}
