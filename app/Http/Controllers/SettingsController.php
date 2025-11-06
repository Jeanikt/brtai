<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Verificar assinatura ativa
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();

        return Inertia::render('Settings/Index', [
            'profile' => $profile,
            'plan' => [
                'type' => $profile->plan_type,
                'is_pro' => $profile->plan_type === 'pro',
                'is_freemium' => $profile->plan_type === 'freemium',
            ],
            'subscription' => $activeSubscription ? [
                'status' => $activeSubscription->status,
                'ends_at' => $activeSubscription->ends_at,
                'is_active' => $activeSubscription->isActive(),
            ] : null
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

        // Buscar assinatura ativa
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();

        return Inertia::render('Settings/Billing', [
            'user_plan' => $profile->plan_type,
            'pro_price' => 19.00, // R$ 19/mês (conforme regra de negócio)
            'subscription' => $activeSubscription,
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
            // Gerar assinatura PIX recorrente
            $subscriptionData = $this->createProSubscription($user, $profile);

            return Inertia::render('Payment/PlanUpgrade', [
                'subscription' => $subscriptionData['subscription'],
                'pix_code' => $subscriptionData['pix_code'],
                'pix_expires_at' => $subscriptionData['expires_at']->toISOString(),
                'plan_type' => 'pro',
                'amount' => 19.00
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar assinatura Pro: ' . $e->getMessage());
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

    public function checkUpgradeStatus($subscriptionId)
    {
        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return response()->json(['paid' => false, 'status' => 'not_found']);
        }

        if ($subscription->status === 'active') {
            return response()->json(['paid' => true, 'status' => 'active']);
        }

        // Verificar status na Woovi
        try {
            $wooviService = app(\App\Services\WooviService::class);
            $result = $wooviService->getCharge($subscription->gateway_transaction_id);

            if ($result['success'] && $result['data']['charge']['status'] === 'COMPLETED') {
                // Ativar assinatura
                $subscription->update([
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                    'activated_at' => now()
                ]);

                // Atualizar plano do usuário
                $subscription->user->profile->update(['plan_type' => 'pro']);

                return response()->json(['paid' => true, 'status' => 'active']);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar status upgrade: ' . $e->getMessage());
        }

        return response()->json(['paid' => false, 'status' => $subscription->status]);
    }

    public function cancelSubscription(Request $request)
    {
        $user = $request->user();

        try {
            $subscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->first();

            if ($subscription) {
                $subscription->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now()
                ]);

                // Reverter para plano freemium no final do período
                $user->profile->update(['plan_type' => 'freemium']);

                return redirect()->back()->with('success', 'Assinatura cancelada com sucesso!');
            }

            return redirect()->back()->withErrors(['subscription' => 'Nenhuma assinatura ativa encontrada.']);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar assinatura: ' . $e->getMessage());
            return redirect()->back()->withErrors(['subscription' => 'Erro ao cancelar assinatura.']);
        }
    }

    private function createProSubscription($user, $profile)
    {
        $apiKey = config('services.woovi.app_id');
        $baseUrl = config('services.woovi.base_url', 'https://api.woovi.com');

        $payload = [
            'correlationID' => 'subscription_' . $user->id . '_' . time(),
            'value' => 1900, // R$ 19,00 em centavos (conforme regra de negócio)
            'comment' => "Assinatura Plano Pro - BrotaAI (Mensal)",
            'type' => 'DYNAMIC',
            'expiresIn' => 1800, // 30 minutos
            'additionalInfo' => [
                [
                    'key' => 'subscription_type',
                    'value' => 'pro_monthly'
                ],
                [
                    'key' => 'user_id',
                    'value' => (string) $user->id
                ],
                [
                    'key' => 'user_email',
                    'value' => $user->email
                ]
            ],
            'customer' => [
                'name' => $profile->full_name,
                'email' => $user->email,
                'taxID' => $profile->cpf ? preg_replace('/[^0-9]/', '', $profile->cpf) : '',
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => $apiKey,
            'Content-Type' => 'application/json'
        ])->timeout(30)->post($baseUrl . '/api/v1/charge', $payload);

        if ($response->successful()) {
            $paymentData = $response->json();

            // Criar registro de assinatura
            $subscription = Subscription::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'user_id' => $user->id,
                'plan_type' => 'pro',
                'status' => 'pending',
                'amount' => 19.00,
                'gateway' => 'woovi',
                'gateway_transaction_id' => $paymentData['charge']['correlationID'],
                'starts_at' => now(),
                'ends_at' => now()->addMonth(),
                'metadata' => [
                    'pix_code' => $paymentData['charge']['brCode'],
                    'qr_code_image' => $paymentData['charge']['qrCodeImage'],
                    'expires_at' => now()->addMinutes(30)->toISOString()
                ]
            ]);

            return [
                'pix_code' => $paymentData['charge']['brCode'],
                'qr_code_image' => $paymentData['charge']['qrCodeImage'],
                'expires_at' => now()->addMinutes(30),
                'transaction_id' => $paymentData['charge']['correlationID'],
                'subscription' => $subscription
            ];
        }

        $errorMessage = $response->body();
        throw new \Exception('Falha ao gerar assinatura PIX: ' . $errorMessage);
    }
}
