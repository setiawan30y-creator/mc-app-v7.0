<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForexStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'currency_variant_id',
        'currency_denomination_id',
        'quantity',
        'stock_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:6',
        'stock_date' => 'date',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(CurrencyVariant::class, 'currency_variant_id');
    }

    public function denomination(): BelongsTo
    {
        return $this->belongsTo(CurrencyDenomination::class, 'currency_denomination_id');
    }
}
