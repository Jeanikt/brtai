<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'operator_account_id',
        'amount',
        'status',
        'gateway_transaction_id',
        'gateway_response',
        'failure_reason'
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'amount' => 'decimal:2'
    ];

    public function operatorAccount()
    {
        return $this->belongsTo(OperatorAccount::class);
    }

    public function markAsProcessing($gatewayTransactionId = null)
    {
        $this->update([
            'status' => 'processing',
            'gateway_transaction_id' => $gatewayTransactionId
        ]);
    }

    public function markAsCompleted($gatewayResponse = null)
    {
        $this->update([
            'status' => 'completed',
            'gateway_response' => $gatewayResponse
        ]);
    }

    public function markAsFailed($reason, $gatewayResponse = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse
        ]);
    }
}
