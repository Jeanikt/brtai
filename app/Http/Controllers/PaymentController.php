<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    private $apiKey = 'abc_dev_2zd5G3HnxkPTxcPs3qd56Kkz';
    private $baseUrl = 'https://api.abacatepay.com/v1';

    public function checkout($participantId)
    {
        $participant = Participant::findOrFail($participantId);

        if ($participant->isPaid()) {
            return redirect()->route('payment.success', $participant->id);
        }

        if (!$participant->pix_code || $participant->isPixExpired()) {
            try {
                $pixData = $this->generatePixPayment($participant);
                $participant->update([
                    'pix_code' => $pixData['pix_code'],
                    'pix_expires_at' => $pixData['expires_at'],
                    'transaction_id' => $pixData['transaction_id']
                ]);
            } catch (\Exception $e) {
                return back()->withErrors(['payment' => 'Erro ao gerar pagamento PIX: ' . $e->getMessage()]);
            }
        }

        return Inertia::render('Payment/Checkout', [
            'participant' => $participant->load('event', 'priceTier'),
            'pix_code' => $participant->pix_code,
            'pix_expires_at' => $participant->pix_expires_at,
        ]);
    }

    public function success($participantId)
    {
        $participant = Participant::findOrFail($participantId);

        if (!$participant->isPaid()) {
            return redirect()->route('payment.checkout', $participant->id);
        }

        $event = $participant->event;

        return Inertia::render('Payment/Success', [
            'participant' => $participant,
            'event' => $event,
            'location' => $event->location_reveal_after_payment ? $event->location : null,
        ]);
    }

    public function status($participantId)
    {
        $participant = Participant::findOrFail($participantId);

        return response()->json([
            'paid' => $participant->isPaid(),
            'status' => $participant->payment_status,
        ]);
    }

    private function generatePixPayment(Participant $participant)
    {
        $event = $participant->event;
        $priceTier = $participant->priceTier;

        $payload = [
            'amount' => (float) $priceTier->price * 100,
            'currency' => 'BRL',
            'description' => "Ingresso: {$event->name} - {$priceTier->name}",
            'payment_method' => 'pix',
            'metadata' => [
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'price_tier_id' => $priceTier->id
            ],
            'success_url' => route('payment.success', $participant->id),
            'webhook_url' => route('webhooks.abacatepay')
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/payments', $payload);

        if ($response->successful()) {
            $paymentData = $response->json();
            return [
                'pix_code' => $paymentData['pix_code'],
                'expires_at' => now()->addMinutes(30),
                'transaction_id' => $paymentData['id']
            ];
        }

        throw new \Exception('Falha ao gerar pagamento PIX: ' . $response->body());
    }
}
