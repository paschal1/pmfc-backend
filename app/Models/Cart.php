<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Cart extends Model
{
    use HasFactory; 
    
    protected $fillable = ['user_id', 'session_id', 'total', 'status'];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function calculateTotal()
    {
        $total = $this->cartItems->sum(function($item) {
            return $item->sub_total;
        });
        $this->update(['total' => $total]);
    }

}
