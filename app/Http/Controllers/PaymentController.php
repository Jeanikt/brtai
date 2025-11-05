<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    private $apiKey = 'abc_dev_2zd5G3HnxkPTxcPs3qd56Kkz';
    private $baseUrl = 'https://api.abacatepay.com/v1';

    public function checkout($participantId)
    {
        $participant = Participant::with(['event', 'priceTier'])->findOrFail($participantId);

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
            'participant' => $participant,
            'pix_code' => $participant->pix_code,
            'pix_expires_at' => $participant->pix_expires_at,
        ]);
    }

    public function success($participantId)
    {
        Log::info('PaymentController::success called for participant: ' . $participantId);

        try {
            $participant = Participant::with(['event.organizer', 'priceTier'])->findOrFail($participantId);
            Log::info('Participant found:', [$participant->toArray()]);

            if (!$participant->isPaid()) {
                Log::warning('Participant not paid, redirecting to checkout');
                return redirect()->route('payment.checkout', $participant->id);
            }

            $event = $participant->event;
            Log::info('Event found:', [$event->toArray()]);

            $data = [
                'participant' => [
                    'id' => $participant->id,
                    'full_name' => $participant->full_name,
                    'email' => $participant->email,
                    'phone' => $participant->phone,
                    'cpf' => $participant->cpf,
                    'payment_status' => $participant->payment_status,
                    'confirmed_at' => $participant->confirmed_at,
                ],
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'event_date' => $event->event_date,
                    'location' => $event->location,
                    'location_reveal_after_payment' => $event->location_reveal_after_payment,
                    'header_image_url' => $event->header_image_url,
                    'rules' => $event->rules,
                    'organizer' => [
                        'full_name' => $event->organizer->full_name ?? 'Organizador',
                    ],
                ],
            ];

            Log::info('Rendering Payment/Success with data:', $data);

            return Inertia::render('Payment/Success', $data);
        } catch (\Exception $e) {
            Log::error('Error in PaymentController::success: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect('/')->withErrors(['error' => 'Erro ao carregar página de sucesso.']);
        }
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
            'description' => "Ingresso: {$event->name} - {$priceTier->name} - {$participant->full_name}",
            'payment_method' => 'pix',
            'metadata' => [
                'participant_id' => $participant->id,
                'event_id' => $event->id,
                'price_tier_id' => $priceTier->id,
                'participant_cpf' => $participant->cpf,
                'participant_name' => $participant->full_name
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
