<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GantunganSettlement extends Model
{
    protected $table = 'gantungan_settlements';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'gantungan_id', 'amount', 'settled_at', 'method', 'reference', 'notes', 'created_by'
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'settled_at' => 'datetime'];
    }

    public function gantungan(): BelongsTo { return $this->belongsTo(Gantungan::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
