<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Fillable fields for mass assignment
     protected $fillable = [
        'user_id',
        'product_id',
        'total_price',
        'fullname',
        'email',
        'payment_method',
        'payment_type',
        'deposit_amount',
        'remaining_amount',
        'payment_status',
        'tracking_number',
        'status',
        'transaction_id',
        'order_date',
        'shipping_address',
        'shipping_state',
        'shipping_city',
        'shipping_zip_code',
    ];

    // Casts for proper data types
    protected $casts = [
        'total_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'order_date' => 'date',
    ];
    /**
     * Define the relationship to the User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Define the relationship to the Product model.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // Get only paid orders
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    // Get orders by status
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Get today's orders
    public function scopeToday($query)
    {
        return $query->whereDate('order_date', now()->toDateString());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    // Full location accessor
    public function getFullShippingLocationAttribute(): string
    {
        return "{$this->shipping_city}, {$this->shipping_state} {$this->shipping_zip_code}";
    }

    // Check if fully paid
    public function getIsFullyPaidAttribute(): bool
    {
        return $this->payment_status === 'Paid' && $this->remaining_amount == 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators
    |--------------------------------------------------------------------------
    */

    // Capitalize full name
    public function setFullnameAttribute($value): void
    {
        $this->attributes['fullname'] = ucwords(strtolower($value));
    }

    // Automatically uppercase tracking number
    public function setTrackingNumberAttribute($value): void
    {
        $this->attributes['tracking_number'] = strtoupper($value);
    }
}
