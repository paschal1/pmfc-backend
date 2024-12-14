<?php

namespace App\Utility;

use App\Models\User;

class UserActivityService
{
    public function getUserActivityStats()
    {
        return [
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
        ];
    }

    public static function log($userId, $activityType)
    {
        UserActivityLog::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'created_at' => now(),
        ]);
    }
}

