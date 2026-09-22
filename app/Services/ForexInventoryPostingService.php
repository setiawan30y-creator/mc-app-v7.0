<?php

namespace App\Services;

use App\Models\CashInventory;
use App\Models\CashInventoryMovement;
use App\Models\McTransaction;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ForexInventoryPostingService
{
    public function __construct(
        protected ClosingPeriodGuard $closingPeriodGuard,
    ) {
    }

    /**
     * Post the foreign-currency side of a paid transaction.
     *
     * BUY  = dealer receives forex (IN)
     * SELL = dealer gives forex (OUT)
     */
    public function post(McTransaction $transaction): void
    {
        $transaction->loadMissing(['items.currency', 'items.currencyVariant', 'items.currencyDenomination']);

        $this->closingPeriodGuard->assertOpen(
            $transaction->transaction_date,
            $transaction->tenant_id,
            $transaction->branch_id,
        );

        $items = $transaction->items
            ->filter(fn ($item) => strtoupper((string) $item->currency?->code) !== 'IDR');

        if ($items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($transaction, $items) {
            $groups = $items->groupBy(function ($item) use ($transaction) {
                if (!$item->currency_denomination_id) {
                    throw new RuntimeException(
                        'Denominasi valas wajib dipilih untuk transaksi ' . $transaction->transaction_no . '.'
                    );
                }

                return implode(':', [
                    $item->currency_id,
                    $item->currency_variant_id,
                    $item->currency_denomination_id,
                ]);
            });

            foreach ($groups as $group) {
                $first = $group->first();
                $quantityIn = (float) $group->where('direction', 'buy')->sum('quantity');
                $quantityOut = (float) $group->where('direction', 'sell')->sum('quantity');
                $netQuantity = round($quantityIn - $quantityOut, 4);

                if ($netQuantity == 0.0) {
                    continue;
                }

                $inventory = CashInventory::query()
                    ->where('tenant_id', $transaction->tenant_id)
                    ->where('branch_id', $transaction->branch_id)
                    ->where('currency_id', $first->currency_id)
                    ->where('currency_variant_id', $first->currency_variant_id)
                    ->where('currency_denomination_id', $first->currency_denomination_id)
                    ->lockForUpdate()
                    ->first();

                if (!$inventory) {
                    $opening = $this->openingBaseline($transaction, $first);

                    $inventory = CashInventory::query()->create([
                        'id' => (string) Str::ulid(),
                        'tenant_id' => $transaction->tenant_id,
                        'branch_id' => $transaction->branch_id,
                        'currency_id' => $first->currency_id,
                        'currency_variant_id' => $first->currency_variant_id,
                        'currency_denomination_id' => $first->currency_denomination_id,
                        'quantity' => $opening['quantity'],
                        'total_amount' => $opening['amount'],
                        'status' => 'active',
                    ]);

                    if ($opening['quantity'] != 0.0) {
                        CashInventoryMovement::query()->create([
                            'id' => (string) Str::ulid(),
                            'tenant_id' => $transaction->tenant_id,
                            'branch_id' => $transaction->branch_id,
                            'inventory_id' => $inventory->id,
                            'transaction_id' => null,
                            'cash_movement_id' => null,
                            'direction' => 'in',
                            'quantity' => $opening['quantity'],
                            'amount' => $opening['amount'],
                            'balance_quantity' => $opening['quantity'],
                            'balance_amount' => $opening['amount'],
                            'movement_type' => 'opening',
                            'reference' => 'OPENING-' . $transaction->transaction_date->format('Ymd'),
                            'notes' => 'Baseline stok valas dari saldo awal ERP.',
                            'created_by' => $transaction->created_by,
                        ]);
                    }
                } else {
                    $inventory = CashInventory::query()
                        ->whereKey($inventory->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                if (CashInventoryMovement::query()
                    ->where('inventory_id', $inventory->id)
                    ->where('transaction_id', $transaction->id)
                    ->exists()) {
                    continue;
                }

                $denominationValue = (float) ($first->currencyDenomination?->value ?? 0);
                if ($denominationValue <= 0) {
                    throw new RuntimeException(
                        'Nilai denominasi tidak valid untuk transaksi ' . $transaction->transaction_no . '.'
                    );
                }

                $currentQuantity = (float) $inventory->quantity;
                $currentAmount = (float) $inventory->total_amount;
                $newQuantity = round($currentQuantity + $netQuantity, 4);

                if ($newQuantity < -0.0001) {
                    throw new RuntimeException(
                        'Stok ' . $first->currency?->code . ' ' . $denominationValue
                        . ' tidak mencukupi untuk transaksi ' . $transaction->transaction_no
                        . '. Sisa stok: ' . $currentQuantity . ', kebutuhan jual: ' . $quantityOut . '.'
                    );
                }

                $movementAmount = round(abs($netQuantity) * $denominationValue, 2);
                $newAmount = round($currentAmount + ($netQuantity > 0 ? $movementAmount : -$movementAmount), 2);
                $direction = $netQuantity > 0 ? 'in' : 'out';

                $inventory->update([
                    'quantity' => max(0, $newQuantity),
                    'total_amount' => max(0, $newAmount),
                ]);

                CashInventoryMovement::query()->create([
                    'id' => (string) Str::ulid(),
                    'tenant_id' => $transaction->tenant_id,
                    'branch_id' => $transaction->branch_id,
                    'inventory_id' => $inventory->id,
                    'transaction_id' => $transaction->id,
                    'cash_movement_id' => null,
                    'direction' => $direction,
                    'quantity' => abs($netQuantity),
                    'amount' => $movementAmount,
                    'balance_quantity' => max(0, $newQuantity),
                    'balance_amount' => max(0, $newAmount),
                    'movement_type' => 'transaction',
                    'reference' => $transaction->transaction_no,
                    'notes' => 'Posting stok valas dari transaksi ' . $transaction->transaction_no . '.',
                    'created_by' => $transaction->updated_by ?: $transaction->created_by,
                ]);
            }
        });
    }

    protected function openingBaseline(McTransaction $transaction, $item): array
    {
        $date = $transaction->transaction_date;

        $latestDate = OpeningBalance::query()
            ->where('tenant_id', $transaction->tenant_id)
            ->where('branch_id', $transaction->branch_id)
            ->where('balance_type', 'forex')
            ->where('status', 'finalized')
            ->where('balance_date', '<=', $date)
            ->max('balance_date');

        if (!$latestDate) {
            return ['quantity' => 0.0, 'amount' => 0.0];
        }

        $rows = OpeningBalance::query()
            ->where('tenant_id', $transaction->tenant_id)
            ->where('branch_id', $transaction->branch_id)
            ->where('balance_type', 'forex')
            ->where('status', 'finalized')
            ->where('balance_date', $latestDate)
            ->where('currency_id', $item->currency_id)
            ->where('currency_variant_id', $item->currency_variant_id)
            ->where('currency_denomination_id', $item->currency_denomination_id)
            ->get(['quantity', 'amount_rp']);

        return [
            'quantity' => (float) $rows->sum('quantity'),
            'amount' => (float) $rows->sum('quantity') * (float) ($item->currencyDenomination?->value ?? 0),
        ];
    }
}
