<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Rating;

class RatingController extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_can_view_ratings()
    {
        $user = User::factory()->create();
        Rating::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/ratings');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_can_add_or_update_rating()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user);
        $response = $this->post('/ratings', [
            'product_id' => $product->id,
            'rating' => 5,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ratings', ['product_id' => $product->id, 'rating' => 5]);
    }
}
