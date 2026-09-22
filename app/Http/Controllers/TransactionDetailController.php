<?php

namespace App\Http\Controllers;

use App\Models\BankMutation;
use App\Models\CashInventoryMovement;
use App\Models\CashMovement;
use App\Models\McTransaction;
use Illuminate\Http\Request;

class TransactionDetailController extends Controller
{
    public function show(Request $request, string $transaction)
    {
        $user = $request->user();

        $trx = McTransaction::query()
            ->whereKey($transaction)
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->with([
                'customer',
                'items.currency',
                'items.currencyVariant',
                'items.currencyDenomination',
                'payments.currency',
                'payments.bankAccount',
                'payments.bankMutation',
                'settlements',
                'cashMovements.currency',
                'cashMovements.currencyVariant',
                'cashMovements.currencyDenomination',
                'createdBy',
            ])
            ->firstOrFail();

        $bankMutations = BankMutation::query()
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->where('matched_transaction_id', $trx->id)
            ->with('bankAccount')
            ->orderBy('transaction_date')
            ->get();

        $inventoryMovements = CashInventoryMovement::query()
            ->where('tenant_id', $user->tenant_id)
            ->when($user->branch_id, fn ($q) => $q->where('branch_id', $user->branch_id))
            ->where('transaction_id', $trx->id)
            ->with(['inventory.currency', 'inventory.currencyVariant', 'inventory.currencyDenomination'])
            ->orderBy('created_at')
            ->get();

        $total = $trx->items->sum(fn ($item) => (float) $item->subtotal);
        $paid = $trx->payments->where('payment_status', '!=', 'failed')->sum(fn ($payment) => (float) $payment->amount);

        return view('transactions.detail', compact(
            'trx',
            'bankMutations',
            'inventoryMovements',
            'total',
            'paid'
        ));
    }
}
