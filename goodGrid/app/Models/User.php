<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'profile_photo',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_SHOPPER = 'shopper';
    public const ROLE_MERCHANT = 'merchant';
    public const ROLE_ADMIN = 'admin';

    public static function roles(): array
    {
        return [
            self::ROLE_CUSTOMER,
            self::ROLE_SHOPPER,
            self::ROLE_MERCHANT,
            self::ROLE_ADMIN,
        ];
    }

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isShopper(): bool
    {
        return $this->role === self::ROLE_SHOPPER;
    }

    public function isMerchant(): bool
    {
        return $this->role === self::ROLE_MERCHANT;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function dietaryPreferences()
    {
        return $this->hasOne(DietaryPreference::class);
    }

    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    public function shopper()
    {
        return $this->hasOne(Shopper::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteShoppers()
    {
        return $this->morphedByMany(Shopper::class, 'favoritable', 'favorites');
    }

    public function favoriteMerchants()
    {
        return $this->morphedByMany(Merchant::class, 'favoritable', 'favorites');
    }
}
