<?php

namespace Tests\Unit;
use App\Models\Order;
use App\Models\User;

use PHPUnit\Framework\TestCase;

class OrderControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function test_user_can_view_orders()
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->get('/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_user_can_update_order_status()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'Pending']);

        $this->actingAs($user);
        $response = $this->patch("/orders/{$order->id}/status", ['status' => 'Completed']);

        $response->assertStatus(200);
        $this->assertEquals('Completed', $order->fresh()->status);
    }

    public function test_user_can_request_refund()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'Paid']);

        $this->actingAs($user);
        $response = $this->patch("/orders/{$order->id}/refund");

        $response->assertStatus(200);
        $this->assertEquals('Refunded', $order->fresh()->payment_status);
    }
}
