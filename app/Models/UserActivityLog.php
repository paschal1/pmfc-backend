<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'device_type',
        'ip_address',
        'created_at'
    ];
// Define the relationship to the User model
public function user()
{
    return $this->belongsTo(User::class);
}
}
