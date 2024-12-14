<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnalyticsDashboardFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   
    public function test_can_view_sales_reports()
    {
        $response = $this->get('/api/analytics/sales-reports');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['month', 'total']]]);
    }

    public function test_can_view_user_activity()
    {
        $response = $this->get('/api/analytics/user-activity');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => ['active_users', 'inactive_users']]);
    }
}
