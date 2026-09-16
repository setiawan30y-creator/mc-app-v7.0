<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_local',
        'numeric_code',
        'flag',
        'decimal_digits',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'decimal_digits' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Variant mata uang.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(CurrencyVariant::class);
    }

    /**
     * Rekening bank yang menggunakan mata uang ini.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Hanya mata uang yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Urutkan sesuai konfigurasi master.
     */
    public function scopeOrdered($query)
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('code');
    }

    /**
     * Format label mata uang untuk UI.
     */
    public function getDisplayLabelAttribute(): string
    {
        return trim(
            ($this->flag ? $this->flag . ' ' : '') .
            $this->code . ' — ' .
            $this->name
        );
    }
}