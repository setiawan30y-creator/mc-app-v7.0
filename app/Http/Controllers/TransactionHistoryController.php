<?php

namespace App\Http\Controllers;

use App\Models\McTransaction;
use Illuminate\Http\Request;

class TransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = McTransaction::query()
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->with([
                'customer:id,customer_number,full_name,phone',
                'items.currency:id,code,name',
                'items.currencyVariant:id,currency_id,name,code',
                'items.currencyDenomination:id,currency_variant_id,value,type',
                'payments:id,transaction_id,payment_method,amount,bank_account_id,payment_status,transfer_reference',
            ]);

        $search = trim((string) $request->input('q'));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer->where('full_name', 'like', "%{$search}%")
                            ->orWhere('customer_number', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        $query->when($request->filled('date_from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date_from));
        $query->when($request->filled('date_to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date_to));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('direction'), function ($q) use ($request) {
            $q->whereHas('items', fn ($items) => $items->where('direction', $request->direction));
        });
        $query->when($request->filled('payment_method'), function ($q) use ($request) {
            $q->whereHas('payments', fn ($payments) => $payments->where('payment_method', $request->payment_method)->where('payment_status', '!=', 'failed'));
        });

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $summaryQuery = clone $query;
        $summaryTransactions = $summaryQuery->get();
        $summary = [
            'count' => $summaryTransactions->count(),
            'buy' => $summaryTransactions->filter(fn ($trx) => $trx->items->contains('direction', 'buy'))->count(),
            'sell' => $summaryTransactions->filter(fn ($trx) => $trx->items->contains('direction', 'sell'))->count(),
            'paid' => $summaryTransactions->where('status', 'paid')->count(),
        ];

        return view('transactions.history', compact('transactions', 'summary'));
    }
}
