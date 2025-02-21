<?php

namespace App\Utility;

use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Cache;

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
            return [
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
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

            // Create activity log
            UserActivityLog::create([
                'user_id' => $userId,
                'activity_type' => $activityType,
                'device_type' => $deviceType ?? request()->header('User-Agent'), // Capture user agent if not provided
                'ip_address' => $ipAddress ?? request()->ip(), // Capture IP address
                'created_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error("Failed to log activity: " . $e->getMessage());
            return false;
        }
    }
}
