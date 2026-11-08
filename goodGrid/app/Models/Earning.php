<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopper_id',
        'batch_id',
        'type',
        'amount',
        'description',
        'status',
        'available_at',
        'paid_at',
        'payout_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'available_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function shopper(): BelongsTo
    {
        return $this->belongsTo(Shopper::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
