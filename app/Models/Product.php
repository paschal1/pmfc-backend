<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory; // Ensure this trait is included to enable the factory method.

    protected $table = "products";
    protected $fillable = [
        'name',
        'description',
        'stock',
        'price',
        'image',
    ];

    // Define the relationship between Product and Category (many-to-one)
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

     /**
     * Define the relationship to Order Items.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

     /**
     * Example scope to filter products by availability.
     */
    public function scopeAvailable($query)
    {
        return $query->where('stock', '>', 0);
    }
}
