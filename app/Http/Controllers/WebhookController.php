<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use App\Models\WebhookLog;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleAbacatePay(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('X-Signature');

        $webhookLog = WebhookLog::create([
            'source' => 'abacate_pay',
            'event_type' => $payload['event'] ?? 'unknown',
            'payload' => $payload,
        ]);

        if (!$this->verifySignature($request)) {
            $webhookLog->update([
                'status_code' => 401,
                'response' => 'Invalid signature'
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($payload['event'] === 'payment.completed') {
            $this->handlePaymentCompleted($payload['data']);
        }

        $webhookLog->update([
            'status_code' => 200,
            'response' => 'Webhook processed successfully',
            'processed_at' => now()
        ]);

        return response()->json(['status' => 'success']);
    }

    private function verifySignature(Request $request)
    {
        $secret = 'webh_dev_24Kzwr0q6YCHGzkDUeGAQ6JL';
        $payload = $request->getContent();
        $signature = $request->header('X-Signature');

        return hash_hmac('sha256', $payload, $secret) === $signature;
    }

    private function handlePaymentCompleted($paymentData)
    {
        $transactionId = $paymentData['id'] ?? null;
        $status = $paymentData['status'] ?? null;
        $amount = $paymentData['amount'] ?? null;

        if (!$transactionId || $status !== 'completed') {
            return;
        }

        $participant = Participant::where('transaction_id', $transactionId)->first();

        if (!$participant) {
            return;
        }

        if ($participant->payment_status !== 'paid') {
            $participant->update([
                'payment_status' => 'paid',
                'confirmed_at' => now()
            ]);

            PaymentTransaction::create([
                'participant_id' => $participant->id,
                'event_id' => $participant->event_id,
                'amount' => $amount / 100,
                'status' => 'completed',
                'gateway' => 'abacate_pay',
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $paymentData,
                'fee_amount' => $this->calculateFee($amount / 100, $participant),
                'net_amount' => ($amount / 100) - $this->calculateFee($amount / 100, $participant),
                'processed_at' => now(),
            ]);
        }
    }

    private function calculateFee($amount, $participant)
    {
        $organizer = $participant->event->organizer;
        $feePercentage = $organizer->plan_type === 'pro' ? 0.055 : 0.065;
        $fixedFee = 0.80;

        return ($amount * $feePercentage) + $fixedFee;
    }
}
