<?php

namespace App\Services;

use App\Models\ComplianceThresholdRule;
use App\Models\Currency;
use App\Models\McTransaction;
use App\Models\McTransactionItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class McTransactionThresholdService
{
    public function __construct(
        protected ComplianceThresholdRuleService $ruleService
    ) {
    }

    /**
     * Hitung threshold seluruh item dalam transaksi
     * berdasarkan rule compliance yang berlaku pada tanggal transaksi.
     */
    public function calculateTransaction(McTransaction $transaction): Collection
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->loadMissing(['items']);

            $rule = $this->resolveRule($transaction);

            foreach ($transaction->items as $item) {
                $this->calculateItem($transaction, $item, $rule);
            }

            return $transaction->fresh(['items', 'customer'])->items;
        });
    }

    /**
     * Hitung threshold satu item.
     *
     * Parameter $rule dibuat optional agar kompatibel dengan pemanggilan
     * service lama yang hanya mengirim transaction + item.
     */
    public function calculateItem(
        McTransaction $transaction,
        McTransactionItem $item,
        ?ComplianceThresholdRule $rule = null
    ): McTransactionItem {
        if ((string) $item->transaction_id !== (string) $transaction->id) {
            throw new RuntimeException(
                'Item tidak termasuk dalam transaksi yang sedang diproses.'
            );
        }

        /*
         * Hanya transaksi BUY yang menggunakan threshold pembelian
         * mata uang asing.
         *
         * SELL dari sudut Almara = customer menjual valuta ke Almara.
         * BUY dari sudut Almara = customer membeli valuta dari Almara.
         */
        if ($item->direction !== 'buy') {
            $item->forceFill([
                'compliance_threshold_rule_id' => null,
                'threshold_equivalent_amount' => null,
                'threshold_currency_id' => null,
                'usd_equivalent_amount' => null,
                'threshold_rate' => null,
                'threshold_rate_snapshot_id' => null,
            ])->save();

            return $item->fresh();
        }

        if ((float) $item->quantity <= 0) {
            throw new RuntimeException(
                'Quantity item tidak valid untuk perhitungan threshold.'
            );
        }

        if ((float) $item->rate <= 0) {
            throw new RuntimeException(
                'Rate item tidak valid untuk perhitungan threshold.'
            );
        }

        if (!$item->rate_snapshot_id) {
            throw new RuntimeException(
                'Rate snapshot item wajib tersedia untuk audit threshold.'
            );
        }

        $currency = Currency::query()
            ->whereKey($item->currency_id)
            ->first();

        if (!$currency) {
            throw new RuntimeException(
                'Currency item tidak ditemukan.'
            );
        }

        $rule ??= $this->resolveRule($transaction);

        $this->ruleService->validate($rule);

        $basis = $rule->basis;

        if ($basis === 'usd') {
            $this->calculateUsdBasis(
                transaction: $transaction,
                item: $item,
                currency: $currency,
                rule: $rule
            );
        } elseif ($basis === 'idr') {
            $this->calculateIdrBasis(
                transaction: $transaction,
                item: $item,
                currency: $currency,
                rule: $rule
            );
        } else {
            throw new RuntimeException(
                "Basis threshold '{$basis}' belum didukung."
            );
        }

        return $item->fresh();
    }

    /**
     * Basis USD Equivalent.
     *
     * Contoh:
     * USD 100 = USD 100
     *
     * SGD 1,000 dengan rate transaksi Rp13.850
     * dan USD reference Rp17.650
     * = sekitar USD 785.55
     */
    protected function calculateUsdBasis(
        McTransaction $transaction,
        McTransactionItem $item,
        Currency $currency,
        ComplianceThresholdRule $rule
    ): void {
        $usdCurrencyId = $this->currencyId('USD');

        /*
         * Jika item memang USD, equivalent langsung sama quantity.
         */
        if ((int) $currency->id === $usdCurrencyId) {
            $equivalent = (float) $item->quantity;

            $item->forceFill([
                'compliance_threshold_rule_id' => $rule->id,
                'threshold_equivalent_amount' => $this->decimal(
                    $equivalent,
                    2
                ),
                'threshold_currency_id' => $usdCurrencyId,

                /*
                 * Tetap dipertahankan untuk kompatibilitas sistem lama.
                 */
                'usd_equivalent_amount' => $this->decimal(
                    $equivalent,
                    2
                ),

                'threshold_rate' => '1.00000000',

                /*
                 * Snapshot transaksi USD menjadi audit snapshot.
                 */
                'threshold_rate_snapshot_id' => $item->rate_snapshot_id,
            ])->save();

            return;
        }

        /*
         * Untuk valuta selain USD:
         *
         * subtotal IDR / rate USD = USD equivalent
         *
         * Kita memakai USD rate yang berlaku pada tanggal transaksi,
         * bukan rate terbaru hari ini.
         */
        $usdSnapshot = $this->findUsdSnapshotForDate(
            $transaction
        );

        $usdRate = (float) $usdSnapshot->buy_rate;

        if ($usdRate <= 0) {
            throw new RuntimeException(
                'Rate BUY USD untuk perhitungan threshold tidak valid.'
            );
        }

        $subtotal = (float) $item->subtotal;

        if ($subtotal <= 0) {
            $subtotal = (float) $item->quantity * (float) $item->rate;
        }

        $usdEquivalent = $subtotal / $usdRate;

        $item->forceFill([
            'compliance_threshold_rule_id' => $rule->id,

            'threshold_equivalent_amount' => $this->decimal(
                $usdEquivalent,
                2
            ),

            'threshold_currency_id' => $usdCurrencyId,

            /*
             * Field legacy tetap diisi agar modul lama tidak rusak.
             */
            'usd_equivalent_amount' => $this->decimal(
                $usdEquivalent,
                2
            ),

            /*
             * Untuk USD basis, threshold_rate adalah rate konversi
             * IDR -> USD yang dipakai.
             */
            'threshold_rate' => $this->decimal(
                $usdRate,
                8
            ),

            /*
             * Ini adalah snapshot USD yang benar-benar digunakan
             * untuk menghitung equivalent.
             */
            'threshold_rate_snapshot_id' => $usdSnapshot->id,
        ])->save();
    }

    /**
     * Basis IDR Equivalent.
     *
     * Untuk transaksi BUY:
     *
     * quantity x rate transaksi = subtotal IDR
     *
     * Contoh:
     * USD 100 x Rp17.650 = Rp1.765.000
     */
    protected function calculateIdrBasis(
        McTransaction $transaction,
        McTransactionItem $item,
        Currency $currency,
        ComplianceThresholdRule $rule
    ): void {
        $idrCurrencyId = $this->currencyId('IDR');

        $subtotal = (float) $item->subtotal;

        if ($subtotal <= 0) {
            $subtotal = (float) $item->quantity * (float) $item->rate;
        }

        if ($subtotal <= 0) {
            throw new RuntimeException(
                'Subtotal transaksi tidak valid untuk perhitungan threshold IDR.'
            );
        }

        $item->forceFill([
            'compliance_threshold_rule_id' => $rule->id,

            'threshold_equivalent_amount' => $this->decimal(
                $subtotal,
                2
            ),

            'threshold_currency_id' => $idrCurrencyId,

            /*
             * USD equivalent tetap dihitung juga untuk kompatibilitas
             * dan kebutuhan laporan lama.
             */
            'usd_equivalent_amount' => $this->calculateUsdEquivalent(
                $transaction,
                $item,
                $currency
            ),

            /*
             * Karena basis IDR langsung menggunakan subtotal IDR,
             * conversion rate threshold = 1.
             */
            'threshold_rate' => '1.00000000',

            /*
             * Rate transaksi tetap diaudit melalui:
             * item.rate_snapshot_id
             *
             * threshold_rate_snapshot_id tidak diperlukan untuk
             * konversi 1:1 IDR.
             */
            'threshold_rate_snapshot_id' => null,
        ])->save();
    }

    /**
     * Tetap hitung USD equivalent untuk compatibility/reporting.
     */
    protected function calculateUsdEquivalent(
        McTransaction $transaction,
        McTransactionItem $item,
        Currency $currency
    ): string {
        $usdCurrencyId = $this->currencyId('USD');

        if ((int) $currency->id === $usdCurrencyId) {
            return $this->decimal(
                (float) $item->quantity,
                2
            );
        }

        $usdSnapshot = $this->findUsdSnapshotForDate(
            $transaction
        );

        $usdRate = (float) $usdSnapshot->buy_rate;

        if ($usdRate <= 0) {
            throw new RuntimeException(
                'Rate BUY USD tidak valid untuk perhitungan USD equivalent.'
            );
        }

        $subtotal = (float) $item->subtotal;

        if ($subtotal <= 0) {
            $subtotal = (float) $item->quantity * (float) $item->rate;
        }

        return $this->decimal(
            $subtotal / $usdRate,
            2
        );
    }

    /**
     * Ambil rule compliance yang berlaku untuk tanggal transaksi.
     */
    public function resolveRule(
        McTransaction $transaction
    ): ComplianceThresholdRule {
        if (!$transaction->transaction_date) {
            throw new RuntimeException(
                'Tanggal transaksi wajib tersedia untuk menentukan rule threshold.'
            );
        }

        $rule = $this->ruleService->resolve(
            (string) $transaction->tenant_id,
            Carbon::parse($transaction->transaction_date)
        );

        $this->ruleService->validate($rule);

        return $rule;
    }

    /**
     * Usage threshold berdasarkan rule yang sama.
     *
     * Kita sengaja tidak mencampur transaksi dengan rule berbeda.
     *
     * Ini penting jika suatu saat:
     *
     * RULE-001 = USD 10.000
     * RULE-002 = IDR 20.000.000
     *
     * Nilai USD dan IDR tidak boleh dijumlahkan mentah.
     */
    public function getMonthlyUsage(
        McTransaction $transaction
    ): string {
        return $this->getUsage($transaction);
    }

    /**
     * Usage berdasarkan period rule:
     * daily / monthly / yearly.
     */
    public function getUsage(
        McTransaction $transaction
    ): string {
        if (!$transaction->customer_id) {
            return '0.00';
        }

        $rule = $this->resolveRule($transaction);

        [$start, $end] = $this->getPeriodRange(
            Carbon::parse($transaction->transaction_date),
            $rule->period
        );

        $currencyId = $this->currencyId(
            $rule->basis === 'usd' ? 'USD' : 'IDR'
        );

        $total = McTransactionItem::query()
            ->whereHas('transaction', function ($query) use (
                $transaction,
                $start,
                $end,
                $rule
            ) {
                $query
                    ->where('tenant_id', $transaction->tenant_id)
                    ->where('customer_id', $transaction->customer_id)
                    ->whereBetween(
                        'transaction_date',
                        [$start, $end]
                    )
                    ->whereIn(
                        'status',
                        [
                            'pending_payment',
                            'paid',
                            'completed',
                        ]
                    );
            })
            ->where('direction', 'buy')
            ->where(
                'compliance_threshold_rule_id',
                $rule->id
            )
            ->where(
                'threshold_currency_id',
                $currencyId
            )
            ->whereNotNull('threshold_equivalent_amount')
            ->sum('threshold_equivalent_amount');

        return $this->decimal(
            (float) $total,
            2
        );
    }

    /**
     * Usage termasuk transaksi yang sedang diproses.
     */
    public function getUsageIncludingTransaction(
        McTransaction $transaction
    ): string {
        /*
         * Pastikan item transaksi sudah mempunyai
         * threshold value dan rule ID.
         */
        $this->calculateTransaction($transaction);

        $existingUsage = (float) $this->getUsage(
            $transaction
        );

        $rule = $this->resolveRule($transaction);

        $currencyId = $this->currencyId(
            $rule->basis === 'usd' ? 'USD' : 'IDR'
        );

        $currentUsage = (float) McTransactionItem::query()
            ->where('transaction_id', $transaction->id)
            ->where('direction', 'buy')
            ->where(
                'compliance_threshold_rule_id',
                $rule->id
            )
            ->where(
                'threshold_currency_id',
                $currencyId
            )
            ->whereNotNull('threshold_equivalent_amount')
            ->sum('threshold_equivalent_amount');

        /*
         * getUsage() sudah menghitung transaction jika transaksi
         * tersebut sudah masuk DB dan statusnya memenuhi filter.
         *
         * Karena itu kita harus menghindari double count.
         */
        $transactionAlreadyIncluded = in_array(
            $transaction->status,
            [
                'pending_payment',
                'paid',
                'completed',
            ],
            true
        );

        if ($transactionAlreadyIncluded) {
            return $this->decimal(
                $existingUsage,
                2
            );
        }

        return $this->decimal(
            $existingUsage + $currentUsage,
            2
        );
    }

    /**
     * Sisa limit rule aktif.
     */
    public function getRemainingLimit(
        McTransaction $transaction
    ): string {
        $rule = $this->resolveRule($transaction);

        $usage = (float) $this->getUsageIncludingTransaction(
            $transaction
        );

        $limit = (float) $rule->limit_amount;

        return $this->decimal(
            max(0, $limit - $usage),
            2
        );
    }

    /**
     * Apakah transaksi melewati limit?
     */
    public function exceedsLimit(
        McTransaction $transaction
    ): bool {
        $rule = $this->resolveRule($transaction);

        return (float) $this->getUsageIncludingTransaction(
            $transaction
        ) > (float) $rule->limit_amount;
    }

    /**
     * Summary threshold untuk UI/API.
     */
    public function getSummary(
        McTransaction $transaction
    ): array {
        $rule = $this->resolveRule($transaction);

        $usage = $this->getUsageIncludingTransaction(
            $transaction
        );

        $limit = $this->ruleService->getLimit($rule);

        $remaining = $this->getRemainingLimit(
            $transaction
        );

        $currencyCode = $rule->basis === 'usd'
            ? 'USD'
            : 'IDR';

        $currencyId = $this->currencyId(
            $currencyCode
        );

        $summary = [
            'rule_id' => $rule->id,
            'rule_code' => $rule->code,
            'rule_name' => $rule->name,

            'basis' => $rule->basis,
            'period' => $rule->period,

            'limit_amount' => $limit,
            'usage_amount' => $usage,
            'remaining_amount' => $remaining,

            'currency_id' => $currencyId,
            'currency_code' => $currencyCode,

            'exceeds_limit' => (float) $usage
                > (float) $limit,
        ];

        /*
         * Backward compatibility dengan service lama.
         */
        if ($rule->basis === 'usd') {
            $summary['limit_usd'] = $limit;
            $summary['usage_usd'] = $usage;
            $summary['remaining_usd'] = $remaining;
        }

        return $summary;
    }

    /**
     * Tentukan awal dan akhir periode.
     */
    protected function getPeriodRange(
        Carbon $date,
        string $period
    ): array {
        return match ($period) {
            'daily' => [
                $date->copy()->startOfDay(),
                $date->copy()->endOfDay(),
            ],

            'monthly' => [
                $date->copy()->startOfMonth(),
                $date->copy()->endOfMonth(),
            ],

            'yearly' => [
                $date->copy()->startOfYear(),
                $date->copy()->endOfYear(),
            ],

            default => throw new RuntimeException(
                "Periode threshold '{$period}' tidak didukung."
            ),
        };
    }

    /**
     * Cari snapshot USD yang berlaku pada tanggal transaksi.
     *
     * Tidak mengambil rate terbaru secara sembarang.
     */
    protected function findUsdSnapshotForDate(
        McTransaction $transaction
    ) {
        $usdCurrencyId = $this->currencyId('USD');

        $date = Carbon::parse(
            $transaction->transaction_date
        );

        $snapshot = \App\Models\RateSnapshot::query()
            ->where(
                'tenant_id',
                $transaction->tenant_id
            )
            ->where(
                'currency_id',
                $usdCurrencyId
            )
            ->where(
                'is_active',
                true
            )
            ->whereNull(
                'currency_denomination_id'
            )
            ->where(
                'effective_at',
                '<=',
                $date
            )
            ->orderByDesc('effective_at')
            ->orderByDesc('id')
            ->first();

        if (!$snapshot) {
            throw new RuntimeException(
                'Rate snapshot USD yang berlaku pada tanggal transaksi tidak ditemukan.'
            );
        }

        if (
            $snapshot->buy_rate === null ||
            (float) $snapshot->buy_rate <= 0
        ) {
            throw new RuntimeException(
                'Rate BUY USD pada snapshot threshold tidak valid.'
            );
        }

        return $snapshot;
    }

    /**
     * Cari ID currency berdasarkan ISO code.
     */
    protected function currencyId(
        string $code
    ): int {
        $id = Currency::query()
            ->where('code', strtoupper($code))
            ->value('id');

        if (!$id) {
            throw new RuntimeException(
                "Currency {$code} tidak ditemukan pada master currency."
            );
        }

        return (int) $id;
    }

    /**
     * Format angka decimal tanpa scientific notation.
     */
    protected function decimal(
        float $value,
        int $precision
    ): string {
        return number_format(
            round($value, $precision),
            $precision,
            '.',
            ''
        );
    }
}