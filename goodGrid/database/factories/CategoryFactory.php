<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $categories = [
            'Produce', 'Dairy & Eggs', 'Meat & Seafood', 'Bakery',
            'Pantry', 'Frozen', 'Beverages', 'Snacks',
            'Health & Wellness', 'Household', 'Baby', 'Pet',
        ];

        $name = $this->faker->randomElement($categories);

        return [
            'name' => $name,
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(),
            'icon' => null,
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
