<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
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

        // Dashboard bank balance uses the finalized opening balance as its baseline.
        // Bank mutations are then applied on top of that baseline. This keeps the
        // Mutasi Bank card consistent with the Saldo Awal card.
        $bankAccounts = BankAccount::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->with(['mutations' => function ($query) use ($openingBoundary, $now) {
                $query->when($openingBoundary, fn ($q) => $q->where('transaction_date', '>=', $openingBoundary))
                    ->where('transaction_date', '<=', $now)
                    ->orderBy('transaction_date')
                    ->orderBy('created_at');
            }])
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        $bankBalance = $openingBank;
        $bankCredit = 0.0;
        $bankDebit = 0.0;
        $todayBankCredit = 0.0;
        $todayBankDebit = 0.0;

        foreach ($bankAccounts as $account) {
            foreach ($account->mutations as $mutation) {
                $credit = (float) $mutation->credit;
                $debit = (float) $mutation->debit;
                $bankCredit += $credit;
                $bankDebit += $debit;
                $bankBalance += $credit - $debit;

                $mutationDate = Carbon::parse($mutation->transaction_date, $timezone);
                if ($mutationDate->isSameDay($today)) {
                    $todayBankCredit += $credit;
                    $todayBankDebit += $debit;
                }
            }
        }

        // Fallback for confirmed transfer payments that are not yet linked to a
        // BankMutation. Linked payments are skipped to prevent double counting.
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
            $paymentDate = $payment->paid_at ? Carbon::parse($payment->paid_at, $timezone) : null;

            if ($payment->settlement->direction === 'customer_pays') {
                $bankBalance += $amount;
                $bankCredit += $amount;
                if ($paymentDate?->isSameDay($today)) {
                    $todayBankCredit += $amount;
                }
            } elseif ($payment->settlement->direction === 'customer_receives') {
                $bankBalance -= $amount;
                $bankDebit += $amount;
                if ($paymentDate?->isSameDay($today)) {
                    $todayBankDebit += $amount;
                }
            }
        }

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
