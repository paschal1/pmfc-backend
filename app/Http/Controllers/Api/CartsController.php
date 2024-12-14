<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all cart items for the logged-in user
        $cartItems = auth()->user()->cartItems()->with('product')->get();
        return response()->json($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Add an item to the cart
         $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'product_id' => $validated['product_id'],
            ],
            [
                'quantity' => $validated['quantity'],
                'price' => $request->price,
            ]
        );

        return response()->json(['message' => 'Item added to cart', 'item' => $cartItem]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $cartItemId)
    {
        
    $cartItem = CartItem::findOrFail($cartItemId);

    if ($request->input('quantity') <= 0) {
        $cartItem->delete();
        return response()->json(['message' => 'Item removed from cart']);
    }

    $cartItem->quantity = $request->input('quantity');
    $cartItem->save();

    return response()->json(['message' => 'Cart updated']);
}

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Remove an item from the cart
        $cartItem = CartItem::findOrFail($id);
        $this->authorize('delete', $cartItem);

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}
