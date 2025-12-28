<?php

namespace App\Utility;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportService
{
    /**
     * Fetch sales reports grouped by month.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSalesReports()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        
        // Check if Order has 'total_amount' or 'total_price' column
        $totalColumn = 'total_price'; // Change to 'total_amount' if that's your column name
        
        // Total sales (all time)
        $totalSales = Order::where('payment_status', 'Paid')->sum($totalColumn);
        
        // Today's sales
        $todaySales = Order::where('payment_status', 'Paid')
            ->whereDate('created_at', $today)
            ->sum($totalColumn);
        
        // This month's sales
        $thisMonthSales = Order::where('payment_status', 'Paid')
            ->whereBetween('created_at', [$thisMonth, now()])
            ->sum($totalColumn);
        
        // Last month's sales
        $lastMonthSales = Order::where('payment_status', 'Paid')
            ->whereBetween('created_at', [$lastMonth, $lastMonthEnd])
            ->sum($totalColumn);
        
        // Calculate growth percentage
        $monthlyGrowth = $lastMonthSales > 0 
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 1) 
            : 0;
        
        // Monthly breakdown for current year
        $monthlyBreakdown = Order::where('payment_status', 'Paid')
            ->selectRaw("MONTH(created_at) as month, MONTHNAME(created_at) as month_name, SUM({$totalColumn}) as total")
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month', 'month_name')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month_name,
                    'sales' => (float) $item->total,
                ];
            });
        
        // Top selling categories (if you have the relationships)
        try {
            $topCategories = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->where('orders.payment_status', 'Paid')
                ->select('categories.name', DB::raw('SUM(order_items.quantity * products.price) as total_sales'))
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_sales')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'sales' => (float) $item->total_sales,
                    ];
                });
        } catch (\Exception $e) {
            $topCategories = [];
        }
        
        return [
            'total_sales' => (float) $totalSales,
            'today_sales' => (float) $todaySales,
            'this_month_sales' => (float) $thisMonthSales,
            'last_month_sales' => (float) $lastMonthSales,
            'monthly_growth' => $monthlyGrowth,
            'monthly_breakdown' => $monthlyBreakdown,
            'top_categories' => $topCategories,
        ];
    }

    /**
     * Fetch sales trends over the past year.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyTrends()
    {
        $totalColumn = 'total_price'; // Change to 'total_amount' if that's your column name
        
        return Order::selectRaw("YEAR(created_at) as year, MONTH(created_at) as month, SUM({$totalColumn}) as total")
            ->where('payment_status', 'Paid')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    /**
     * Fetch total sales for a given period.
     *
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getTotalSalesForPeriod(string $startDate, string $endDate)
    {
        $totalColumn = 'total_price'; // Change to 'total_amount' if that's your column name
        
        return Order::where('payment_status', 'Paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum($totalColumn);
    }
}