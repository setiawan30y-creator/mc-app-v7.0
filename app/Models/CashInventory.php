<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashInventory extends Model
{
    use HasFactory;

    protected $table = 'cash_inventory';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'currency_id',
        'currency_variant_id',
        'currency_denomination_id',
        'quantity',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'total_amount' => 'decimal:2',
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

    public function movements(): HasMany
    {
        return $this->hasMany(
            CashInventoryMovement::class,
            'inventory_id'
        );
    }
}