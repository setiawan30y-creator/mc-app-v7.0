<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClosingBank extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'cash_closing_banks';

    protected $fillable = [
        'cash_closing_id',
        'bank_account_id',
        'system_amount',
        'physical_amount',
        'difference_amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'system_amount' => 'decimal:2',
            'physical_amount' => 'decimal:2',
            'difference_amount' => 'decimal:2',
        ];
    }

    public function closing(): BelongsTo
    {
        return $this->belongsTo(CashClosing::class, 'cash_closing_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function syncDifference(): self
    {
        $this->difference_amount = bcsub(
            (string) ($this->physical_amount ?? '0'),
            (string) ($this->system_amount ?? '0'),
            2
        );

        return $this;
    }
}
