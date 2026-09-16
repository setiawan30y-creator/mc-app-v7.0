<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McTransactionPayment extends Model
{
    use HasFactory;

    protected $table = 'mc_transaction_payments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'transaction_id',
        'settlement_id',
        'payment_method',
        'amount',
        'currency_id',
        'bank_account_id',
        'bank_mutation_id',
        'transfer_reference',
        'transfer_external_id',
        'payer_name',
        'payment_status',
        'paid_at',
        'confirmed_at',
        'confirmed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(
            McTransaction::class,
            'transaction_id'
        );
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(
            McTransactionSettlement::class,
            'settlement_id'
        );
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class,
            'currency_id'
        );
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(
            BankAccount::class,
            'bank_account_id'
        );
    }

    public function bankMutation(): BelongsTo
    {
        return $this->belongsTo(
            BankMutation::class,
            'bank_mutation_id'
        );
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'confirmed_by'
        );
    }
}