<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;

class CartItemController extends Controller
{
    /**
     * Display a listing of the user's cart items.
     */
    public function index()
    {
        try {
            $cartItems = CartItem::with('product')
                ->where('user_id', auth()->id())
                ->get();

            return response()->json($cartItems, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch cart items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created cart item in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            // Get product to check stock
            $product = Product::findOrFail($validated['product_id']);

            // Check if product is in stock
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $product->stock
                ], 400);
            }

            // Check if item already exists in cart
            $existingItem = CartItem::where('user_id', auth()->id())
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($existingItem) {
                // Update quantity if item exists
                $newQuantity = $existingItem->quantity + $validated['quantity'];
                
                if ($product->stock < $newQuantity) {
                    return response()->json([
                        'message' => 'Cannot add more items. Insufficient stock',
                        'available_stock' => $product->stock,
                        'current_cart_quantity' => $existingItem->quantity
                    ], 400);
                }

                $existingItem->update(['quantity' => $newQuantity]);
                $cartItem = $existingItem->load('product');
            } else {
                // Create new cart item
                $cartItem = CartItem::create([
                    'user_id' => auth()->id(),
                    'product_id' => $validated['product_id'],
                    'quantity' => $validated['quantity'],
                ]);
                $cartItem->load('product');
            }

            return response()->json([
                'message' => 'Product added to cart successfully',
                'cart_item' => $cartItem
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to add item to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified cart item.
     */
    public function show(string $id)
    {
        try {
            $cartItem = CartItem::with('product')
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            return response()->json($cartItem, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        }
    }

    /**
     * Update the specified cart item quantity.
     */
    public function update(Request $request, $id)
    {
        try {
            $cartItem = CartItem::where('user_id', auth()->id())->findOrFail($id);

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            // Check stock availability
            $product = Product::findOrFail($cartItem->product_id);
            
            if ($product->stock < $validated['quantity']) {
                return response()->json([
                    'message' => 'Insufficient stock',
                    'available_stock' => $product->stock
                ], 400);
            }

            $cartItem->update(['quantity' => $validated['quantity']]);
            $cartItem->load('product');

            return response()->json([
                'message' => 'Cart updated successfully',
                'cart_item' => $cartItem
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified cart item from storage.
     */
    public function destroy($id)
    {
        try {
            $cartItem = CartItem::where('user_id', auth()->id())->findOrFail($id);
            $cartItem->delete();

            return response()->json([
                'message' => 'Item removed from cart successfully'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Cart item not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to remove item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}