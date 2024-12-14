<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Fetch all wishlist items for the logged-in user
         $wishlist = auth()->user()->wishlist()->with('product')->get();
         return response()->json($wishlist);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Add an item to the wishlist
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $validated['product_id'],
        ]);

        return response()->json(['message' => 'Item added to wishlist', 'item' => $wishlist]);
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
                // Remove an item from the wishlist
                $wishlist = Wishlist::findOrFail($id);
                $this->authorize('delete', $wishlist);
        
                $wishlist->delete();
        
                return response()->json(['message' => 'Item removed from wishlist']);
    }
}
