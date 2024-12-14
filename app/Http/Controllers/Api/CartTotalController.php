<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartTotalController extends Controller
{
    public function getCartTotal()
{
    $userId = auth()->id();
    $total = CartItem::where('user_id', $userId)->with('product')
        ->get()
        ->reduce(function ($carry, $item) {
            return $carry + ($item->product->price * $item->quantity);
        }, 0);

    return response()->json(['total' => $total]);
}

public function saveForLater($cartItemId)
{
    $cartItem = CartItem::findOrFail($cartItemId);

    Wishlist::create([
        'user_id' => $cartItem->user_id,
        'product_id' => $cartItem->product_id,
    ]);

    $cartItem->delete();

    return response()->json(['message' => 'Item saved for later']);
}

public function clearCart()
{
    $userId = auth()->id();
    CartItem::where('user_id', $userId)->delete();

    return response()->json(['message' => 'Cart cleared']);
}


}
