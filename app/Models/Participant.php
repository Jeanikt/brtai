<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Participant extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'price_tier_id',
        'profile_id',
        'full_name',
        'email',
        'phone',
        'payment_status',
        'payment_amount',
        'transaction_id',
        'pix_code',
        'pix_expires_at',
        'confirmed_at',
        'checked_in_at',
        'metadata',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'pix_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($participant) {
            if (empty($participant->id)) {
                $participant->id = (string) Str::uuid();
            }
        });
    }

    /** 🔗 Relationships **/
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }

    public function userProfile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    /** 🎯 Scopes **/
    public function scopeConfirmed($query)
    {
        return $query->where('payment_status', 'paid')
            ->whereNotNull('confirmed_at');
    }

    public function scopeForFinishedEvents($query)
    {
        return $query->whereHas('event', function ($q) {
            $q->where('event_date', '<', now())
                ->orWhere('status', 'completed');
        });
    }

    /** 💳 Actions **/
    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'confirmed_at' => now(),
        ]);
    }

    public function markAsFailed()
    {
        $this->update(['payment_status' => 'failed']);
    }

    public function markAsCheckedIn()
    {
        $this->update(['checked_in_at' => now()]);
    }

    /** 🧾 Helpers **/
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    public function isCheckedIn()
    {
        return !is_null($this->checked_in_at);
    }

    public function isPixExpired()
    {
        return $this->pix_expires_at && $this->pix_expires_at->isPast();
    }

    /** 💰 Accessors **/
    public function getFormattedPaymentAmountAttribute()
    {
        $amount = (float) $this->payment_amount;
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->payment_status) {
            'paid' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Pago'],
            'pending' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Pendente'],
            'failed' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Falhou'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Desconhecido'],
        };
    }

    /** 🆓 Free events **/
    public function isFreeEvent()
    {
        return $this->event && $this->event->is_free;
    }

    public function processFreeRegistration()
    {
        if ($this->isFreeEvent() && $this->isPending()) {
            $this->update([
                'payment_status' => 'paid',
                'confirmed_at' => now(),
            ]);
        }
    }
}
