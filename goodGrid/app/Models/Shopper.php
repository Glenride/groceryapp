<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shopper extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_make',
        'vehicle_model',
        'vehicle_year',
        'license_plate',
        'driver_license_number',
        'driver_license_state',
        'insurance_attestation',
        'background_check_status',
        'background_check_completed_at',
        'status',
        'rating',
        'total_batches',
        'last_batch_at',
        'payout_method',
        'payout_account_last_four',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'total_batches' => 'integer',
        'last_batch_at' => 'datetime',
        'background_check_completed_at' => 'datetime',
        'insurance_attestation' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(Earning::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->rating, 1);
    }
}
