<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'unit',
        'size',
        'sku',
        'barcode',
        'image',
        'gallery',
        'stock_quantity',
        'track_stock',
        'is_available',
        'nutrition_facts',
        'allergens',
        'ingredients',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'gallery' => 'json',
        'nutrition_facts' => 'json',
        'allergens' => 'json',
        'ingredients' => 'json',
        'track_stock' => 'boolean',
        'is_available' => 'boolean',
        'stock_quantity' => 'integer',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function healthTags(): BelongsToMany
    {
        return $this->belongsToMany(HealthTag::class);
    }

    public function goodSwaps(): HasMany
    {
        return $this->hasMany(GoodSwap::class, 'product_id');
    }

    public function suggestedSwaps(): HasMany
    {
        return $this->hasMany(GoodSwap::class, 'suggested_product_id');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getCurrentPriceAttribute(): float
    {
        return $this->sale_price ?? $this->price;
    }

    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }
}
