<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive()
{
    $recentActivity = $this->activityLogs()
        ->where('created_at', '>=', now()->subDays(30))
        ->exists();

    return $this->last_login_at >= now()->subDays(30) || $recentActivity;
}

public function activityLogs()
{
    return $this->hasMany(UserActivityLog::class);
}

public function role()
    {
        return $this->belongsTo(Role::class);
    }

public function hasPermission($permissionName)
    {
        return $this->role->permissions->contains('name', $permissionName);
    }

}
