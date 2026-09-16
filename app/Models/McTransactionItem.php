<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McTransactionItem extends Model
{
    use HasFactory;

    protected $table = 'mc_transaction_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'transaction_id',
        'currency_id',
        'currency_variant_id',
        'currency_denomination_id',
        'direction',
        'quantity',
        'rate',
        'rate_snapshot_id',
        'subtotal',

        // Compliance / Threshold
        'compliance_threshold_rule_id',
        'threshold_equivalent_amount',
        'threshold_currency_id',
        'usd_equivalent_amount',
        'threshold_rate',
        'threshold_rate_snapshot_id',

        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'rate' => 'decimal:8',
            'subtotal' => 'decimal:2',

            // Compliance / Threshold
            'threshold_equivalent_amount' => 'decimal:2',
            'usd_equivalent_amount' => 'decimal:2',
            'threshold_rate' => 'decimal:8',

            'threshold_currency_id' => 'integer',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            McTransaction::class,
            'transaction_id'
        );
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class,
            'currency_id'
        );
    }

    public function currencyVariant(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyVariant::class,
            'currency_variant_id'
        );
    }

    public function currencyDenomination(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyDenomination::class,
            'currency_denomination_id'
        );
    }

    /**
     * Rate snapshot yang digunakan sebagai
     * rate transaksi sebenarnya.
     */
    public function rateSnapshot(): BelongsTo
    {
        return $this->belongsTo(
            RateSnapshot::class,
            'rate_snapshot_id'
        );
    }

    /**
     * Compliance threshold rule yang berlaku
     * pada saat transaksi dibuat.
     */
    public function complianceThresholdRule(): BelongsTo
    {
        return $this->belongsTo(
            ComplianceThresholdRule::class,
            'compliance_threshold_rule_id'
        );
    }

    /**
     * Rate snapshot yang digunakan untuk
     * menghitung threshold equivalent.
     *
     * Contoh:
     * USD basis + transaksi EUR
     * => EUR subtotal dikonversi menggunakan
     * snapshot USD yang relevan.
     */
    public function thresholdRateSnapshot(): BelongsTo
    {
        return $this->belongsTo(
            RateSnapshot::class,
            'threshold_rate_snapshot_id'
        );
    }

    /**
     * Helper untuk mengetahui apakah item
     * termasuk transaksi BUY.
     */
    public function isBuy(): bool
    {
        return $this->direction === 'buy';
    }

    /**
     * Helper untuk mengetahui apakah item
     * termasuk transaksi SELL.
     */
    public function isSell(): bool
    {
        return $this->direction === 'sell';
    }
}