<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class WishlistControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_can_view_wishlist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/wishlist');
        $response->assertStatus(200);
    }

    public function test_user_can_add_item_to_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/wishlist', ['product_id' => $product->id]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'product_id' => $product->id]);
    }

    public function test_user_can_remove_item_from_wishlist()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $wishlistItem = $user->wishlist()->create(['product_id' => $product->id]);

        $this->actingAs($user);
        $response = $this->delete("/wishlist/{$wishlistItem->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('wishlists', ['id' => $wishlistItem->id]);
    }
}
