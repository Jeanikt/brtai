<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'price_tier_id',
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
        'metadata'
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'pix_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function priceTier()
    {
        return $this->belongsTo(PriceTier::class);
    }

    public function paymentTransaction()
    {
        return $this->hasOne(PaymentTransaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'confirmed_at' => now()
        ]);
    }

    public function markAsCheckedIn()
    {
        $this->update(['checked_in_at' => now()]);
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isCheckedIn()
    {
        return !is_null($this->checked_in_at);
    }

    public function isPixExpired()
    {
        return $this->pix_expires_at && $this->pix_expires_at < now();
    }
}
