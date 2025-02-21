<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;

class WishlistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         // Fetch all wishlist items for the logged-in user

        $wishlist = Wishlist::with('product')
        ->where('user_id', auth()->id())
        ->get();

    // Format the response
    $response = $wishlist->map(function ($item) {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product->name ?? 'N/A',
            'product_price' => $item->product->price ?? 0,
            'product_description' => $item->product->description ?? 'No description',
            'added_at' => $item->created_at->toDateTimeString(),
        ];
    });

    return response()->json($response);
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
   /**
 * Display the specified resource.
 */
public function show(string $id)
{
    // Fetch the wishlist item by ID
    $wishlist = Wishlist::with('product')->find($id);

    // Check if the wishlist item exists
    if (!$wishlist) {
        return response()->json(['message' => 'Wishlist item not found'], 404);
    }

    // Ensure the wishlist item belongs to the logged-in user
    if ($wishlist->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized access'], 403);
    }

    // Format the response
    $response = [
        'id' => $wishlist->id,
        'product_id' => $wishlist->product_id,
        'product_name' => $wishlist->product->name ?? 'N/A',
        'product_price' => $wishlist->product->price ?? 0,
        'product_description' => $wishlist->product->description ?? 'No description',
        'added_at' => $wishlist->created_at->toDateTimeString(),
    ];

    return response()->json($response);
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, string $id)
{
    // Fetch the wishlist item by ID
    $wishlist = Wishlist::findOrFail($id);

    // Ensure the wishlist item belongs to the logged-in user
    if ($wishlist->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized access'], 403);
    }

    // Validate the incoming request
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
    ]);

    // Update the wishlist item
    $wishlist->update([
        'product_id' => $validated['product_id'],
    ]);

    return response()->json(['message' => 'Wishlist updated successfully', 'item' => $wishlist]);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
                // Remove an item from the wishlist
                $wishlist = Wishlist::findOrFail($id);
              //  $this->authorize('delete', $wishlist);
        
                $wishlist->delete();
        
                return response()->json(['message' => 'Item removed from wishlist']);
    }
}
