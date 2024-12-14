<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;


class CartControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_can_view_cart_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/cart');
        $response->assertStatus(200);
    }

    public function test_user_can_add_item_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['product_id' => $product->id]);
    }

    public function test_user_can_remove_item_from_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $cartItem = $user->cartItems()->create(['product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($user);
        $response = $this->delete("/cart/{$cartItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
