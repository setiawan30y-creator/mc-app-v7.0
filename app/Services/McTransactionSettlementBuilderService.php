<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\McTransaction;
use App\Models\McTransactionSettlement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class McTransactionSettlementBuilderService
{
    /**
     * Build settlement automatically from transaction items.
     *
     * Business rules:
     *
     * BUY:
     * - Customer gives foreign currency.
     * - Customer receives IDR.
     *
     * SELL:
     * - Customer gives IDR.
     * - Customer receives foreign currency.
     */
    public function build(McTransaction $transaction): Collection
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->loadMissing([
                'items',
                'items.currency',
            ]);

            if ($transaction->items->isEmpty()) {
                throw new RuntimeException(
                    'Tidak dapat membuat settlement: transaksi tidak memiliki item.'
                );
            }

            if (
                McTransactionSettlement::query()
                    ->where('transaction_id', $transaction->id)
                    ->exists()
            ) {
                throw new RuntimeException(
                    'Settlement transaksi sudah tersedia.'
                );
            }

            $this->validateTransactionItems($transaction->items);

            $idCurrency = $this->getIdrCurrency();

            $buckets = [
                'customer_pays' => [],
                'customer_receives' => [],
            ];

            foreach ($transaction->items as $item) {
                $foreignCurrencyId = (string) $item->currency_id;
                $quantity = (float) $item->quantity;
                $subtotal = (float) $item->subtotal;

                if ($item->direction === 'buy') {
                    $this->addAmount(
                        $buckets['customer_pays'],
                        $foreignCurrencyId,
                        $quantity
                    );

                    $this->addAmount(
                        $buckets['customer_receives'],
                        (string) $idCurrency->id,
                        $subtotal
                    );

                    continue;
                }

                $this->addAmount(
                    $buckets['customer_pays'],
                    (string) $idCurrency->id,
                    $subtotal
                );

                $this->addAmount(
                    $buckets['customer_receives'],
                    $foreignCurrencyId,
                    $quantity
                );
            }

            $settlements = collect();

            foreach (['customer_pays', 'customer_receives'] as $direction) {
                foreach ($buckets[$direction] as $currencyId => $amount) {
                    if ($amount <= 0) {
                        continue;
                    }

                    $settlement = McTransactionSettlement::query()->create([
                        'id' => (string) Str::ulid(),
                        'transaction_id' => $transaction->id,
                        'direction' => $direction,
                        'currency_id' => $currencyId,
                        'amount' => $this->normalizeAmount($amount),
                        'status' => 'pending',
                        'notes' => null,
                    ]);

                    $settlements->push($settlement);
                }
            }

            if ($settlements->isEmpty()) {
                throw new RuntimeException(
                    'Settlement tidak dapat dibuat dari item transaksi.'
                );
            }

            $transaction->forceFill([
                'settlement_status' => 'pending',
            ])->save();

            return $settlements;
        });
    }

    protected function validateTransactionItems(Collection $items): void
    {
        foreach ($items as $index => $item) {
            if (!in_array($item->direction, ['buy', 'sell'], true)) {
                throw new RuntimeException("Item {$index} memiliki direction tidak valid.");
            }

            if ($item->quantity === null || (float) $item->quantity <= 0) {
                throw new RuntimeException("Item {$index} memiliki quantity tidak valid.");
            }

            if ($item->subtotal === null || (float) $item->subtotal <= 0) {
                throw new RuntimeException("Item {$index} memiliki subtotal tidak valid.");
            }

            if ($item->currency_id === null || (string) $item->currency_id === '') {
                throw new RuntimeException("Item {$index} tidak memiliki currency.");
            }
        }
    }

    protected function addAmount(array &$bucket, string $currencyId, float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        if (!array_key_exists($currencyId, $bucket)) {
            $bucket[$currencyId] = 0;
        }

        $bucket[$currencyId] += $amount;
    }

    /**
     * Resolve IDR by its stable business code, never by a hard-coded PK.
     */
    protected function getIdrCurrency(): Currency
    {
        $currency = Currency::query()
            ->where('code', 'IDR')
            ->first();

        if (!$currency) {
            throw new RuntimeException(
                'Currency IDR tidak ditemukan pada master currency.'
            );
        }

        return $currency;
    }

    protected function normalizeAmount(float $amount): string
    {
        return number_format(round($amount, 2), 2, '.', '');
    }
}
