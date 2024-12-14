<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // Import the Category model

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use the factory to generate 20 sample categories
        Category::factory()->count(20)->create();
    }
}
