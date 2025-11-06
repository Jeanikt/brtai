<?php

namespace App\Jobs;

use App\Models\WebhookLog;
use App\Models\Participant;
use App\Models\PaymentTransaction;
use App\Models\Notification;
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
        try {
            $payload = $this->webhookLog->payload;

            Log::info('Processing Woovi webhook', ['webhook_id' => $this->webhookLog->id]);

            // A estrutura do webhook da Woovi/OpenPix
            $event = $payload['event'] ?? null;
            $charge = $payload['charge'] ?? $payload;

            if ($event === 'OPENPIX:CHARGE_COMPLETED' || $event === 'OPENPIX:TRANSACTION_RECEIVED') {
                $this->handleChargeCompleted($charge);
            } elseif ($event === 'OPENPIX:CHARGE_EXPIRED') {
                $this->handleChargeExpired($charge);
            }

            $this->webhookLog->update(['processed_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Error processing Woovi webhook', [
                'webhook_id' => $this->webhookLog->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function handleChargeCompleted($charge)
    {
        $correlationID = $charge['correlationID'] ?? null;
        $status = $charge['status'] ?? null;

        if (!$correlationID || $status !== 'COMPLETED') {
            return;
        }

        // Extrair participant_id do correlationID
        if (preg_match('/participant_(\d+)_/', $correlationID, $matches)) {
            $participantId = $matches[1];
            $participant = Participant::find($participantId);
        } else {
            // Fallback: buscar por transaction_id
            $participant = Participant::where('transaction_id', $correlationID)->first();
        }

        if (!$participant) {
            Log::error('Participant not found for webhook', ['correlation_id' => $correlationID]);
            return;
        }

        if ($participant->payment_status !== 'paid') {
            $this->processSuccessfulPayment($participant, $charge);
        }
    }

    private function handleChargeExpired($charge)
    {
        $correlationID = $charge['correlationID'] ?? null;
        $status = $charge['status'] ?? null;

        if (!$correlationID || $status !== 'EXPIRED') {
            return;
        }

        $participant = Participant::where('transaction_id', $correlationID)->first();

        if ($participant && $participant->payment_status === 'pending') {
            $participant->update(['payment_status' => 'failed']);

            PaymentTransaction::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'amount' => $participant->payment_amount,
                'status' => 'failed',
                'gateway' => 'woovi',
                'gateway_transaction_id' => $correlationID,
                'gateway_response' => $charge,
                'processed_at' => now(),
            ]);

            Log::info('Payment expired via webhook', ['participant_id' => $participant->id]);
        }
    }

    private function processSuccessfulPayment(Participant $participant, array $charge)
    {
        $participant->markAsPaid();
        $participant->update(['transaction_id' => $charge['correlationID']]);

        // Calcular taxas
        $organizer = $participant->event->organizer;
        $feePercentage = $organizer->isPro() ? 0.055 : 0.065;
        $fixedFee = 0.80;
        $feeAmount = ($participant->payment_amount * $feePercentage) + $fixedFee;
        $netAmount = $participant->payment_amount - $feeAmount;

        // Criar transação
        PaymentTransaction::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'amount' => $participant->payment_amount,
            'status' => 'completed',
            'gateway' => 'woovi',
            'gateway_transaction_id' => $charge['correlationID'],
            'gateway_response' => $charge,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'processed_at' => now(),
        ]);

        // Notificações
        Notification::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'type' => 'payment_confirmed',
            'title' => 'Pagamento Confirmado! 🎉',
            'message' => "Seu pagamento para {$participant->event->name} foi confirmado. Você está na lista!",
            'channel' => 'whatsapp',
            'status' => 'pending',
        ]);

        Notification::create([
            'user_id' => $participant->event->organizer_id,
            'event_id' => $participant->event_id,
            'type' => 'new_participant',
            'title' => 'Novo Participante Confirmado!',
            'message' => "{$participant->full_name} acabou de confirmar presença no seu evento.",
            'channel' => 'push',
            'status' => 'pending',
        ]);

        Log::info('Payment processed successfully via webhook', [
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'amount' => $participant->payment_amount
        ]);
    }
}
