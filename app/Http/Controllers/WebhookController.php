<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use App\Models\WebhookLog;
use App\Models\User;
use App\Models\Profile;
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
        $metadata = $paymentData['metadata'] ?? [];

        if (!$transactionId || $status !== 'completed') {
            return;
        }

        // Verificar se é upgrade de plano
        if (isset($metadata['plan_type']) && $metadata['plan_type'] === 'pro') {
            $this->handlePlanUpgrade($transactionId, $paymentData, $metadata);
        } else {
            // É pagamento de participante normal
            $this->handleParticipantPayment($transactionId, $paymentData);
        }
    }

    private function handlePlanUpgrade($transactionId, $paymentData, $metadata)
    {
        $userId = $metadata['user_id'] ?? null;

        if (!$userId) {
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Atualizar plano para Pro
        $user->profile->update([
            'plan_type' => 'pro',
            'subscription_id' => $transactionId
        ]);

        // Atualizar ou criar transação
        $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionId)->first();

        if ($transaction) {
            $transaction->update([
                'status' => 'completed',
                'gateway_response' => $paymentData,
                'processed_at' => now(),
                'fee_amount' => $this->calculateUpgradeFee($paymentData['amount'] / 100),
                'net_amount' => ($paymentData['amount'] / 100) - $this->calculateUpgradeFee($paymentData['amount'] / 100)
            ]);
        } else {
            PaymentTransaction::create([
                'participant_id' => null,
                'event_id' => null,
                'amount' => $paymentData['amount'] / 100,
                'status' => 'completed',
                'gateway' => 'abacate_pay',
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $paymentData,
                'fee_amount' => $this->calculateUpgradeFee($paymentData['amount'] / 100),
                'net_amount' => ($paymentData['amount'] / 100) - $this->calculateUpgradeFee($paymentData['amount'] / 100),
                'metadata' => [
                    'type' => 'plan_upgrade',
                    'user_id' => $userId,
                    'plan' => 'pro'
                ],
                'processed_at' => now(),
            ]);
        }
    }

    private function handleParticipantPayment($transactionId, $paymentData)
    {
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
                'amount' => $paymentData['amount'] / 100,
                'status' => 'completed',
                'gateway' => 'abacate_pay',
                'gateway_transaction_id' => $transactionId,
                'gateway_response' => $paymentData,
                'fee_amount' => $this->calculateFee($paymentData['amount'] / 100, $participant),
                'net_amount' => ($paymentData['amount'] / 100) - $this->calculateFee($paymentData['amount'] / 100, $participant),
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

    private function calculateUpgradeFee($amount)
    {
        // Taxa fixa para upgrades de plano
        $feePercentage = 0.055; // 5.5%
        $fixedFee = 0.80;

        return ($amount * $feePercentage) + $fixedFee;
    }
}
