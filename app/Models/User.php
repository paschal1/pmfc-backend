<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
     * The attributes that should be hidden for arrays.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Check if the user is active (based on login or recent activity).
     */
    public function isActive()
    {
        $recentActivity = $this->activityLogs()
            ->where('created_at', '>=', now()->subDays(30))
            ->exists();

        return $this->last_login_at >= now()->subDays(30) || $recentActivity;
    }

    /**
     * User activity logs relationship.
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Ratings relationship.
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Wishlist (Many-to-Many with Product).
     */
    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists', 'user_id', 'product_id');
    }

    public function guardName()
{
    return 'sanctum';
}

}
