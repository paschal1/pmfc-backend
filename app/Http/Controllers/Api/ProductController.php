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
        $imageResponse = ImageProcessor::processImage($request, 'image', 'products', 300, 200);
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
        $thumbnailResponse = ImageProcessor::processImage($request, 'thumbnailImage', 'products', 300, 200);
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
        'message' => Strings::ProductAdded(),  // Ensure this method exists and returns a message
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

        // Validate request
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailImage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        // Fallback to current paths
        $imagePath = $product->image;
        $thumbnailImagePath = $product->thumbnailImage;

        // Process main image if uploaded
        if ($request->hasFile('image')) {
            if ($product->image) {
                // Remove old image
                @unlink(public_path(str_replace(asset('/'), '', $product->image)));
            }

            $imageResponse = ImageProcessor::processImage($request, 'image', 'products', 300, 200);
            if ($imageResponse['success']) {
                $imagePath = $imageResponse['image_url'];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Image upload failed!',
                    'errors' => $imageResponse['errors'] ?? 'An unknown error occurred.',
                ], 400);
            }
        }

        // Process thumbnail image if uploaded
        if ($request->hasFile('thumbnailImage')) {
            if ($product->thumbnailImage) {
                // Remove old thumbnail
                @unlink(public_path(str_replace(asset('/'), '', $product->thumbnailImage)));
            }

            $thumbResponse = ImageProcessor::processImage($request, 'thumbnailImage', 'products', 300, 200);
            if ($thumbResponse['success']) {
                $thumbnailImagePath = $thumbResponse['image_url'];
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Thumbnail image upload failed!',
                    'errors' => $thumbResponse['errors'] ?? 'An unknown error occurred.',
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
            'data' => $product,
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
