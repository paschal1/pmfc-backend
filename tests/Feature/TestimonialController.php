<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Testimonial;

class TestimonialController extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_user_can_view_approved_testimonials()
    {
        Testimonial::factory()->count(3)->create(['is_approved' => true]);

        $response = $this->get('/testimonials');
        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_can_submit_testimonial()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/testimonials', [
            'message' => 'Great service!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('testimonials', ['user_id' => $user->id, 'message' => 'Great service!']);
    }
}
