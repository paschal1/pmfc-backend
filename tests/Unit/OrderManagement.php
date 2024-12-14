<?php

namespace Tests\Unit;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

class OrderManagement extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_view_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $this->assertEquals('pending', $order->status);
    }

    public function test_update_order_status()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->update(['status' => 'completed']);

        $this->assertEquals('completed', $order->status);
    }

    public function test_issue_refund()
    {
        $order = Order::factory()->create(['status' => 'completed', 'refunded' => false]);

        $order->update(['refunded' => true]);

        $this->assertTrue($order->refunded);
    }
}
