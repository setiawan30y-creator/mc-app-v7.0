<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'tenant_id',
        'branch_id',
        'booking_no',
        'customer_id',
        'booking_date',
        'expiry_at',
        'status',
        'deposit_amount',
        'deposit_currency_id',
        'notes',
        'created_by',
        'fulfilled_at',
        'fulfilled_transaction_id',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'datetime',
            'expiry_at' => 'datetime',
            'deposit_amount' => 'decimal:2',
            'fulfilled_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function depositCurrency(): BelongsTo
    {
        return $this->belongsTo(
            Currency::class,
            'deposit_currency_id'
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(
            BookingItem::class,
            'booking_id'
        );
    }

    public function fulfilledTransaction(): BelongsTo
    {
        return $this->belongsTo(
            McTransaction::class,
            'fulfilled_transaction_id'
        );
    }

    public function isActive(): bool
    {
        return in_array(
            $this->status,
            ['draft', 'confirmed', 'partially_paid']
        );
    }

    public function isFulfilled(): bool
    {
        return $this->status === 'fulfilled'
            && !empty($this->fulfilled_transaction_id);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->expiry_at !== null
            && $this->expiry_at->isPast()
            && $this->isActive();
    }
}