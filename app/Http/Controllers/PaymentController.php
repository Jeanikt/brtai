<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\PaymentTransaction;
use App\Services\WooviService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PaymentController extends Controller
{
    private $wooviService;

    public function __construct(WooviService $wooviService)
    {
        $this->wooviService = $wooviService;
    }

    public function checkout($participantId)
    {
        $participant = Participant::with(['event', 'priceTier'])->findOrFail($participantId);

        if ($participant->isPaid()) {
            return redirect()->route('payment.success', $participant->id);
        }

        // DEBUG: Log do estado atual
        Log::info('PaymentController::checkout - Participant state', [
            'participant_id' => $participant->id,
            'pix_code_exists' => !empty($participant->pix_code),
            'pix_expired' => $participant->isPixExpired(),
            'payment_status' => $participant->payment_status
        ]);

        // Gerar ou reutilizar PIX
        if (!$participant->pix_code || $participant->isPixExpired()) {
            try {
                Log::info('Generating new PIX payment for participant', [
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'current_pix_code' => $participant->pix_code ? 'exists' : 'null',
                    'pix_expired' => $participant->isPixExpired() ? 'yes' : 'no'
                ]);

                $pixData = $this->generatePixPayment($participant);

                // DEBUG: Log dos dados do PIX gerados
                Log::info('PIX data generated', [
                    'participant_id' => $participant->id,
                    'pix_code_length' => strlen($pixData['pix_code']),
                    'qr_code_image' => $pixData['qr_code_image'],
                    'transaction_id' => $pixData['transaction_id']
                ]);

                $participant->update([
                    'pix_code' => $pixData['pix_code'],
                    'pix_expires_at' => $pixData['expires_at'],
                    'transaction_id' => $pixData['transaction_id'],
                    'pix_qr_code' => $pixData['qr_code_image'],
                    'payment_amount' => $participant->priceTier->price,
                ]);

                // Recarregar o participant com os dados atualizados
                $participant->refresh();

                Log::info('PIX payment generated successfully', [
                    'participant_id' => $participant->id,
                    'transaction_id' => $pixData['transaction_id'],
                    'expires_at' => $pixData['expires_at'],
                    'pix_code_saved' => !empty($participant->pix_code),
                    'qr_code_saved' => !empty($participant->pix_qr_code)
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao gerar PIX: ' . $e->getMessage(), [
                    'participant_id' => $participant->id,
                    'event_id' => $participant->event_id,
                    'exception_trace' => $e->getTraceAsString()
                ]);
                return back()->withErrors(['payment' => 'Erro ao gerar pagamento PIX: ' . $e->getMessage()]);
            }
        } else {
            Log::info('Reusing existing PIX payment', [
                'participant_id' => $participant->id,
                'transaction_id' => $participant->transaction_id,
                'pix_code' => $participant->pix_code ? 'present' : 'missing',
                'qr_code' => $participant->pix_qr_code ? 'present' : 'missing'
            ]);
        }

        // DEBUG: Log dos dados que serão enviados para o frontend
        Log::info('Sending data to frontend', [
            'participant_id' => $participant->id,
            'pix_code' => $participant->pix_code ? 'present' : 'missing',
            'pix_qr_code' => $participant->pix_qr_code ? 'present' : 'missing',
            'pix_expires_at' => $participant->pix_expires_at
        ]);

        return Inertia::render('Payment/Checkout', [
            'participant' => $participant->load('event.organizer'),
            'pix_code' => $participant->pix_code,
            'pix_qr_code' => $participant->pix_qr_code,
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
                Log::warning('Participant not paid, redirecting to checkout', [
                    'participant_id' => $participant->id,
                    'payment_status' => $participant->payment_status
                ]);
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

        if (!$participant->transaction_id) {
            Log::warning('No transaction ID found for participant', ['participant_id' => $participantId]);
            return response()->json(['paid' => false]);
        }

        // Verificar status na Woovi
        $result = $this->wooviService->getCharge($participant->transaction_id);

        if ($result['success']) {
            $charge = $result['data']['charge'];
            $isPaid = $charge['status'] === 'COMPLETED';

            Log::info('Woovi charge status check', [
                'participant_id' => $participantId,
                'charge_status' => $charge['status'],
                'is_paid' => $isPaid,
                'current_participant_status' => $participant->payment_status
            ]);

            if ($isPaid && $participant->payment_status !== 'paid') {
                Log::info('Marking participant as paid', ['participant_id' => $participantId]);
                $participant->markAsPaid();

                // Registrar transação
                $this->createPaymentTransaction($participant, $charge);
            }

            return response()->json([
                'paid' => $isPaid,
                'status' => $charge['status'],
            ]);
        } else {
            Log::error('Error checking Woovi charge status', [
                'participant_id' => $participantId,
                'error' => $result['error'] ?? 'Unknown error'
            ]);
        }

        return response()->json(['paid' => false]);
    }

    private function generatePixPayment(Participant $participant)
    {
        $event = $participant->event;
        $priceTier = $participant->priceTier;

        $value = $event->is_free ? 0 : $priceTier->price;
        $valueInCents = (int) ($value * 100);

        $chargeData = [
            'correlation_id' => 'participant_' . $participant->id . '_' . time(),
            'value' => $valueInCents,
            'comment' => "Ingresso: {$event->name} - {$participant->full_name}",
            'additional_info' => [
                [
                    'key' => 'participant_id',
                    'value' => (string) $participant->id,
                ],
                [
                    'key' => 'event_id',
                    'value' => (string) $event->id,
                ],
                [
                    'key' => 'participant_name',
                    'value' => $participant->full_name,
                ],
                [
                    'key' => 'participant_cpf',
                    'value' => $participant->cpf,
                ]
            ],
            'customer' => [
                'name' => $participant->full_name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'taxID' => $participant->cpf,
            ]
        ];

        Log::info('Attempting to generate PIX payment via Woovi', [
            'participant_id' => $participant->id,
            'value' => $value,
            'value_in_cents' => $valueInCents,
            'correlation_id' => $chargeData['correlation_id'],
            'event_name' => $event->name
        ]);

        $result = $this->wooviService->createCharge($chargeData);

        if (!$result['success']) {
            Log::error('Failed to generate PIX payment via Woovi', [
                'participant_id' => $participant->id,
                'error' => $result['error'],
                'charge_data' => $chargeData
            ]);
            throw new \Exception('Erro ao criar cobrança PIX: ' . $result['error']);
        }

        $charge = $result['data']['charge'];

        // DEBUG: Log completo da resposta
        Log::info('PIX payment created successfully via Woovi', [
            'participant_id' => $participant->id,
            'correlation_id' => $charge['correlationID'],
            'charge_keys' => array_keys($charge),
            'brcode_present' => isset($charge['brCode']),
            'qrCodeImage_present' => isset($charge['qrCodeImage']),
            'paymentLinkUrl_present' => isset($charge['paymentLinkUrl']),
            // 'full_charge' => $charge // Descomente apenas para debug
        ]);

        // Verificar se temos os campos necessários
        if (!isset($charge['brCode'])) {
            Log::error('Missing brCode in Woovi response', [
                'participant_id' => $participant->id,
                'charge' => $charge
            ]);
            throw new \Exception('Resposta da Woovi não contém código PIX.');
        }

        // Usar qrCodeImage da resposta ou gerar URL alternativa
        $qrCodeImage = $charge['qrCodeImage'] ?? $this->wooviService->generateQrCodeImageUrl($charge['correlationID']);

        return [
            'pix_code' => $charge['brCode'],
            'qr_code_image' => $qrCodeImage,
            'expires_at' => now()->addMinutes(30),
            'transaction_id' => $charge['correlationID'],
        ];
    }

    private function createPaymentTransaction(Participant $participant, array $chargeData)
    {
        $organizer = $participant->event->organizer;

        // ✅ APLICANDO NOVAS REGRAS: Taxas por plano
        if ($organizer->plan_type === 'pro') {
            $feePercentage = 0.055; // 5.5% para Pro
            $fixedFee = 0.85; // R$ 0,85 fixo para PIX
        } else {
            $feePercentage = 0.065; // 6.5% para Free
            $fixedFee = 0.85; // R$ 0,85 fixo para PIX
        }

        // 🔹 Valor total do pagamento
        $paymentAmount = $participant->payment_amount ?? $participant->priceTier->price;

        // 🔹 Cálculo da taxa e do ganho líquido
        $feeAmount = ($paymentAmount * $feePercentage) + $fixedFee;
        $netAmount = max(0, $paymentAmount - $feeAmount);

        // 🔹 Log detalhado
        Log::info('💰 Calculando transação com NOVAS REGRAS', [
            'participant_id' => $participant->id,
            'organizer_plan' => $organizer->plan_type,
            'payment_amount' => $paymentAmount,
            'fee_percentage' => $feePercentage,
            'fixed_fee' => $fixedFee,
            'fee_amount_total' => $feeAmount,
            'net_amount' => $netAmount,
        ]);

        // 🔹 Registro no banco
        return PaymentTransaction::create([
            'participant_id' => $participant->id,
            'event_id' => $participant->event_id,
            'amount' => $paymentAmount,
            'status' => 'completed',
            'gateway' => 'woovi',
            'gateway_transaction_id' => $chargeData['correlationID'],
            'gateway_response' => $chargeData,
            'fee_amount' => $feeAmount,
            'net_amount' => $netAmount,
            'processed_at' => now(),
        ]);
    }}
