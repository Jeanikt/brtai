<?php

namespace App\Http\Controllers;

use App\Models\OperatorAccount;
use App\Models\Withdrawal;
use App\Models\PaymentTransaction;
use App\Services\DocumentValidationService;
use App\Services\WooviService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class EarningsController extends Controller
{
    private $documentValidationService;
    private $wooviService;

    public function __construct(DocumentValidationService $documentValidationService, WooviService $wooviService)
    {
        $this->documentValidationService = $documentValidationService;
        $this->wooviService = $wooviService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        // Verificar se as tabelas existem antes de fazer as queries
        $tablesExist = Schema::hasTable('payment_transactions') &&
            Schema::hasTable('withdrawals') &&
            Schema::hasTable('operator_accounts');

        if (!$tablesExist) {
            return Inertia::render('Earnings/Index', [
                'total_earnings' => 0,
                'available_earnings' => 0,
                'total_withdrawn' => 0,
                'operator_account' => null,
                'withdrawals' => [],
                'earnings_by_event' => [],
                'tables_missing' => true,
            ]);
        }

        try {
            // Calcular ganhos totais - CORREÇÃO: Usar net_amount quando disponível
            $totalEarnings = PaymentTransaction::whereHas('event', function ($query) use ($profile) {
                $query->where('organizer_id', $profile->id);
            })
                ->where('status', 'completed')
                ->sum('net_amount');

            Log::info('Earnings calculation', [
                'organizer_id' => $profile->id,
                'total_earnings' => $totalEarnings
            ]);

            $totalWithdrawn = Withdrawal::whereHas('operatorAccount', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->where('status', 'completed')->sum('amount');

            $availableEarnings = $totalEarnings - $totalWithdrawn;

            $operatorAccount = OperatorAccount::where('user_id', $user->id)->first();

            $withdrawals = Withdrawal::with('operatorAccount')
                ->whereHas('operatorAccount', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'id' => $withdrawal->id,
                        'amount' => $withdrawal->amount,
                        'status' => $withdrawal->status,
                        'failure_reason' => $withdrawal->failure_reason,
                        'created_at' => $withdrawal->created_at,
                        'updated_at' => $withdrawal->updated_at,
                    ];
                });

            // Ganhos por evento
            $earningsByEvent = PaymentTransaction::with('event')
                ->whereHas('event', function ($query) use ($profile) {
                    $query->where('organizer_id', $profile->id);
                })
                ->where('status', 'completed')
                ->select('event_id', DB::raw('SUM(net_amount) as total_earnings'))
                ->groupBy('event_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'event_id' => $item->event_id,
                        'total_earnings' => $item->total_earnings,
                        'event' => $item->event ? [
                            'id' => $item->event->id,
                            'name' => $item->event->name,
                            'event_date' => $item->event->event_date,
                        ] : null
                    ];
                });

            return Inertia::render('Earnings/Index', [
                'total_earnings' => $totalEarnings,
                'available_earnings' => $availableEarnings,
                'total_withdrawn' => $totalWithdrawn,
                'operator_account' => $operatorAccount ? [
                    'id' => $operatorAccount->id,
                    'account_type' => $operatorAccount->account_type,
                    'document' => $operatorAccount->document,
                    'document_formatted' => $this->formatDocument($operatorAccount->document, $operatorAccount->account_type),
                    'pix_key' => $operatorAccount->pix_key,
                    'verified' => $operatorAccount->verified,
                ] : null,
                'withdrawals' => $withdrawals,
                'earnings_by_event' => $earningsByEvent,
                'tables_missing' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in EarningsController: ' . $e->getMessage());

            return Inertia::render('Earnings/Index', [
                'total_earnings' => 0,
                'available_earnings' => 0,
                'total_withdrawn' => 0,
                'operator_account' => null,
                'withdrawals' => [],
                'earnings_by_event' => [],
                'tables_missing' => false,
                'error' => 'Erro ao carregar dados de ganhos'
            ]);
        }
    }

    public function storeAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'account_type' => 'required|in:CPF,CNPJ',
            'document' => 'required|string',
            'pix_key' => 'required|string',
        ]);

        // Limpar documento (remover caracteres não numéricos)
        $cleanDocument = preg_replace('/[^0-9]/', '', $request->document);

        // Validar CPF/CNPJ
        $validation = $this->documentValidationService->validateDocument(
            $request->account_type,
            $cleanDocument
        );

        if (!$validation['valid']) {
            return redirect()->back()->withErrors([
                'document' => $validation['message']
            ]);
        }

        try {
            $operatorAccount = OperatorAccount::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'account_type' => $request->account_type,
                    'document' => $cleanDocument,
                    'pix_key' => $request->pix_key,
                    'verified' => true,
                    'verification_data' => $validation['data'] ?? null
                ]
            );

            return redirect()->back()->with('success', 'Conta verificada e salva com sucesso!');
        } catch (\Exception $e) {
            Log::error('Error saving operator account: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'document' => 'Erro ao salvar conta. Tente novamente.'
            ]);
        }
    }

    public function requestWithdrawal(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        // Verificar se as tabelas existem
        if (!Schema::hasTable('operator_accounts') || !Schema::hasTable('withdrawals')) {
            return redirect()->back()->withErrors([
                'withdrawal' => 'Sistema temporariamente indisponível. Tente novamente em alguns minutos.'
            ]);
        }

        $operatorAccount = OperatorAccount::where('user_id', $user->id)->first();
        if (!$operatorAccount) {
            return redirect()->back()->withErrors([
                'withdrawal' => 'Você precisa cadastrar uma conta antes de solicitar um saque.'
            ]);
        }

        // Recalcular ganhos disponíveis
        $totalEarnings = PaymentTransaction::whereHas('event', function ($query) use ($profile) {
            $query->where('organizer_id', $profile->id);
        })->where('status', 'completed')->sum('net_amount');

        $totalWithdrawn = Withdrawal::whereHas('operatorAccount', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'completed')->sum('amount');

        $availableEarnings = $totalEarnings - $totalWithdrawn;

        if ($request->amount > $availableEarnings) {
            return redirect()->back()->withErrors([
                'withdrawal' => 'Valor solicitado é maior que o saldo disponível.'
            ]);
        }

        // Verificar valor mínimo baseado no plano
        $minAmount = $profile->plan_type === 'pro' ? 10 : 50;
        if ($request->amount < $minAmount) {
            return redirect()->back()->withErrors([
                'withdrawal' => "Valor mínimo para saque é R$ {$minAmount}."
            ]);
        }

        try {
            $withdrawal = Withdrawal::create([
                'operator_account_id' => $operatorAccount->id,
                'amount' => $request->amount,
                'status' => 'pending'
            ]);

            $result = $this->wooviService->createWithdrawal([
                'value' => $request->amount * 100,
                'pixKey' => $operatorAccount->pix_key,
                'correlationID' => 'withdrawal_' . $withdrawal->id
            ]);

            if ($result['success']) {
                $withdrawal->update([
                    'status' => 'processing',
                    'gateway_transaction_id' => $result['data']['payment']['correlationID'] ?? null
                ]);
                return redirect()->back()->with('success', 'Saque solicitado com sucesso!');
            } else {
                $withdrawal->update([
                    'status' => 'failed',
                    'failure_reason' => $result['error']
                ]);
                return redirect()->back()->withErrors([
                    'withdrawal' => 'Erro ao processar saque: ' . $result['error']
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Withdrawal error: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'withdrawal' => 'Erro ao processar saque. Tente novamente.'
            ]);
        }
    }

    public function updateAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'pix_key' => 'required|string'
        ]);

        if (!Schema::hasTable('operator_accounts')) {
            return redirect()->back()->withErrors([
                'pix_key' => 'Sistema temporariamente indisponível.'
            ]);
        }

        try {
            $operatorAccount = OperatorAccount::where('user_id', $user->id)->firstOrFail();
            $operatorAccount->update([
                'pix_key' => $request->pix_key
            ]);

            return redirect()->back()->with('success', 'Chave PIX atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Error updating operator account: ' . $e->getMessage());
            return redirect()->back()->withErrors([
                'pix_key' => 'Erro ao atualizar chave PIX.'
            ]);
        }
    }

    private function formatDocument($document, $type)
    {
        if ($type === 'CPF') {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document);
        } else {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document);
        }
    }
}
