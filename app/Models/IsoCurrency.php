<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IsoCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'numeric_code',
        'name',
        'minor_unit',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'minor_unit' => 'integer',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Negara / entitas yang menggunakan currency ini.
     */
    public function entities(): HasMany
    {
        return $this->hasMany(IsoCurrencyEntity::class);
    }

    /**
     * Scope hanya currency ISO yang aktif/current.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Cari berdasarkan kode ISO, numeric code, atau nama.
     */
    public function scopeSearch($query, ?string $search)
    {
        if ($search === null || trim($search) === '') {
            return $query;
        }

        $search = trim($search);

        return $query->where(function ($query) use ($search) {
            $query
                ->where('code', 'like', '%' . $search . '%')
                ->orWhere('numeric_code', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%')
                ->orWhereHas('entities', function ($entityQuery) use ($search) {
                    $entityQuery
                        ->where('country_code', 'like', '%' . $search . '%')
                        ->orWhere('entity_name', 'like', '%' . $search . '%');
                });
        });
    }

    /**
     * Cari currency berdasarkan ISO alphabetic code.
     */
    public static function findByCode(?string $code): ?self
    {
        if (!$code) {
            return null;
        }

        return static::query()
            ->where('code', strtoupper(trim($code)))
            ->first();
    }

    /**
     * Relasi ke Master Currency operasional berdasarkan ISO code.
     *
     * Tidak membuat foreign key karena currencies adalah
     * master operasional yang terpisah dari katalog ISO.
     */
    public function masterCurrency(): HasOne
    {
        return $this->hasOne(Currency::class, 'code', 'code');
    }
}