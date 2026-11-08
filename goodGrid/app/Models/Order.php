<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'merchant_id',
        'batch_id',
        'address_id',
        'status',
        'delivery_instructions',
        'scheduled_delivery_time',
        'delivered_at',
        'delivery_photo',
        'substitution_preference',
        'subtotal',
        'tax',
        'delivery_fee',
        'service_fee',
        'tip',
        'discount',
        'total',
        'payment_method',
        'payment_intent_id',
        'payment_status',
        'customer_rating',
        'customer_comment',
        'rated_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tip' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'scheduled_delivery_time' => 'datetime',
        'delivered_at' => 'datetime',
        'rated_at' => 'datetime',
        'customer_rating' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'preparing']);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'GG-' . strtoupper(uniqid());
            }
        });
    }
}
