<?php

namespace Database\Factories;

use App\Models\Shopper;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShopperFactory extends Factory
{
    protected $model = Shopper::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'shopper']),
            'vehicle_type' => $this->faker->randomElement(['car', 'bike', 'scooter']),
            'vehicle_make' => $this->faker->randomElement(['Toyota', 'Honda', 'Ford', 'Chevrolet']),
            'vehicle_model' => $this->faker->word(),
            'vehicle_year' => $this->faker->year(),
            'license_plate' => $this->faker->regexify('[A-Z]{3}-[0-9]{3}'),
            'driver_license_number' => $this->faker->regexify('[A-Z][0-9]{8}'),
            'driver_license_state' => $this->faker->stateAbbr(),
            'insurance_attestation' => true,
            'background_check_status' => 'approved',
            'background_check_completed_at' => now(),
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'pending']),
            'rating' => $this->faker->randomFloat(1, 4.0, 5.0),
            'total_batches' => $this->faker->numberBetween(0, 500),
            'payout_method' => 'bank_transfer',
            'payout_account_last_four' => $this->faker->numerify('####'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'background_check_status' => 'approved',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'background_check_status' => 'pending',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
