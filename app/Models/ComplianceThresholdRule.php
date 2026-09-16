<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceThresholdRule extends Model
{
    protected $table = 'compliance_threshold_rules';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'code',
        'basis',
        'limit_amount',
        'period',
        'effective_from',
        'effective_until',
        'is_active',
        'regulation_reference',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(
            McTransactionItem::class,
            'compliance_threshold_rule_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isEffectiveAt($date): bool
    {
        $date = $date instanceof \Carbon\Carbon
            ? $date
            : \Carbon\Carbon::parse($date);

        if (!$this->is_active) {
            return false;
        }

        if ($date->lt($this->effective_from)) {
            return false;
        }

        if (
            $this->effective_until !== null &&
            $date->gt($this->effective_until)
        ) {
            return false;
        }

        return true;
    }

    public function isUsdBasis(): bool
    {
        return $this->basis === 'usd';
    }

    public function isIdrBasis(): bool
    {
        return $this->basis === 'idr';
    }

    public function isMonthly(): bool
    {
        return $this->period === 'monthly';
    }

    public function isDaily(): bool
    {
        return $this->period === 'daily';
    }

    public function isYearly(): bool
    {
        return $this->period === 'yearly';
    }
}