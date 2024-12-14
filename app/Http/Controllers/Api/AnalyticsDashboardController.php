<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SalesReportService;
use App\Services\UserActivityService;

class AnalyticsDashboardController extends Controller
{
   
    protected $salesReportService;
    protected $userActivityService;

    public function __construct(SalesReportService $salesReportService, UserActivityService $userActivityService)
    {
        $this->salesReportService = $salesReportService;
        $this->userActivityService = $userActivityService;
    }

    /**
     * Display sales reports and trends.
     */
    public function salesReports()
    {
        try {
            $salesData = $this->salesReportService->getSalesReports();
            return response()->json(['data' => $salesData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch sales reports.'], 500);
        }
    }

    /**
     * Display user activity statistics.
     */
    public function userActivity()
    {
        try {
            $activityData = $this->userActivityService->getUserActivityStats();
            return response()->json(['data' => $activityData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch user activity statistics.'], 500);
        }
    }

    /**
     * Display website performance statistics.
     */
    public function websitePerformance()
    {
        try {
            // Add logic for fetching website performance data.
            $performanceData = [
                'page_load_time' => '1.5s',
                'bounce_rate' => '47%',
                'conversion_rate' => '3.2%',
            ];
            return response()->json(['data' => $performanceData], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to fetch website performance statistics.'], 500);
        }
    }
}


