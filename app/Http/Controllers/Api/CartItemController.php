<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(CartItem::with('product')->where('user_id', auth()->id())->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::updateOrCreate(
            ['user_id' => auth()->id(), 'product_id' => $validated['product_id']],
            ['quantity' => $validated['quantity']]
        );

        return response()->json($cartItem, 201);
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
    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorize('update', $cartItem);

        $validated = $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json($cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CartItem $cartItem)
    {
        $this->authorize('delete', $cartItem);

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart'], 200);
    }
}
