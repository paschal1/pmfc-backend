<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnalyticsDashboardTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;

    public function test_example(): void
    {
        $this->assertTrue(true);
    }

    public function test_sales_reports()
    {
        $salesData = [
            ['month' => 'January', 'total' => 1000],
            ['month' => 'February', 'total' => 1500],
        ];

        $response = $this->get('/api/analytics/sales-reports');

        $response->assertStatus(200);
        $response->assertJson(['data' => $salesData]);
    }

    public function test_user_activity_statistics()
    {
        $activityData = [
            'active_users' => 50,
            'inactive_users' => 10,
        ];

        $response = $this->get('/api/analytics/user-activity');

        $response->assertStatus(200);
        $response->assertJson(['data' => $activityData]);
    }
}
