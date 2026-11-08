<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HealthTag;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Database\Seeder;

class GoodGridSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            'Produce' => ['Fresh Fruits', 'Fresh Vegetables', 'Herbs', 'Organic Produce'],
            'Dairy & Eggs' => ['Milk', 'Cheese', 'Yogurt', 'Eggs', 'Butter'],
            'Meat & Seafood' => ['Beef', 'Chicken', 'Pork', 'Fish', 'Shellfish'],
            'Bakery' => ['Bread', 'Pastries', 'Bagels', 'Tortillas'],
            'Pantry' => ['Canned Goods', 'Pasta & Rice', 'Cereals', 'Sauces'],
            'Frozen' => ['Frozen Vegetables', 'Frozen Meals', 'Ice Cream'],
            'Beverages' => ['Water', 'Juice', 'Coffee', 'Tea', 'Soda'],
            'Snacks' => ['Chips', 'Nuts', 'Crackers', 'Candy'],
        ];

        foreach ($categories as $main => $subcategories) {
            $parent = Category::factory()->create(['name' => $main]);
            foreach ($subcategories as $sub) {
                Category::factory()->create([
                    'name' => $sub,
                    'parent_id' => $parent->id,
                ]);
            }
        }

        // Create health tags
        $healthTags = [
            ['name' => 'Organic', 'slug' => 'organic', 'color' => '#22c55e', 'description' => 'Certified organic products'],
            ['name' => 'Low Sodium', 'slug' => 'low-sodium', 'color' => '#3b82f6', 'description' => 'Lower in sodium than typical alternatives'],
            ['name' => 'High Fiber', 'slug' => 'high-fiber', 'color' => '#8b5cf6', 'description' => 'Good source of dietary fiber'],
            ['name' => 'Heart Healthy', 'slug' => 'heart-healthy', 'color' => '#ef4444', 'description' => 'Supports heart health'],
            ['name' => 'Plant Based', 'slug' => 'plant-based', 'color' => '#10b981', 'description' => 'Made from plants'],
            ['name' => 'No Added Sugar', 'slug' => 'no-added-sugar', 'color' => '#f59e0b', 'description' => 'No sugar added'],
            ['name' => 'Gluten Free', 'slug' => 'gluten-free', 'color' => '#6366f1', 'description' => 'Gluten-free certified'],
            ['name' => 'High Protein', 'slug' => 'high-protein', 'color' => '#ec4899', 'description' => 'Good source of protein'],
            ['name' => 'Non-GMO', 'slug' => 'non-gmo', 'color' => '#14b8a6', 'description' => 'Non-GMO verified'],
            ['name' => 'Local', 'slug' => 'local', 'color' => '#84cc16', 'description' => 'Locally sourced'],
        ];

        foreach ($healthTags as $tag) {
            HealthTag::factory()->create($tag);
        }

        // Create merchants with products
        $merchants = Merchant::factory()->count(5)->create();
        
        foreach ($merchants as $merchant) {
            $categories = Category::whereNotNull('parent_id')->inRandomOrder()->take(4)->get();
            
            foreach ($categories as $category) {
                Product::factory()
                    ->count(10)
                    ->create([
                        'merchant_id' => $merchant->id,
                        'category_id' => $category->id,
                    ])
                    ->each(function ($product) {
                        $product->healthTags()->attach(
                            HealthTag::inRandomOrder()->take(rand(0, 3))->pluck('id')
                        );
                    });
            }
        }

        $this->command->info('Good Grid seeding complete!');
        $this->command->info("Created {$merchants->count()} merchants with products and health tags.");
    }
}
