<?php

namespace App\Utility;

use App\Models\User;
use App\Models\Order;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserActivityService
{
    /**
     * Get User Activity Statistics
     *
     * @return array
     */
    public static function getUserActivityStats()
    {
        return Cache::remember('user_activity_stats', 60, function () {
            $today = Carbon::today();
            $thisWeek = Carbon::now()->startOfWeek();
            $thisMonth = Carbon::now()->startOfMonth();
            
            // Total users
            $totalUsers = User::count();
            
            // Active users today (users who placed orders today)
            $activeUsersToday = Order::whereDate('created_at', $today)
                ->distinct('user_id')
                ->count('user_id');
            
            // New users today
            $newUsersToday = User::whereDate('created_at', $today)->count();
            
            // New users this week
            $newUsersThisWeek = User::whereBetween('created_at', [$thisWeek, now()])->count();
            
            // New users this month
            $newUsersThisMonth = User::whereBetween('created_at', [$thisMonth, now()])->count();
            
            // Users with orders (customers who have purchased)
            $usersWithOrders = Order::distinct('user_id')->count('user_id');
            
            // Average orders per user
            $totalOrders = Order::count();
            $averageOrdersPerUser = $totalUsers > 0 ? round($totalOrders / $totalUsers, 2) : 0;
            
            // Returning customers (users with more than 1 order)
            $returningCustomers = DB::table('orders')
                ->select('user_id', DB::raw('COUNT(*) as order_count'))
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            
            // Active/Inactive users (if you have is_active column)
            $activeUsers = 0;
            $inactiveUsers = 0;
            
            try {
                $activeUsers = User::where('is_active', true)->count();
                $inactiveUsers = User::where('is_active', false)->count();
            } catch (\Exception $e) {
                // Column doesn't exist, skip
            }
            
            // User activity by day (last 7 days)
            $activityByDay = Order::selectRaw('DATE(created_at) as date, COUNT(DISTINCT user_id) as active_users')
                ->whereBetween('created_at', [Carbon::now()->subDays(6), now()])
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('M d'),
                        'active_users' => $item->active_users,
                    ];
                });
            
            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers,
                'active_users_today' => $activeUsersToday,
                'new_users_today' => $newUsersToday,
                'new_users_this_week' => $newUsersThisWeek,
                'new_users_this_month' => $newUsersThisMonth,
                'users_with_orders' => $usersWithOrders,
                'average_orders_per_user' => $averageOrdersPerUser,
                'returning_customers' => $returningCustomers,
                'activity_by_day' => $activityByDay,
            ];
        });
    }

    /**
     * Log User Activity
     *
     * @param int $userId
     * @param string $activityType
     * @return bool
     */
    public static function log($userId, $activityType, $deviceType = null, $ipAddress = null)
    {
        try {
            // Validate user existence
            if (!User::find($userId)) {
                throw new \Exception("User ID {$userId} does not exist.");
            }

            // Create activity log (only if UserActivityLog model exists)
            if (class_exists('App\Models\UserActivityLog')) {
                UserActivityLog::create([
                    'user_id' => $userId,
                    'activity_type' => $activityType,
                    'device_type' => $deviceType ?? request()->header('User-Agent'),
                    'ip_address' => $ipAddress ?? request()->ip(),
                    'created_at' => now(),
                ]);
            }

            return true;
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }
}