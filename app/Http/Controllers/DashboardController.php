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
        $today = Carbon::today();

        $opening = OpeningBalance::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('status', 'finalized')
            ->whereDate('balance_date', '<=', $today)
            ->orderByDesc('balance_date')
            ->get()
            ->groupBy('balance_date')
            ->first() ?? collect();

        $openingCash = (float) $opening->where('balance_type', 'cash')->sum('amount_rp');
        $openingBank = (float) $opening->where('balance_type', 'bank')->sum('amount_rp');
        $openingForex = (float) $opening->where('balance_type', 'forex')->sum('amount_rp');

        $transactions = McTransaction::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today)
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

        $bankCredit = (float) BankMutation::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today)
            ->sum('credit');
        $bankDebit = (float) BankMutation::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today)
            ->sum('debit');
        $bankNet = $bankCredit - $bankDebit;

        $forexBalance = $openingForex + $purchase - $sales;

        $cashIn = (float) CashMovement::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('created_at', $today)
            ->where('direction', 'in')
            ->sum('amount');
        $cashOut = (float) CashMovement::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('created_at', $today)
            ->where('direction', 'out')
            ->sum('amount');
        $cashBalance = $openingCash + $cashIn - $cashOut;
        $gross = $cashBalance + $openingBank + $bankNet + $forexBalance;

        $closing = CashClosing::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('business_date', $today)
            ->latest('created_at')
            ->first();

        return response()->json([
            'date' => $today->toDateString(),
            'opening' => [
                'cash' => $openingCash,
                'bank' => $openingBank,
                'forex' => $openingForex,
                'gross' => $openingCash + $openingBank + $openingForex,
            ],
            'today' => [
                'purchase' => $purchase,
                'sales' => $sales,
                'bank_credit' => $bankCredit,
                'bank_debit' => $bankDebit,
                'bank_net' => $bankNet,
                'cash_in' => $cashIn,
                'cash_out' => $cashOut,
                'transaction_count' => $transactions->count(),
            ],
            'position' => [
                'cash' => $cashBalance,
                'bank' => $openingBank + $bankNet,
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
