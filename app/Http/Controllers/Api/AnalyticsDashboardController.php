<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utility\SalesReportService;
use App\Utility\UserActivityService;

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

public function dashboardSummary()
{
    // Total revenue: sum of all paid orders
    $totalRevenue = \App\Models\Order::where('payment_status', 'Paid')->sum('total_price');

    // Total orders
    $totalOrders = \App\Models\Order::count();

    // Total products
    $totalProducts = \App\Models\Product::count();

    // Total customers
    $totalCustomers = \App\Models\User::count();

    // Total categories count
    $totalCategories = \App\Models\Category::count();

    // Get all categories with name and image
    $categories = \App\Models\Category::select('id', 'name', 'image')->get();

    // Best-selling products (top 5 based on order items count)
    $bestSellingProducts = \App\Models\Product::with(['category', 'orderItems'])
        ->withCount('orderItems')
        ->orderByDesc('order_items_count')
        ->take(5)
        ->get()
        ->map(function ($product) {
            $amount = $product->price * $product->order_items_count;
            return [
                'id' => $product->id,
                'image' => $product->image, // assuming 'image' column exists
                'name' => $product->name,
                'price' => $product->price,
                'orders' => $product->order_items_count,
                'stock' => $product->stock, // assuming 'stock' column exists
                'amount' => $amount,
                'date' => $product->updated_at->toDateTimeString(),
                'category' => $product->category ? $product->category->name : null,
            ];
        });

    // Recent orders (latest 5) with product details
    $recentOrders = \App\Models\Order::with('orderItems.product')
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($order) {
            return $order->orderItems->map(function ($item) use ($order) {
                $product = $item->product;
                $amount = $product->price * $item->quantity;
                return [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'image' => $product->image, // assuming 'image' column exists
                    'name' => $product->name,
                    'price' => $product->price,
                    'orders' => $item->quantity,
                    'stock' => $product->stock, // assuming 'stock' column exists
                    'amount' => $amount,
                    'date' => $order->created_at->toDateTimeString(),
                ];
            });
        })
        ->flatten(1);

    return response()->json([
        'total_revenue' => $totalRevenue,
        'total_orders' => $totalOrders,
        'total_products' => $totalProducts,
        'total_customers' => $totalCustomers,
        'total_categories' => $totalCategories,
        'categories' => $categories,
        'best_selling_products' => $bestSellingProducts,
        'recent_orders' => $recentOrders,
    ]);
}


}


