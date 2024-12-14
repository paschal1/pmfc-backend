<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;
use App\Models\Category;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'stock' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->numberBetween(1000, 10000),
            'image' => $this->faker->imageUrl(640, 480, 'products', true),
            'category_id' => Category::factory(), // Generate a category for each product
        ];
    }
}
