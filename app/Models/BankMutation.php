<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankMutation extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'bank_mutations';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'bank_account_id',
        'transaction_date',
        'value_date',
        'reference',
        'description',
        'debit',
        'credit',
        'balance',
        'external_id',
        'source',
        'reconciliation_status',
        'matched_transaction_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'value_date' => 'date',
            'debit' => 'decimal:2',
            'credit' => 'decimal:2',
            'balance' => 'decimal:2',
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

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function getNetAmountAttribute(): string
    {
        return bcsub(
            (string) $this->credit,
            (string) $this->debit,
            2
        );
    }

    public function getIsReconciledAttribute(): bool
    {
        return in_array(
            $this->reconciliation_status,
            ['matched', 'manual'],
            true
        );
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForBranch($query, string $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeUnmatched($query)
    {
        return $query->where('reconciliation_status', 'unmatched');
    }

    public function scopeMatched($query)
    {
        return $query->where('reconciliation_status', 'matched');
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
