<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashMovement extends Model
{
    use HasFactory;

    protected $table = 'cash_movements';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'transaction_id',
        'payment_id',
        'currency_id',
        'currency_variant_id',
        'currency_denomination_id',
        'direction',
        'quantity',
        'amount',
        'movement_type',
        'reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            McTransaction::class,
            'transaction_id'
        );
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(
            McTransactionPayment::class,
            'payment_id'
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

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(
            CashInventoryMovement::class,
            'cash_movement_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}