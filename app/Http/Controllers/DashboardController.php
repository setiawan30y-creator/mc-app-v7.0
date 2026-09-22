<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CashClosing;
use App\Models\CashInventory;
use App\Models\CashMovement;
use App\Models\McTransaction;
use App\Models\McTransactionItem;
use App\Models\OpeningBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // The Blade dashboard renders some values server-side while the same
        // payload is refreshed by dashboard.data. Build the initial payload
        // from the exact same source so Blade and AJAX can never drift apart.
        $dashboard = $this->data($request)->getData(true);

        return view('dashboard', compact('dashboard'));
    }

    public function data(Request $request): JsonResponse
    {
        $user = $request->user();
        $tz = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->startOfDay();

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
                ->with(['currency', 'bankAccount', 'variant', 'denomination'])
                ->get()
            : collect();

        $openingCash = (float) $opening->where('balance_type', 'cash')->sum('amount_rp');
        $openingBank = (float) $opening->where('balance_type', 'bank')->sum('amount_rp');
        $openingForex = (float) $opening->where('balance_type', 'forex')->sum('amount_rp');

        $transactionsToday = McTransaction::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereDate('transaction_date', $today->toDateString())
            ->whereIn('status', ['paid', 'completed'])
            ->with('items.currency')
            ->get();

        $purchase = 0.0;
        $sales = 0.0;
        $purchaseCount = 0;
        $salesCount = 0;

        foreach ($transactionsToday as $t) {
            $hasBuy = false;
            $hasSell = false;
            foreach ($t->items as $item) {
                if ($item->direction === 'buy') {
                    $purchase += (float) $item->subtotal;
                    $hasBuy = true;
                } elseif ($item->direction === 'sell') {
                    $sales += (float) $item->subtotal;
                    $hasSell = true;
                }
            }
            if ($hasBuy) $purchaseCount++;
            if ($hasSell) $salesCount++;
        }

        $inventories = CashInventory::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('status', 'active')
            ->with(['currency:id,code,name', 'currencyVariant:id,currency_id,name,code', 'currencyDenomination:id,currency_variant_id,value,type'])
            ->get();

        $forexDetail = $inventories
            ->filter(fn ($inventory) => strtoupper((string) ($inventory->currency?->code ?? '')) !== 'IDR')
            ->map(function ($inventory) use ($user, $opening) {
                $quantity = (float) $inventory->quantity;
                $denomination = (float) ($inventory->currencyDenomination?->value ?? 0);

                $latestRate = McTransactionItem::query()
                    ->where('currency_id', $inventory->currency_id)
                    ->where('currency_variant_id', $inventory->currency_variant_id)
                    ->where('currency_denomination_id', $inventory->currency_denomination_id)
                    ->where('rate', '>', 0)
                    ->whereHas('transaction', fn ($q) => $q
                        ->where('tenant_id', $user->tenant_id)
                        ->where('branch_id', $user->branch_id)
                        ->whereIn('status', ['paid', 'completed']))
                    ->orderByDesc('created_at')
                    ->value('rate');

                $openingRate = (float) $opening
                    ->where('balance_type', 'forex')
                    ->where('currency_id', $inventory->currency_id)
                    ->where('currency_variant_id', $inventory->currency_variant_id)
                    ->where('currency_denomination_id', $inventory->currency_denomination_id)
                    ->max('rate');

                $rate = (float) ($latestRate ?: $openingRate);
                $amountRp = round($quantity * $denomination * $rate, 2);

                return [
                    'currency_id' => $inventory->currency_id,
                    'currency' => $inventory->currency?->code ?? 'VALAS',
                    'variant_id' => $inventory->currency_variant_id,
                    'denomination_id' => $inventory->currency_denomination_id,
                    'denomination' => $denomination,
                    'quantity' => $quantity,
                    'rate' => $rate,
                    'opening_rate' => $openingRate,
                    'amount_rp' => $amountRp,
                ];
            })
            ->filter(fn ($row) => abs($row['quantity']) > 0.00005)
            ->values();

        $forexBalance = (float) $forexDetail->sum('amount_rp');

        $forexByCurrency = $forexDetail
            ->groupBy('currency')
            ->map(function ($rows, $currency) {
                return [
                    'currency' => $currency,
                    'quantity' => (float) $rows->sum('quantity'),
                    'amount_rp' => (float) $rows->sum('amount_rp'),
                ];
            })
            ->values();

        // Bank position is calculated from ONE ERP source of truth: bank_mutations.
        // Opening balance is the baseline; only mutations on/after the active opening date
        // are applied. We intentionally do not add McTransactionPayment again here.
        $accounts = BankAccount::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->with(['currency:id,code,name', 'mutations'])
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        $bankCards = $accounts->map(function ($a) use ($tz, $today, $opening, $openingDate) {
            $openingForAccount = (float) $opening
                ->where('balance_type', 'bank')
                ->where('bank_account_id', $a->id)
                ->sum('amount_rp');

            $balance = $openingDate ? $openingForAccount : (float) $a->opening_balance;
            $credit = 0.0;
            $debit = 0.0;
            $todayCredit = 0.0;
            $todayDebit = 0.0;
            $todayMutationCount = 0;

            foreach ($a->mutations as $m) {
                $md = Carbon::parse($m->transaction_date, $tz);
                if ($openingDate && $md->lt(Carbon::parse($openingDate, $tz)->startOfDay())) continue;

                $c = (float) $m->credit;
                $d = (float) $m->debit;
                $balance += $c - $d;
                $credit += $c;
                $debit += $d;

                if ($md->isSameDay($today)) {
                    $todayCredit += $c;
                    $todayDebit += $d;
                    $todayMutationCount++;
                }
            }

            return [
                'id' => (string) $a->id,
                'bank_name' => $a->bank_name,
                'account_name' => $a->account_name,
                'account_number' => $a->account_number,
                'currency' => $a->currency?->code ?? 'IDR',
                'currency_name' => $a->currency?->name ?? 'Rupiah',
                'opening_balance' => $balance - $credit + $debit,
                'credit' => $credit,
                'debit' => $debit,
                'today_credit' => $todayCredit,
                'today_debit' => $todayDebit,
                'today_mutation_count' => $todayMutationCount,
                'balance' => $balance,
            ];
        })->values();

        $bankBalance = (float) $bankCards->sum('balance');
        $todayBankCredit = (float) $bankCards->sum('today_credit');
        $todayBankDebit = (float) $bankCards->sum('today_debit');
        $todayBankMutationCount = (int) $bankCards->sum('today_mutation_count');

        $cashQuery = CashMovement::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereHas('currency', fn ($q) => $q->where('code', 'IDR'));

        if ($openingDate) $cashQuery->whereDate('created_at', '>=', $openingDate);

        $cashMovements = $cashQuery->get(['direction', 'amount', 'transaction_id', 'movement_type', 'created_at']);
        $cashIn = (float) $cashMovements->where('direction', 'in')->sum('amount');
        $cashOut = (float) $cashMovements->where('direction', 'out')->sum('amount');
        $todayCashMovements = $cashMovements->filter(fn ($movement) => Carbon::parse($movement->created_at, $tz)->isSameDay($today));
        $todayCashIn = (float) $todayCashMovements->where('direction', 'in')->sum('amount');
        $todayCashOut = (float) $todayCashMovements->where('direction', 'out')->sum('amount');

        $expenseMovements = $todayCashMovements->filter(fn ($movement) => $movement->direction === 'out' && empty($movement->transaction_id));
        $expense = (float) $expenseMovements->sum('amount');
        $expenseCount = $expenseMovements->count();

        $cashBalance = $openingCash + $cashIn - $cashOut;
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
                'purchase_count' => $purchaseCount,
                'sales' => $sales,
                'sales_count' => $salesCount,
                'expense' => $expense,
                'expense_count' => $expenseCount,
                'bank_credit' => $todayBankCredit,
                'bank_debit' => $todayBankDebit,
                'bank_net' => $todayBankCredit - $todayBankDebit,
                'bank_mutation_count' => $todayBankMutationCount,
                'cash_in' => $todayCashIn,
                'cash_out' => $todayCashOut,
                'cash_net' => $todayCashIn - $todayCashOut,
                'transaction_count' => $transactionsToday->count(),
            ],
            'bank_accounts' => $bankCards->values(),
            'forex' => [
                'balance_rp' => $forexBalance,
                'opening_rp' => $openingForex,
                'by_currency' => $forexByCurrency,
                'details' => $forexDetail,
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
