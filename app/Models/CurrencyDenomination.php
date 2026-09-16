<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrencyDenomination extends Model
{
    use HasFactory;

    public const TYPE_BANKNOTE = 'banknote';

    public const TYPE_COIN = 'coin';

    public const TYPES = [
        self::TYPE_BANKNOTE,
        self::TYPE_COIN,
    ];

    protected $fillable = [
        'currency_variant_id',
        'value',
        'type',
        'label',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'value' => 'decimal:6',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(
            CurrencyVariant::class,
            'currency_variant_id'
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('value')
            ->orderBy('type');
    }

    public function scopeBanknotes($query)
    {
        return $query->where('type', self::TYPE_BANKNOTE);
    }

    public function scopeCoins($query)
    {
        return $query->where('type', self::TYPE_COIN);
    }

    public function isBanknote(): bool
    {
        return $this->type === self::TYPE_BANKNOTE;
    }

    public function isCoin(): bool
    {
        return $this->type === self::TYPE_COIN;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_BANKNOTE => 'Banknote',
            self::TYPE_COIN => 'Coin',
            default => ucfirst((string) $this->type),
        };
    }

    public function getDisplayLabelAttribute(): string
    {
        if ($this->label) {
            return $this->label;
        }

        return rtrim(
            rtrim(number_format((float) $this->value, 6, '.', ''), '0'),
            '.'
        );
    }
}