<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users with different roles
        User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@goodgrid.test',
            'role' => 'customer',
        ]);

        User::factory()->create([
            'name' => 'Shopper User',
            'email' => 'shopper@goodgrid.test',
            'role' => 'shopper',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@goodgrid.test',
            'role' => 'admin',
        ]);

        // Seed Good Grid marketplace data
        $this->call([
            GoodGridSeeder::class,
        ]);
    }
}
