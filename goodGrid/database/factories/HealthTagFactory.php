<?php

namespace Database\Factories;

use App\Models\HealthTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealthTagFactory extends Factory
{
    protected $model = HealthTag::class;

    public function definition(): array
    {
        $tags = [
            ['name' => 'Organic', 'slug' => 'organic', 'color' => '#22c55e'],
            ['name' => 'Low Sodium', 'slug' => 'low-sodium', 'color' => '#3b82f6'],
            ['name' => 'High Fiber', 'slug' => 'high-fiber', 'color' => '#8b5cf6'],
            ['name' => 'Heart Healthy', 'slug' => 'heart-healthy', 'color' => '#ef4444'],
            ['name' => 'Plant Based', 'slug' => 'plant-based', 'color' => '#10b981'],
            ['name' => 'No Added Sugar', 'slug' => 'no-added-sugar', 'color' => '#f59e0b'],
            ['name' => 'Gluten Free', 'slug' => 'gluten-free', 'color' => '#6366f1'],
            ['name' => 'High Protein', 'slug' => 'high-protein', 'color' => '#ec4899'],
            ['name' => 'Non-GMO', 'slug' => 'non-gmo', 'color' => '#14b8a6'],
            ['name' => 'Local', 'slug' => 'local', 'color' => '#84cc16'],
        ];

        $tag = $this->faker->randomElement($tags);

        return [
            'name' => $tag['name'],
            'slug' => $tag['slug'],
            'description' => $this->faker->sentence(),
            'icon' => null,
            'color' => $tag['color'],
            'is_active' => true,
        ];
    }
}
