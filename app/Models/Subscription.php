<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'plan_type',
        'status',
        'amount',
        'gateway',
        'gateway_transaction_id',
        'starts_at',
        'ends_at',
        'activated_at',
        'cancelled_at',
        'metadata'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isExpired()
    {
        return $this->ends_at->isPast();
    }
}
