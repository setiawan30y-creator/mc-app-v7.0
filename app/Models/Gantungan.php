<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Gantungan extends Model
{
    protected $table = 'gantungans';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'tenant_id', 'branch_id', 'gantungan_no', 'business_date',
        'category', 'title', 'description', 'amount', 'settled_amount',
        'outstanding_amount', 'counterparty_name', 'reference', 'status',
        'due_date', 'created_by', 'updated_by', 'settled_by', 'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'business_date' => 'date',
            'due_date' => 'date',
            'settled_at' => 'datetime',
            'amount' => 'decimal:2',
            'settled_amount' => 'decimal:2',
            'outstanding_amount' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->id ??= (string) Str::ulid();
            if ($model->outstanding_amount === null) {
                $model->outstanding_amount = $model->amount ?? 0;
            }
            $model->status ??= 'open';
        });
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(GantunganSettlement::class, 'gantungan_id');
    }

    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
    public function settledBy(): BelongsTo { return $this->belongsTo(User::class, 'settled_by'); }

    public function isOpen(): bool { return in_array($this->status, ['open', 'partial'], true); }
    public function isSettled(): bool { return $this->status === 'settled'; }
}
