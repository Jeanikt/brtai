<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWooviWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $webhookLog;

    public function __construct(WebhookLog $webhookLog)
    {
        $this->webhookLog = $webhookLog;
    }

    public function handle()
    {
        $payload = $this->webhookLog->payload;
        $event = $payload['event'] ?? null;

        if ($event === 'OPENPIX:CHARGE_COMPLETED') {
            $this->handleChargeCompleted($payload);
        }

        $this->webhookLog->update([
            'processed_at' => now(),
            'status_code' => 200,
            'response' => 'Processed'
        ]);
    }

    private function handleChargeCompleted($payload)
    {
        $charge = $payload['charge'] ?? [];
        $correlationID = $charge['correlationID'] ?? null;
        $status = $charge['status'] ?? null;

        if ($status === 'COMPLETED' && $correlationID) {
            if (str_starts_with($correlationID, 'subscription_')) {
                $this->handleSubscriptionPayment($charge);
            } else {
                $this->handleParticipantPayment($charge);
            }
        }
    }

    private function handleSubscriptionPayment($charge)
    {
        $correlationID = $charge['correlationID'];

        $subscription = Subscription::where('gateway_transaction_id', $correlationID)->first();

        if ($subscription && $subscription->status === 'pending') {
            $subscription->update([
                'status' => 'active',
                'activated_at' => now(),
                'ends_at' => now()->addMonth(),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'paid_at' => now()->toISOString(),
                    'first_payment' => true
                ])
            ]);

            $subscription->user->profile->update(['plan_type' => 'pro']);
        }
    }

    private function handleParticipantPayment($charge)
    {
        $correlationID = $charge['correlationID'];

        if (preg_match('/participant_([^_]+)_/', $correlationID, $matches)) {
            $participantId = $matches[1];
            $participant = Participant::find($participantId);

            if ($participant && $participant->payment_status !== 'paid') {
                $participant->markAsPaid();

                $organizer = $participant->event->organizer;
                $event = $participant->event;
                $paymentAmount = $participant->payment_amount ?? $participant->priceTier->price;

                $feeAmount = $event->calculateFee($paymentAmount, 'pix', $organizer->plan_type);
                $netAmount = max(0, $paymentAmount - $feeAmount);

                PaymentTransaction::create([
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'amount' => $paymentAmount,
                    'status' => 'completed',
                    'gateway' => 'woovi',
                    'gateway_transaction_id' => $correlationID,
                    'gateway_response' => $charge,
                    'fee_amount' => $feeAmount,
                    'net_amount' => $netAmount,
                    'processed_at' => now(),
                ]);
            }
        }
    }
}
