<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'bank_accounts';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'bank_name',
        'bank_code',
        'account_name',
        'account_number',
        'currency_id',
        'opening_balance',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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
        return $this->belongsTo(Currency::class);
    }

    public function mutations(): HasMany
    {
        return $this->hasMany(BankMutation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Balance
    |--------------------------------------------------------------------------
    */

    /**
     * Saldo berdasarkan opening balance + seluruh mutasi.
     *
     * Credit menambah saldo.
     * Debit mengurangi saldo.
     */
    public function getCalculatedBalanceAttribute(): string
    {
        $totals = $this->mutations()
            ->selectRaw('
                COALESCE(SUM(credit), 0) as total_credit,
                COALESCE(SUM(debit), 0) as total_debit
            ')
            ->first();

        return bcadd(
            bcsub(
                (string) $this->opening_balance,
                (string) ($totals->total_debit ?? '0'),
                2
            ),
            (string) ($totals->total_credit ?? '0'),
            2
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

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForBranch($query, string $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}