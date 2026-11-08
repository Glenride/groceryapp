<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'shopper_id',
        'status',
        'offered_at',
        'accepted_at',
        'shopping_started_at',
        'in_transit_at',
        'delivered_at',
        'estimated_pay',
        'base_pay',
        'distance_pay',
        'time_pay',
        'estimated_tip',
        'bonus',
        'total_distance',
        'estimated_time',
        'route_data',
    ];

    protected $casts = [
        'offered_at' => 'datetime',
        'accepted_at' => 'datetime',
        'shopping_started_at' => 'datetime',
        'in_transit_at' => 'datetime',
        'delivered_at' => 'datetime',
        'estimated_pay' => 'decimal:2',
        'base_pay' => 'decimal:2',
        'distance_pay' => 'decimal:2',
        'time_pay' => 'decimal:2',
        'estimated_tip' => 'decimal:2',
        'bonus' => 'decimal:2',
        'total_distance' => 'decimal:2',
        'estimated_time' => 'integer',
        'route_data' => 'json',
    ];

    public function shopper(): BelongsTo
    {
        return $this->belongsTo(Shopper::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function getTotalPayAttribute(): float
    {
        return $this->base_pay + $this->distance_pay + $this->time_pay + $this->bonus;
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['accepted', 'shopping', 'in_transit']);
    }

    public function canAccept(): bool
    {
        return $this->status === 'offered';
    }
}
