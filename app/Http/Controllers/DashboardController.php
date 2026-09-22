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
                ->with(['currency', 'variant', 'denomination'])
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

        $forexRows = [];
        foreach ($opening->where('balance_type', 'forex') as $row) {
            if (!$row->currency_id || strtoupper((string) ($row->currency?->code ?? '')) === 'IDR') {
                continue;
            }
            $key = implode(':', [
                (string) $row->currency_id,
                (string) ($row->currency_variant_id ?? 0),
                (string) ($row->currency_denomination_id ?? 0),
            ]);
            if (!isset($forexRows[$key])) {
                $forexRows[$key] = [
                    'currency_id' => $row->currency_id,
                    'currency' => $row->currency?->code ?? 'VALAS',
                    'variant_id' => $row->currency_variant_id,
                    'denomination_id' => $row->currency_denomination_id,
                    'denomination' => (float) ($row->denomination?->value ?? 0),
                    'quantity' => 0.0,
                    'opening_rate' => 0.0,
                    'rate' => 0.0,
                ];
            }
            $forexRows[$key]['quantity'] += (float) $row->quantity;
            $forexRows[$key]['opening_rate'] = max($forexRows[$key]['opening_rate'], (float) $row->rate);
            $forexRows[$key]['rate'] = $forexRows[$key]['opening_rate'];
        }

        $stockTransactionsQuery = McTransaction::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereIn('status', ['paid', 'completed'])
            ->with('items.currency');

        if ($openingDate) {
            $stockTransactionsQuery->whereDate('transaction_date', '>=', $openingDate);
        }

        $stockTransactions = $stockTransactionsQuery->orderBy('transaction_date')->orderBy('created_at')->get();
        foreach ($stockTransactions as $transaction) {
            foreach ($transaction->items as $item) {
                $currencyCode = strtoupper((string) ($item->currency?->code ?? ''));
                if (!$item->currency_id || $currencyCode === 'IDR') {
                    continue;
                }

                $key = implode(':', [
                    (string) $item->currency_id,
                    (string) ($item->currency_variant_id ?? 0),
                    (string) ($item->currency_denomination_id ?? 0),
                ]);

                if (!isset($forexRows[$key])) {
                    $forexRows[$key] = [
                        'currency_id' => $item->currency_id,
                        'currency' => $currencyCode,
                        'variant_id' => $item->currency_variant_id,
                        'denomination_id' => $item->currency_denomination_id,
                        'denomination' => (float) ($item->currencyDenomination?->value ?? 0),
                        'quantity' => 0.0,
                        'opening_rate' => 0.0,
                        'rate' => 0.0,
                    ];
                }

                $qty = (float) $item->quantity;
                $forexRows[$key]['quantity'] += $item->direction === 'buy' ? $qty : -$qty;
                if ((float) $item->rate > 0) {
                    $forexRows[$key]['rate'] = (float) $item->rate;
                }
            }
        }

        $forexDetail = collect($forexRows)
            ->filter(fn ($row) => abs($row['quantity']) > 0.00005)
            ->map(function ($row) {
                $rate = $row['rate'] > 0 ? $row['rate'] : $row['opening_rate'];
                $row['rate'] = $rate;
                $row['amount_rp'] = $row['quantity'] * $rate;
                return $row;
            })
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

        $accounts = BankAccount::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->where('is_active', true)
            ->with(['currency:id,code,name', 'mutations'])
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get();

        $bankCards = $accounts->map(function ($a) use ($tz, $today, $opening) {
            $openingForAccount = $opening
                ->where('balance_type', 'bank')
                ->where('bank_account_id', $a->id)
                ->sum('amount_rp');
            $openingForAccount = (float) $openingForAccount;
            $balance = $openingForAccount > 0 ? $openingForAccount : (float) $a->opening_balance;
            $credit = 0.0;
            $debit = 0.0;
            $todayCredit = 0.0;
            $todayDebit = 0.0;
            $todayMutationCount = 0;

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
                $bankCards[$i]['today_mutation_count']++;
            }
        }

        $bankBalance = (float) $bankCards->sum('balance');
        $todayBankCredit = (float) $bankCards->sum('today_credit');
        $todayBankDebit = (float) $bankCards->sum('today_debit');
        $todayBankMutationCount = (int) $bankCards->sum('today_mutation_count');

        $cashQuery = CashMovement::where('tenant_id', $user->tenant_id)
            ->where('branch_id', $user->branch_id)
            ->whereHas('currency', fn ($q) => $q->where('code', 'IDR'));

        if ($openingDate) {
            $cashQuery->whereDate('created_at', '>=', $openingDate);
        }

        $cashMovements = $cashQuery->get(['direction', 'amount', 'transaction_id', 'movement_type', 'created_at']);
        $cashIn = (float) $cashMovements->where('direction', 'in')->sum('amount');
        $cashOut = (float) $cashMovements->where('direction', 'out')->sum('amount');

        $todayCashMovements = $cashMovements->filter(function ($movement) use ($today, $tz) {
            return Carbon::parse($movement->created_at, $tz)->isSameDay($today);
        });

        $todayCashIn = (float) $todayCashMovements->where('direction', 'in')->sum('amount');
        $todayCashOut = (float) $todayCashMovements->where('direction', 'out')->sum('amount');

        $expenseMovements = $todayCashMovements->filter(function ($movement) {
            return $movement->direction === 'out' && empty($movement->transaction_id);
        });
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
