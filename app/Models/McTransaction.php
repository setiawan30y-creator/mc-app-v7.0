<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McTransaction extends Model
{
    use HasFactory;

    protected $table = 'mc_transactions';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'transaction_no',
        'customer_id',
        'transaction_date',
        'status',
        'settlement_status',
        'source_of_funds',
        'transaction_purpose',
        'pickup_same_as_customer',
        'pickup_party_type',
        'pickup_party_id',
        'pickup_name',
        'pickup_phone',
        'pickup_identity_type',
        'pickup_identity_number',
        'pickup_relationship',
        'pickup_status',
        'pickup_at',
        'handed_over_by',
        'wa_template_id',
        'wa_template_version',
        'wa_status',
        'wa_sent_at',
        'wa_message_id',
        'wa_error',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'pickup_same_as_customer' => 'boolean',
            'pickup_at' => 'datetime',
            'wa_sent_at' => 'datetime',
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(McTransactionItem::class, 'transaction_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(McTransactionSettlement::class, 'transaction_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(McTransactionPayment::class, 'transaction_id');
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'transaction_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function fulfilledBooking(): ?Booking
    {
        return Booking::where('fulfilled_transaction_id', $this->id)->first();
    }
}