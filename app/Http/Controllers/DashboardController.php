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
        $tz = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();
        $now = Carbon::now($tz);

        $openingDate = OpeningBalance::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('status', 'finalized')
            ->whereDate('balance_date', '<=', $today->toDateString())
            ->max('balance_date');

        $opening = $openingDate
            ? OpeningBalance::where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id)
                ->where('status', 'finalized')
                ->whereDate('balance_date', $openingDate)
                ->get()
            : collect();

        $openingCash = (float) $opening->where('balance_type', 'cash')->sum('amount_rp');
        $openingBank = (float) $opening->where('balance_type', 'bank')->sum('amount_rp');
        $openingForex = (float) $opening->where('balance_type', 'forex')->sum('amount_rp');

        $transactions = McTransaction::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today->toDateString())
            ->whereIn('status', ['paid', 'completed'])
            ->with('items')
            ->get();

        $purchase = 0.0;
        $sales = 0.0;
        foreach ($transactions as $t) {
            foreach ($t->items as $item) {
                if ($item->direction === 'buy') {
                    $purchase += (float) $item->subtotal;
                } elseif ($item->direction === 'sell') {
                    $sales += (float) $item->subtotal;
                }
            }
        }

        $accounts = BankAccount::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->with(['currency:id,code,name', 'mutations'])
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        $bankCards = $accounts->map(function ($a) use ($tz, $today) {
            $balance = (float) $a->opening_balance;
            $credit = 0.0;
            $debit = 0.0;
            $todayCredit = 0.0;
            $todayDebit = 0.0;

            foreach ($a->mutations as $m) {
                $c = (float) $m->credit;
                $d = (float) $m->debit;
                $balance += $c - $d;
                $credit += $c;
                $debit += $d;

                $md = Carbon::parse($m->transaction_date, $tz);
                if ($md->isSameDay($today)) {
                    $todayCredit += $c;
                    $todayDebit += $d;
                }
            }

            return [
                'id' => (string) $a->id,
                'bank_name' => $a->bank_name,
                'account_name' => $a->account_name,
                'account_number' => $a->account_number,
                'currency' => $a->currency?->code ?? 'IDR',
                'currency_name' => $a->currency?->name ?? 'Rupiah',
                'opening_balance' => (float) $a->opening_balance,
                'credit' => $credit,
                'debit' => $debit,
                'today_credit' => $todayCredit,
                'today_debit' => $todayDebit,
                'balance' => $balance,
            ];
        })->values();

        // Legacy fallback only. New transfer payments are required to have a real bank mutation.
        $payments = McTransactionPayment::where('payment_method', 'transfer')
            ->where('payment_status', 'confirmed')
            ->whereNotNull('bank_account_id')
            ->where('paid_at', '<=', $now)
            ->whereHas('transaction', fn ($q) => $q
                ->where('tenant_id', $user->tenant_id)
                ->where('branch_id', $user->branch_id))
            ->with('settlement:id,direction')
            ->get(['id', 'settlement_id', 'amount', 'paid_at', 'bank_account_id', 'bank_mutation_id']);

        foreach ($payments as $p) {
            if ($p->bank_mutation_id || !$p->settlement) {
                continue;
            }

            $i = $bankCards->search(fn ($a) => (string) $a['id'] === (string) $p->bank_account_id);
            if ($i === false) {
                continue;
            }

            $amount = (float) $p->amount;
            $credit = $p->settlement->direction === 'customer_pays';
            $bankCards[$i]['balance'] += $credit ? $amount : -$amount;
            $bankCards[$i][$credit ? 'credit' : 'debit'] += $amount;

            if ($p->paid_at && Carbon::parse($p->paid_at, $tz)->isSameDay($today)) {
                $bankCards[$i][$credit ? 'today_credit' : 'today_debit'] += $amount;
            }
        }

        $bankBalance = (float) $bankCards->sum('balance');
        $todayBankCredit = (float) $bankCards->sum('today_credit');
        $todayBankDebit = (float) $bankCards->sum('today_debit');

        // Cash balance starts from the latest finalized opening balance.
        // Only movements on/after that opening date are applied, preventing historical
        // cash movements from being added on top of an already-finalized opening balance.
        $cashQuery = CashMovement::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereHas('currency', fn ($q) => $q->where('code', 'IDR'));

        if ($openingDate) {
            $cashQuery->whereDate('created_at', '>=', $openingDate);
        }

        $cashMovements = $cashQuery->get(['direction', 'amount', 'created_at']);
        $cashIn = (float) $cashMovements->where('direction', 'in')->sum('amount');
        $cashOut = (float) $cashMovements->where('direction', 'out')->sum('amount');

        $todayCashMovements = $cashMovements->filter(function ($movement) use ($today, $tz) {
            return Carbon::parse($movement->created_at, $tz)->isSameDay($today);
        });

        $todayCashIn = (float) $todayCashMovements->where('direction', 'in')->sum('amount');
        $todayCashOut = (float) $todayCashMovements->where('direction', 'out')->sum('amount');

        $cashBalance = $openingCash + $cashIn - $cashOut;
        $forexBalance = $openingForex + $purchase - $sales;
        $gross = $cashBalance + $bankBalance + $forexBalance;

        $closing = CashClosing::where('tenant_id', $user->tenant_id)
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
                'cash_in' => $todayCashIn,
                'cash_out' => $todayCashOut,
                'cash_net' => $todayCashIn - $todayCashOut,
                'transaction_count' => $transactions->count(),
            ],
            'bank_accounts' => $bankCards->values(),
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
