<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RateSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'currency_id',
        'rate_source_id',
        'currency_variant_id',
        'currency_denomination_id',
        'effective_at',
        'reference_buy_rate',
        'reference_sell_rate',
        'buy_spread',
        'sell_spread',
        'buy_rate',
        'sell_rate',
        'source_url',
        'notes',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'effective_at' => 'datetime',

        'reference_buy_rate' => 'decimal:6',
        'reference_sell_rate' => 'decimal:6',

        'buy_spread' => 'decimal:6',
        'sell_spread' => 'decimal:6',

        'buy_rate' => 'decimal:6',
        'sell_rate' => 'decimal:6',

        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function rateSource(): BelongsTo
    {
        return $this->belongsTo(RateSource::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyVariant::class,
            'currency_variant_id'
        );
    }

    public function denomination(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyDenomination::class,
            'currency_denomination_id'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatestEffective($query)
    {
        return $query->orderByDesc('effective_at');
    }
}