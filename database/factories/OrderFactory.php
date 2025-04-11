<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
    

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
            'tracking_number' => 'TRK' . strtoupper(Str::random(10)),
            'payment_status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'Unpaid']),
            'status' => $this->faker->randomElement([ 'order_processing', 
            'pre_production', 
            'in_production', 
            'shipped', 
            'delivered',
            'canceled']),
            'shipping_address' => $this->faker->address(), // Generate a random shipping address
        ];
        
    }
}
