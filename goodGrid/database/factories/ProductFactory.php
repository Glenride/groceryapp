<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $productNames = [
            'Organic Bananas', 'Fresh Spinach', 'Whole Milk', 'Free-Range Eggs', 
            'Sourdough Bread', 'Ground Coffee', 'Olive Oil', 'Greek Yogurt',
            'Chicken Breast', 'Atlantic Salmon', 'Brown Rice', 'Quinoa',
            'Almond Butter', 'Honey', 'Avocados', 'Sweet Potatoes',
            'Bell Peppers', 'Cherry Tomatoes', 'Cucumber', 'Carrots',
        ];

        $name = $this->faker->randomElement($productNames);
        $price = $this->faker->randomFloat(2, 2, 50);

        return [
            'merchant_id' => Merchant::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $this->faker->slug(),
            'description' => $this->faker->paragraph(),
            'price' => $price,
            'sale_price' => $this->faker->boolean(20) ? $price * 0.8 : null,
            'unit' => $this->faker->randomElement(['lb', 'oz', 'each', 'pack', 'bunch']),
            'size' => $this->faker->randomElement(['1 lb', '16 oz', '12 pack', '1 bunch']),
            'sku' => strtoupper($this->faker->unique()->bothify('???-####')),
            'barcode' => $this->faker->unique()->ean13(),
            'image' => null,
            'gallery' => null,
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'track_stock' => true,
            'is_available' => $this->faker->boolean(90),
            'nutrition_facts' => [
                'calories' => $this->faker->numberBetween(50, 500),
                'protein' => $this->faker->numberBetween(0, 30),
                'carbs' => $this->faker->numberBetween(0, 50),
                'fat' => $this->faker->numberBetween(0, 30),
                'fiber' => $this->faker->numberBetween(0, 10),
                'sodium' => $this->faker->numberBetween(0, 500),
            ],
            'allergens' => [],
            'ingredients' => [],
            'sort_order' => 0,
        ];
    }
}
