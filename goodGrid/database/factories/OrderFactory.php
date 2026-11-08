<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 20, 200);
        $deliveryFee = $this->faker->randomFloat(2, 2.99, 9.99);
        $serviceFee = round($subtotal * 0.05, 2);
        $tax = round($subtotal * 0.08, 2);
        $total = $subtotal + $deliveryFee + $serviceFee + $tax;

        return [
            'customer_id' => User::factory()->state(['role' => 'customer']),
            'merchant_id' => Merchant::factory(),
            'address_id' => Address::factory(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'preparing', 'in_transit', 'delivered']),
            'delivery_instructions' => $this->faker->optional()->sentence(),
            'substitution_preference' => $this->faker->randomElement(['no_substitute', 'ask_first', 'use_best']),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'delivery_fee' => $deliveryFee,
            'service_fee' => $serviceFee,
            'tip' => $this->faker->randomFloat(2, 0, 10),
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'card',
            'payment_status' => $this->faker->randomElement(['pending', 'authorized', 'captured']),
            'delivered_at' => null,
        ];
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'delivered_at' => now(),
            'payment_status' => 'captured',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }
}
