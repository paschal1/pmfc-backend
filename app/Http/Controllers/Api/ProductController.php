<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Utility\Methods;
use App\Utility\ImageProcessor;
use App\Utility\Strings;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->paginate(16); // You can adjust pagination as needed
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

          // Handle image upload if provided
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imageResponse = ImageProcessor::processImage($request, 'products', 300, 200);
        if ($imageResponse['success']) {
            $imagePath = $imageResponse['image_url']; // Path to the processed image
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Image upload failed!',
                'errors' => $imageResponse['errors'] ?? 'An unknown error occurred.',
            ], 400);
        }
    }


        // Create the product
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'image' => $imagePath,
        ]);

        return response()->json([
            'status' => true,
            'message' => Strings::ProductAdded(),
            'data' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);
    
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            ]);
    
            // Handle image upload
            $imagePath = $product->image; // Keep existing image path as fallback
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($product->image) {
                    Storage::delete('public/products/' . $product->image);
                }
    
                $imageResponse = ImageProcessor::processImage($request, 'products', 300, 200);
                if ($imageResponse['success']) {
                    $imagePath = $imageResponse['image_url']; // Path to the processed image
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Image upload failed!',
                        'errors' => $imageResponse['errors'] ?? 'An unknown error occurred.',
                    ], 400);
                }
            }
    
            // Update product
            $product->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'stock' => $request->input('stock'),
                'category_id' => $request->input('category_id'),
                'image' => $imagePath,
            ]);
    
            return response()->json([
                'status' => true,
                'message' => Strings::ProductUpdated(),
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Delete image if it exists
        if ($product->image) {
            Storage::delete('public/products/' . $product->image);
        }

        // Delete product
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => Strings::ProductDeleted()
        ]);
    }
}
