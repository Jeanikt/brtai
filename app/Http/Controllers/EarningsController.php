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

        $totalEarnings = PaymentTransaction::whereHas('event', function ($query) use ($profile) {
            $query->where('organizer_id', $profile->id);
        })->where('status', 'completed')->sum('net_amount');

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
            ->get();

        $earningsByEvent = PaymentTransaction::with('event')
            ->whereHas('event', function ($query) use ($profile) {
                $query->where('organizer_id', $profile->id);
            })
            ->where('status', 'completed')
            ->select('event_id', DB::raw('SUM(net_amount) as total_earnings'))
            ->groupBy('event_id')
            ->get();

        return Inertia::render('Earnings/Index', [
            'total_earnings' => $totalEarnings,
            'available_earnings' => $availableEarnings,
            'total_withdrawn' => $totalWithdrawn,
            'operator_account' => $operatorAccount,
            'withdrawals' => $withdrawals,
            'earnings_by_event' => $earningsByEvent,
            'tables_missing' => false,
        ]);
    }

    public function storeAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'account_type' => 'required|in:CPF,CNPJ',
            'document' => 'required|string',
            'pix_key' => 'required|string',
        ]);

        // Verificar se a tabela existe
        if (!Schema::hasTable('operator_accounts')) {
            return redirect()->back()->withErrors([
                'document' => 'Sistema temporariamente indisponível. Tente novamente em alguns minutos.'
            ]);
        }

        $validation = $this->documentValidationService->validateDocument(
            $request->account_type,
            $request->document
        );

        if (!$validation['valid']) {
            return redirect()->back()->withErrors([
                'document' => $validation['message']
            ]);
        }

        $operatorAccount = OperatorAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'account_type' => $request->account_type,
                'document' => preg_replace('/[^0-9]/', '', $request->document),
                'pix_key' => $request->pix_key,
                'verified' => true,
                'verification_data' => $validation['data'] ?? null
            ]
        );

        return redirect()->back()->with('success', 'Conta verificada e salva com sucesso!');
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

        $withdrawal = Withdrawal::create([
            'operator_account_id' => $operatorAccount->id,
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        try {
            $result = $this->wooviService->createWithdrawal([
                'value' => $request->amount * 100,
                'pixKey' => $operatorAccount->pix_key,
                'correlationID' => 'withdrawal_' . $withdrawal->id
            ]);

            if ($result['success']) {
                $withdrawal->markAsProcessing($result['data']['payment']['correlationID']);
                return redirect()->back()->with('success', 'Saque solicitado com sucesso!');
            } else {
                $withdrawal->markAsFailed($result['error']);
                return redirect()->back()->withErrors([
                    'withdrawal' => 'Erro ao processar saque: ' . $result['error']
                ]);
            }
        } catch (\Exception $e) {
            $withdrawal->markAsFailed($e->getMessage());
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

        $operatorAccount = OperatorAccount::where('user_id', $user->id)->firstOrFail();
        $operatorAccount->update([
            'pix_key' => $request->pix_key
        ]);

        return redirect()->back()->with('success', 'Chave PIX atualizada com sucesso!');
    }
}
