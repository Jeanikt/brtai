<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'event_id',
        'amount',
        'status',
        'gateway',
        'gateway_transaction_id',
        'gateway_response',
        'fee_amount',
        'net_amount',
        'processed_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime'
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function markAsCompleted($gatewayResponse = null)
    {
        $this->update([
            'status' => 'completed',
            'processed_at' => now(),
            'gateway_response' => $gatewayResponse
        ]);
    }

    public function markAsFailed($gatewayResponse = null)
    {
        $this->update([
            'status' => 'failed',
            'processed_at' => now(),
            'gateway_response' => $gatewayResponse
        ]);
    }

    public function calculateNetAmount()
    {
        $feePercentage = $this->participant->event->organizer->isPro() ? 0.055 : 0.065;
        $fixedFee = 0.80;

        $this->fee_amount = ($this->amount * $feePercentage) + $fixedFee;
        $this->net_amount = $this->amount - $this->fee_amount;
    }
}
