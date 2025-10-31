<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/Index', [
            'profile' => $profile,
            'plan' => [
                'type' => $profile->plan_type,
                'is_pro' => $profile->plan_type === 'pro',
                'is_freemium' => $profile->plan_type === 'freemium',
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20'
        ]);

        $profile->update($request->only(['full_name', 'phone']));

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function billing(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/Billing', [
            'user_plan' => $profile->plan_type,
            'pro_price' => 49.00, // R$ 49/mês
        ]);
    }

    public function upgradeToPro(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Se já é Pro, retornar erro
        if ($profile->plan_type === 'pro') {
            return redirect()->back()->withErrors(['plan' => 'Você já está no plano Pro.']);
        }

        try {
            // Gerar pagamento PIX para upgrade
            $pixData = $this->generateProUpgradePayment($user, $profile);

            // Criar transação de upgrade
            $transaction = PaymentTransaction::create([
                'participant_id' => null, // Não é participante de evento
                'event_id' => null,
                'amount' => 49.00,
                'status' => 'pending',
                'gateway' => 'abacate_pay',
                'gateway_transaction_id' => $pixData['transaction_id'],
                'metadata' => [
                    'type' => 'plan_upgrade',
                    'user_id' => $user->id,
                    'plan' => 'pro',
                    'pix_code' => $pixData['pix_code'],
                    'pix_expires_at' => $pixData['expires_at']->toISOString()
                ]
            ]);

            return Inertia::render('Payment/PlanUpgrade', [
                'transaction' => $transaction,
                'pix_code' => $pixData['pix_code'],
                'pix_expires_at' => $pixData['expires_at']->toISOString(),
                'plan_type' => 'pro',
                'amount' => 49.00
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar pagamento para upgrade Pro: ' . $e->getMessage());
            return redirect()->back()->withErrors(['payment' => 'Erro ao processar upgrade. Tente novamente.']);
        }
    }

    public function upgradeSuccess(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        return Inertia::render('Settings/UpgradeSuccess', [
            'plan_type' => $profile->plan_type,
            'user' => $user
        ]);
    }

    public function checkUpgradeStatus($transactionId)
    {
        $transaction = PaymentTransaction::where('gateway_transaction_id', $transactionId)->first();

        if (!$transaction) {
            return response()->json(['paid' => false, 'status' => 'not_found']);
        }

        if ($transaction->status === 'completed') {
            return response()->json(['paid' => true, 'status' => 'completed']);
        }

        // Verificar status na AbacatePay
        try {
            $paymentStatus = $this->checkPaymentStatus($transaction->gateway_transaction_id);

            if ($paymentStatus === 'paid') {
                // Atualizar plano do usuário
                $user = $transaction->metadata['user_id'] ?
                    \App\Models\User::find($transaction->metadata['user_id']) : null;

                if ($user) {
                    $user->profile->update(['plan_type' => 'pro']);
                    $transaction->update(['status' => 'completed']);
                }

                return response()->json(['paid' => true, 'status' => 'completed']);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status upgrade: ' . $e->getMessage());
        }

        return response()->json(['paid' => false, 'status' => $transaction->status]);
    }

    private function generateProUpgradePayment($user, $profile)
    {
        $apiKey = config('services.abacatepay.key', 'abc_dev_2zd5G3HnxkPTxcPs3qd56Kkz');
        $baseUrl = config('services.abacatepay.url', 'https://api.abacatepay.com/v1');

        $payload = [
            'amount' => 4900, // R$ 49,00 em centavos
            'currency' => 'BRL',
            'description' => "Upgrade para Plano Pro - BrotaAI",
            'payment_method' => 'pix',
            'metadata' => [
                'user_id' => $user->id,
                'plan_type' => 'pro',
                'user_email' => $user->email,
                'user_name' => $profile->full_name
            ],
            'success_url' => route('settings.upgrade-success'),
            'webhook_url' => route('webhooks.abacatepay')
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(30)->post($baseUrl . '/payments', $payload);

        if ($response->successful()) {
            $paymentData = $response->json();

            return [
                'pix_code' => $paymentData['pix_code'] ?? $paymentData['payload'],
                'expires_at' => now()->addMinutes(30),
                'transaction_id' => $paymentData['id']
            ];
        }

        $errorMessage = $response->body();
        throw new \Exception('Falha ao gerar pagamento PIX: ' . $errorMessage);
    }

    private function checkPaymentStatus($transactionId)
    {
        $apiKey = config('services.abacatepay.key', 'abc_dev_2zd5G3HnxkPTxcPs3qd56Kkz');
        $baseUrl = config('services.abacatepay.url', 'https://api.abacatepay.com/v1');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json'
        ])->get($baseUrl . "/payments/{$transactionId}");

        if ($response->successful()) {
            $paymentData = $response->json();
            return $paymentData['status'] ?? 'pending';
        }

        throw new \Exception('Falha ao verificar status do pagamento');
    }
}
