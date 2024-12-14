<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderManagementFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_can_view_orders()
    {
        $order = Order::factory()->create();

        $response = $this->get('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $order->id]);
    }

    public function test_can_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->put("/api/orders/{$order->id}", ['status' => 'completed']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
    }
    public function test_can_issue_refund()
    {
        $order = Order::factory()->create(['status' => 'completed', 'refunded' => false]);

        $response = $this->put("/api/orders/{$order->id}/refund");

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'refunded' => true]);
    }
}
