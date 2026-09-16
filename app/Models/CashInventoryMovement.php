<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashInventoryMovement extends Model
{
    use HasFactory;

    protected $table = 'cash_inventory_movements';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'inventory_id',
        'transaction_id',
        'cash_movement_id',
        'direction',
        'quantity',
        'amount',
        'balance_quantity',
        'balance_amount',
        'movement_type',
        'reference',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'amount' => 'decimal:2',
            'balance_quantity' => 'decimal:4',
            'balance_amount' => 'decimal:2',
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

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(
            CashInventory::class,
            'inventory_id'
        );
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            McTransaction::class,
            'transaction_id'
        );
    }

    public function cashMovement(): BelongsTo
    {
        return $this->belongsTo(
            CashMovement::class,
            'cash_movement_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function closingAdjustment(): HasOne
    {
        return $this->hasOne(
            CashClosingDetail::class,
            'adjustment_movement_id'
        );
    }
}