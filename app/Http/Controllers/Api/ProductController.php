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
use Illuminate\Support\Facades\Validator;



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
        // Validate incoming request data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailImage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        // Initialize image paths
        $imagePath = null;
        $thumbnailImagePath = null;

        // Process main image if uploaded
        if ($request->hasFile('image')) {
            $imageResponse = ImageProcessor::processImage($request, 'products', 300, 200);
            if ($imageResponse['success']) {
                $imagePath = $imageResponse['image_url'];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Main image upload failed!',
                    'errors' => $imageResponse['errors'] ?? 'Unknown error.',
                ], 400);
            }
        }

        // Process thumbnail image if uploaded
        if ($request->hasFile('thumbnailImage')) {
            $thumbnailResponse = ImageProcessor::processImage($request, 'products', 300, 200);
            if ($thumbnailResponse['success']) {
                $thumbnailImagePath = $thumbnailResponse['image_url'];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Thumbnail image upload failed!',
                    'errors' => $thumbnailResponse['errors'] ?? 'Unknown error.',
                ], 400);
            }
        }

        // Create product with validated data and image paths
        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'image' => $imagePath,
            'thumbnailImage' => $thumbnailImagePath,
        ]);

        return response()->json([
            'status' => true,
            'message' => Strings::ProductAdded(),  // Make sure Strings::ProductAdded() exists
            'data' => $product,
        ], 201);
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
                'thumbnailImage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
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

             // Handle image upload
             $thumbnailImagePath = $product->thumbnailImage; // Keep existing image path as fallback
             if ($request->hasFile('thumbnailImage')) {
                 // Delete the old image if it exists
                 if ($product->thumbnailImage) {
                     Storage::delete('public/products/' . $product->thumbnailImage);
                 }
     
                 $imageResponse = ImageProcessor::processImage($request, 'products', 300, 200);
                 if ($imageResponse['success']) {
                     $thumbnailImagePath = $imageResponse['image_url']; // Path to the processed image
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
                'thumbnailImage' => $thumbnailImagePath, 
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
