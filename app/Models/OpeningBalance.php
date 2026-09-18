<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalance extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'opening_balances';

    protected $fillable = [
        'tenant_id', 'branch_id', 'balance_date', 'balance_type',
        'currency_id', 'bank_account_id', 'currency_variant_id',
        'currency_denomination_id', 'quantity', 'rate', 'amount_rp',
        'notes', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'balance_date' => 'date',
            'quantity' => 'decimal:4',
            'rate' => 'decimal:6',
            'amount_rp' => 'decimal:2',
        ];
    }

    public function currency(): BelongsTo { return $this->belongsTo(Currency::class); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function variant(): BelongsTo { return $this->belongsTo(CurrencyVariant::class, 'currency_variant_id'); }
    public function denomination(): BelongsTo { return $this->belongsTo(CurrencyDenomination::class, 'currency_denomination_id'); }
}
