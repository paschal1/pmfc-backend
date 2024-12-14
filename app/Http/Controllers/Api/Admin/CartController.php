<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function viewCart()
{
    $userId = auth()->id();
    $cartItems = CartItem::where('user_id', $userId)->with('product')->get();

    return response()->json($cartItems);
}

}
