<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    public function definition(): array
    {
        $businessTypes = [
            'Fresh Mart', 'Green Grocers', 'Organic Oasis', 'Local Harvest', 
            'Farm to Table', 'Community Market', 'Healthy Haven', 'Nature\'s Basket',
        ];

        return [
            'user_id' => User::factory()->state(['role' => 'merchant']),
            'business_name' => $this->faker->randomElement($businessTypes) . ' ' . $this->faker->city(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(3),
            'logo' => null,
            'cover_image' => null,
            'street_address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'zip_code' => $this->faker->postcode(),
            'latitude' => $this->faker->latitude(39.0, 40.0),
            'longitude' => $this->faker->longitude(-85.0, -84.0),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'hours' => [
                'monday' => ['open' => '08:00', 'close' => '20:00'],
                'tuesday' => ['open' => '08:00', 'close' => '20:00'],
                'wednesday' => ['open' => '08:00', 'close' => '20:00'],
                'thursday' => ['open' => '08:00', 'close' => '20:00'],
                'friday' => ['open' => '08:00', 'close' => '21:00'],
                'saturday' => ['open' => '09:00', 'close' => '21:00'],
                'sunday' => ['open' => '09:00', 'close' => '19:00'],
            ],
            'delivery_radius' => $this->faker->numberBetween(5, 20),
            'delivery_fee' => $this->faker->randomFloat(2, 2.99, 9.99),
            'minimum_order' => $this->faker->randomFloat(2, 15, 50),
            'status' => $this->faker->randomElement(['approved', 'approved', 'approved', 'pending']),
            'bank_account_last_four' => $this->faker->numerify('####'),
            'commission_rate' => 15.00,
        ];
    }
}
