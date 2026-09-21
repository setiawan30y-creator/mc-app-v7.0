<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankMutation;
use App\Models\CashClosing;
use App\Models\CashMovement;
use App\Models\McTransaction;
use App\Models\McTransactionPayment;
use App\Models\OpeningBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        $timezone = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($timezone)->startOfDay();
        $now = Carbon::now($timezone);

        $openingDate = OpeningBalance::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('status', 'finalized')
            ->whereDate('balance_date', '<=', $today->toDateString())
            ->max('balance_date');

        $opening = $openingDate
            ? OpeningBalance::query()
                ->where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id)
                ->where('status', 'finalized')
                ->whereDate('balance_date', $openingDate)
                ->get()
            : collect();

        $openingCash = (float) $opening->where('balance_type', 'cash')->sum('amount_rp');
        $openingBank = (float) $opening->where('balance_type', 'bank')->sum('amount_rp');
        $openingForex = (float) $opening->where('balance_type', 'forex')->sum('amount_rp');

        $transactions = McTransaction::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today->toDateString())
            ->whereIn('status', ['paid', 'completed'])
            ->with('items')
            ->get();

        $purchase = 0.0;
        $sales = 0.0;
        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $amount = (float) $item->subtotal;
                if ($item->direction === 'buy') {
                    $purchase += $amount;
                } elseif ($item->direction === 'sell') {
                    $sales += $amount;
                }
            }
        }

        $openingBoundary = $openingDate
            ? Carbon::parse($openingDate, $timezone)->startOfDay()
            : null;

        $bankAccounts = BankAccount::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->active()
            ->with(['mutations' => function ($query) use ($openingBoundary, $now) {
                $query->when($openingBoundary, fn ($q) => $q->where('transaction_date', '>=', $openingBoundary))
                    ->where('transaction_date', '<=', $now)
                    ->orderByDesc('transaction_date')
                    ->orderByDesc('created_at');
            }])
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        // The bank card uses the balance ledger per bank account. This is more
        // reliable than summing only today's mutations: accounts without a new
        // mutation still retain their opening balance, while accounts with
        // mutations use the latest ledger balance.
        $bankBalance = 0.0;
        $bankCredit = 0.0;
        $bankDebit = 0.0;
        $todayBankCredit = 0.0;
        $todayBankDebit = 0.0;

        foreach ($bankAccounts as $account) {
            $mutations = $account->mutations;
            $latest = $mutations->first();
            $bankBalance += $latest
                ? (float) $latest->balance
                : (float) $account->opening_balance;

            $bankCredit += (float) $mutations->sum('credit');
            $bankDebit += (float) $mutations->sum('debit');

            $todayBankCredit += (float) $mutations
                ->filter(fn ($mutation) => Carbon::parse($mutation->transaction_date, $timezone)->isSameDay($today))
                ->sum('credit');
            $todayBankDebit += (float) $mutations
                ->filter(fn ($mutation) => Carbon::parse($mutation->transaction_date, $timezone)->isSameDay($today))
                ->sum('debit');
        }

        // Fallback for confirmed transfer payments that have not yet been linked
        // to a BankMutation. Once linked, the payment is intentionally ignored
        // here to prevent double counting.
        $transferPayments = McTransactionPayment::query()
            ->where('payment_method', 'transfer')
            ->where('payment_status', 'confirmed')
            ->whereNotNull('bank_account_id')
            ->where('paid_at', '<=', $now)
            ->whereHas('transaction', function ($q) use ($user, $openingBoundary) {
                $q->where('tenant_id', $user->tenant_id)
                    ->where('branch_id', $user->branch_id)
                    ->when($openingBoundary, fn ($query) => $query->where('transaction_date', '>=', $openingBoundary));
            })
            ->with('settlement:id,direction')
            ->get(['id', 'settlement_id', 'amount', 'paid_at', 'bank_mutation_id']);

        foreach ($transferPayments as $payment) {
            if ($payment->bank_mutation_id || !$payment->settlement) {
                continue;
            }

            $amount = (float) $payment->amount;
            if ($payment->settlement->direction === 'customer_pays') {
                $bankBalance += $amount;
                $bankCredit += $amount;
                if ($payment->paid_at && Carbon::parse($payment->paid_at, $timezone)->isSameDay($today)) {
                    $todayBankCredit += $amount;
                }
            } elseif ($payment->settlement->direction === 'customer_receives') {
                $bankBalance -= $amount;
                $bankDebit += $amount;
                if ($payment->paid_at && Carbon::parse($payment->paid_at, $timezone)->isSameDay($today)) {
                    $todayBankDebit += $amount;
                }
            }
        }

        $bankNet = $bankCredit - $bankDebit;

        $cashMovements = CashMovement::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->when($openingBoundary, fn ($q) => $q->where('created_at', '>=', $openingBoundary))
            ->where('created_at', '<=', $now)
            ->get(['direction', 'amount', 'created_at']);

        $cashIn = (float) $cashMovements->where('direction', 'in')->sum('amount');
        $cashOut = (float) $cashMovements->where('direction', 'out')->sum('amount');
        $cashBalance = $openingCash + $cashIn - $cashOut;

        $forexBalance = $openingForex + $purchase - $sales;
        $gross = $cashBalance + $bankBalance + $forexBalance;

        $closing = CashClosing::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('business_date', $today->toDateString())
            ->latest('created_at')
            ->first();

        return response()->json([
            'date' => $today->toDateString(),
            'opening' => [
                'date' => $openingDate,
                'cash' => $openingCash,
                'bank' => $openingBank,
                'forex' => $openingForex,
                'gross' => $openingCash + $openingBank + $openingForex,
            ],
            'today' => [
                'purchase' => $purchase,
                'sales' => $sales,
                'bank_credit' => $todayBankCredit,
                'bank_debit' => $todayBankDebit,
                'bank_net' => $todayBankCredit - $todayBankDebit,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'transaction_count' => $transactions->count(),
            ],
            'position' => [
                'cash' => $cashBalance,
                'bank' => $bankBalance,
                'forex' => $forexBalance,
                'gross' => $gross,
            ],
            'closing' => $closing ? [
                'status' => $closing->status,
                'expected_cash' => (float) $closing->expected_cash_amount,
                'physical_cash' => (float) $closing->physical_cash_amount,
                'difference' => (float) $closing->cash_difference_amount,
                'balanced' => $closing->isBalanced(),
            ] : null,
        ]);
    }
}
