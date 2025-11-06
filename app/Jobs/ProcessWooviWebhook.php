<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Models\Participant;
use App\Models\User;
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

        Log::info('Processing Woovi webhook event: ' . $event, ['webhook_id' => $this->webhookLog->id]);

        if ($event === 'OPENPIX:CHARGE_COMPLETED') {
            $this->handleChargeCompleted($payload);
        }

        // Atualizar o webhook log como processado
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
            // Verificar se é uma assinatura
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

            // Atualizar plano do usuário
            $subscription->user->profile->update(['plan_type' => 'pro']);

            Log::info('Subscription activated via webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id
            ]);
        }
    }

    private function handleParticipantPayment($charge)
    {
        $correlationID = $charge['correlationID'];

        // Extrair participant_id do correlationID (formato: participant_{id}_{timestamp})
        if (preg_match('/participant_([^_]+)_/', $correlationID, $matches)) {
            $participantId = $matches[1];

            $participant = Participant::find($participantId);

            if ($participant && $participant->payment_status !== 'paid') {
                $participant->markAsPaid();

                // Calcular taxas
                $organizer = $participant->event->organizer;
                $feePercentage = $organizer->plan_type === 'pro' ? 0.055 : 0.065;
                $fixedFee = 0.85;
                $paymentAmount = $participant->payment_amount ?? $participant->priceTier->price;
                $feeAmount = ($paymentAmount * $feePercentage) + $fixedFee;
                $netAmount = max(0, $paymentAmount - $feeAmount);

                // Registrar transação de pagamento
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

                Log::info('Participant payment completed via webhook', [
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id
                ]);
            }
        }
    }
}
