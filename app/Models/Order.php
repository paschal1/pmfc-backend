<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'total_price',
        'fullname',
        'email',
        'status',
        'payment_status',
        'transaction_id',
        'shipping_address',
        'order_date',
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
}
