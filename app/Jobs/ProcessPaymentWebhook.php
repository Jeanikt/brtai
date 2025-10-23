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

class ProcessPaymentWebhook implements ShouldQueue
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

            Log::info('Processing payment webhook', ['webhook_id' => $this->webhookLog->id]);

            // Extract transaction data from AbacatePay payload
            $transactionId = $payload['id'] ?? $payload['transaction_id'] ?? null;
            $status = $payload['status'] ?? null;
            $amount = $payload['amount'] ?? null;
            $customerEmail = $payload['customer']['email'] ?? null;
            $customerPhone = $payload['customer']['phone'] ?? null;

            if (!$transactionId || !$status) {
                Log::error('Invalid webhook payload', ['payload' => $payload]);
                return;
            }

            // Find participant by transaction ID or customer info
            $participant = Participant::where('transaction_id', $transactionId)
                ->orWhere(function ($query) use ($customerEmail, $customerPhone) {
                    if ($customerEmail) {
                        $query->where('email', $customerEmail);
                    }
                    if ($customerPhone) {
                        $query->where('phone', $customerPhone);
                    }
                })
                ->first();

            if (!$participant) {
                Log::error('Participant not found for webhook', ['transaction_id' => $transactionId]);
                return;
            }

            // Update payment status based on webhook
            if ($status === 'paid' || $status === 'completed') {
                if ($participant->payment_status !== 'paid') {
                    $participant->markAsPaid();
                    $participant->update(['transaction_id' => $transactionId]);

                    // Calculate fees
                    $organizer = $participant->event->organizer;
                    $feePercentage = $organizer->isPro() ? 0.055 : 0.065;
                    $fixedFee = 0.80;
                    $feeAmount = ($participant->payment_amount * $feePercentage) + $fixedFee;
                    $netAmount = $participant->payment_amount - $feeAmount;

                    // Create payment transaction record
                    PaymentTransaction::create([
                        'participant_id' => $participant->id,
                        'event_id' => $participant->event_id,
                        'amount' => $participant->payment_amount,
                        'status' => 'completed',
                        'gateway' => 'abacate_pay',
                        'gateway_transaction_id' => $transactionId,
                        'gateway_response' => $payload,
                        'fee_amount' => $feeAmount,
                        'net_amount' => $netAmount,
                        'processed_at' => now(),
                    ]);

                    // Send confirmation notification to participant
                    Notification::create([
                        'participant_id' => $participant->id,
                        'event_id' => $participant->event_id,
                        'type' => 'payment_confirmed',
                        'title' => 'Pagamento Confirmado! 🎉',
                        'message' => "Seu pagamento para {$participant->event->name} foi confirmado. Você está na lista!",
                        'channel' => 'whatsapp',
                        'status' => 'pending',
                    ]);

                    // Notify organizer
                    Notification::create([
                        'user_id' => $participant->event->organizer_id,
                        'event_id' => $participant->event_id,
                        'type' => 'new_participant',
                        'title' => 'Novo Participante Confirmado!',
                        'message' => "{$participant->full_name} acabou de confirmar presença no seu evento.",
                        'channel' => 'push',
                        'status' => 'pending',
                    ]);

                    Log::info('Payment processed successfully', [
                        'participant_id' => $participant->id,
                        'event_id' => $participant->event_id,
                        'amount' => $participant->payment_amount
                    ]);
                }
            } elseif ($status === 'failed' || $status === 'cancelled') {
                $participant->update(['payment_status' => 'failed']);

                PaymentTransaction::create([
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'amount' => $participant->payment_amount,
                    'status' => 'failed',
                    'gateway' => 'abacate_pay',
                    'gateway_transaction_id' => $transactionId,
                    'gateway_response' => $payload,
                    'processed_at' => now(),
                ]);

                Log::info('Payment failed', ['participant_id' => $participant->id]);
            }

            $this->webhookLog->update(['processed_at' => now()]);
        } catch (\Exception $e) {
            Log::error('Error processing payment webhook', [
                'webhook_id' => $this->webhookLog->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
