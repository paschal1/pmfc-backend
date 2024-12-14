<?php

namespace App\Utility;

use App\Models\Order;

class SalesReportService
{
    /**
     * Fetch sales reports grouped by month.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSalesReports()
    {
        return Order::selectRaw('MONTH(created_at) as month, SUM(total_amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /**
     * Fetch sales trends over the past year.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getYearlyTrends()
    {
        return Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total_amount) as total')
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
        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');
    }
}
