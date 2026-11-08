<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_price',
        'quantity',
        'unit',
        'special_instructions',
        'substitution_status',
        'substituted_product_id',
        'substituted_product_name',
        'substituted_product_price',
        'total',
        'is_found',
        'shopper_notes',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'substituted_product_price' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
        'is_found' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function substitutedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'substituted_product_id');
    }

    public function wasSubstituted(): bool
    {
        return $this->substitution_status === 'approved' && $this->substituted_product_id !== null;
    }
}
