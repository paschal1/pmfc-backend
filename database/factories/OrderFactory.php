<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Automatically creates a user if none exists
            'product_id' => Product::factory(), // Automatically creates a product if none exists
            'quantity' => $this->faker->numberBetween(1, 10), // Random quantity
            'total_price' => function (array $attributes) {
                $product = Product::find($attributes['product_id']);
                return $product ? $product->price * $attributes['quantity'] : 0;
            },
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'shipping_address' => $this->faker->address(), // Generate a random shipping address
        ];
        
    }
}
