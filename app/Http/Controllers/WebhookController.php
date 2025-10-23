<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use App\Models\WebhookLog;
use App\Jobs\ProcessPaymentWebhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleAbacatePay(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        // Log webhook
        $webhookLog = WebhookLog::create([
            'source' => 'abacate_pay',
            'event_type' => $payload['event'] ?? 'unknown',
            'payload' => $payload,
        ]);

        // Verify signature (implement based on AbacatePay docs)
        if (!$this->verifySignature($request)) {
            $webhookLog->markAsProcessed(401, 'Invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process webhook asynchronously
        ProcessPaymentWebhook::dispatch($webhookLog);

        $webhookLog->markAsProcessed(200, 'Webhook accepted for processing');

        return response()->json(['status' => 'accepted']);
    }

    private function verifySignature(Request $request)
    {
        $secret = config('services.abacate_pay.webhook_secret');
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        // TODO: Implement proper signature verification
        // This is a placeholder implementation
        return hash_hmac('sha256', $payload, $secret) === $signature;
    }

    public function handlePixPayment($webhookLog)
    {
        $payload = $webhookLog->payload;

        // Extract transaction data from payload
        $transactionId = $payload['transaction_id'] ?? null;
        $status = $payload['status'] ?? null;
        $amount = $payload['amount'] ?? null;

        if (!$transactionId || !$status) {
            return;
        }

        // Find participant by transaction ID
        $participant = Participant::where('transaction_id', $transactionId)->first();

        if (!$participant) {
            return;
        }

        // Update payment status
        if ($status === 'paid' && $participant->payment_status !== 'paid') {
            $participant->markAsPaid();

            // Create payment transaction record
            PaymentTransaction::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'amount' => $amount,
                'status' => 'completed',
                'gateway' => 'abacate_pay',
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $payload,
                'fee_amount' => $this->calculateFee($amount, $participant),
                'net_amount' => $amount - $this->calculateFee($amount, $participant),
                'processed_at' => now(),
            ]);

            // TODO: Send confirmation notification
            // Notification::sendPaymentConfirmation($participant);
        }
    }

    private function calculateFee($amount, $participant)
    {
        $organizer = $participant->event->organizer;
        $feePercentage = $organizer->isPro() ? 0.055 : 0.065;
        $fixedFee = 0.80;

        return ($amount * $feePercentage) + $fixedFee;
    }
}
