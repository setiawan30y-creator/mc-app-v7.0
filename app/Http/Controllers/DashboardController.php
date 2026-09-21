<?php

namespace App\Http\Controllers;

use App\Models\BankMutation;
use App\Models\CashClosing;
use App\Models\CashMovement;
use App\Models\McTransaction;
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

        // Bank position: opening balance plus all posted bank mutations from the opening date.
        $bankMutations = BankMutation::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->when($openingDate, fn ($q) => $q->whereDate('transaction_date', '>=', Carbon::parse($openingDate)->toDateString()))
            ->where('transaction_date', '<=', $now)
            ->get(['credit', 'debit', 'transaction_date']);

        $bankCredit = (float) $bankMutations->sum('credit');
        $bankDebit = (float) $bankMutations->sum('debit');
        $bankNet = $bankCredit - $bankDebit;
        $bankBalance = $openingBank + $bankNet;

        $todayBankMutations = $bankMutations->filter(
            fn ($mutation) => Carbon::parse($mutation->transaction_date, $timezone)->isSameDay($today)
        );
        $todayBankCredit = (float) $todayBankMutations->sum('credit');
        $todayBankDebit = (float) $todayBankMutations->sum('debit');
        $todayBankNet = $todayBankCredit - $todayBankDebit;

        // Cash position must use every posted cash movement since the latest opening,
        // not only movements created today.
        $cashMovements = CashMovement::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->when($openingDate, fn ($q) => $q->whereDate('created_at', '>=', Carbon::parse($openingDate)->toDateString()))
            ->where('created_at', '<=', $now)
            ->get(['direction', 'amount', 'created_at']);

        $cashIn = (float) $cashMovements->where('direction', 'in')->sum('amount');
        $cashOut = (float) $cashMovements->where('direction', 'out')->sum('amount');
        $cashBalance = $openingCash + $cashIn - $cashOut;

        // Forex position is shown in its Rp valuation, using today's completed
        // transaction flow on top of the latest opening valuation.
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
                'bank_net' => $todayBankNet,
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
