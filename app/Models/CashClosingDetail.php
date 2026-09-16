<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClosingDetail extends Model
{
    use HasFactory;

    protected $table = 'cash_closing_details';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'closing_id',
        'currency_id',
        'currency_variant_id',
        'currency_denomination_id',
        'system_quantity',
        'physical_quantity',
        'difference_quantity',
        'system_amount',
        'physical_amount',
        'difference_amount',
        'adjustment_status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'adjustment_movement_id',
    ];

    protected function casts(): array
    {
        return [
            'system_quantity' => 'decimal:4',
            'physical_quantity' => 'decimal:4',
            'difference_quantity' => 'decimal:4',
            'system_amount' => 'decimal:2',
            'physical_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function closing(): BelongsTo
    {
        return $this->belongsTo(
            CashClosing::class,
            'closing_id'
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }

    public function adjustmentMovement(): BelongsTo
    {
        return $this->belongsTo(
            CashInventoryMovement::class,
            'adjustment_movement_id'
        );
    }
}