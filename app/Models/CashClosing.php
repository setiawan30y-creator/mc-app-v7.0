<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashClosing extends Model
{
    use HasFactory;

    protected $table = 'cash_closings';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','tenant_id','branch_id','business_date','shift','closing_type','closing_no','closing_date','status',
        'opening_cash_amount','expected_amount','physical_amount','difference_amount',
        'expected_cash_amount','physical_cash_amount','cash_difference_amount','hanging_amount',
        'bank_system_amount','bank_physical_amount','bank_difference_amount','notes',
        'prepared_by','approved_by','closed_by','approved_at','closed_at','rejected_at','rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'closing_date' => 'datetime',
            'opening_cash_amount' => 'decimal:2',
            'expected_amount' => 'decimal:2',
            'physical_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
            'expected_cash_amount' => 'decimal:2',
            'physical_cash_amount' => 'decimal:2',
            'cash_difference_amount' => 'decimal:2',
            'hanging_amount' => 'decimal:2',
            'bank_system_amount' => 'decimal:2',
            'bank_physical_amount' => 'decimal:2',
            'bank_difference_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'closed_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function preparedBy(): BelongsTo { return $this->belongsTo(User::class, 'prepared_by'); }
    public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function details(): HasMany { return $this->hasMany(CashClosingDetail::class, 'closing_id'); }
    public function bankDetails(): HasMany { return $this->hasMany(CashClosingBank::class, 'cash_closing_id'); }

    /**
     * Total system balance from per-account bank closing details.
     */
    public function getBankDetailsSystemAmountAttribute(): string
    {
        return (string) $this->bankDetails()->sum('system_amount');
    }

    /**
     * Total physical/statement balance from per-account bank closing details.
     */
    public function getBankDetailsPhysicalAmountAttribute(): string
    {
        return (string) $this->bankDetails()->sum('physical_amount');
    }

    /**
     * Total difference across all bank accounts.
     */
    public function getBankDetailsDifferenceAmountAttribute(): string
    {
        return (string) $this->bankDetails()->sum('difference_amount');
    }

    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isSubmitted(): bool { return $this->status === 'submitted'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isClosed(): bool { return $this->status === 'closed'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isBalanced(): bool { return bccomp((string) $this->difference_amount, '0', 2) === 0; }
}
