<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IsoCurrencyEntity extends Model
{
    use HasFactory;

    protected $fillable = [
        'iso_currency_id',
        'country_code',
        'entity_name',
    ];

    /**
     * Currency ISO induk.
     */
    public function isoCurrency(): BelongsTo
    {
        return $this->belongsTo(IsoCurrency::class);
    }

    /**
     * Normalisasi country/entity code.
     */
    public function getCountryCodeAttribute($value): string
    {
        return strtoupper((string) $value);
    }
}