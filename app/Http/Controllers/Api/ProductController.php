<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->paginate(16);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailImage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'pmfc/products'
            ]);
            $imageUrl = $upload['secure_url'] ?? null;
        }

        $thumbUrl = null;
        if ($request->hasFile('thumbnailImage')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('thumbnailImage')->getRealPath(), [
                'folder' => 'pmfc/products'
            ]);
            $thumbUrl = $upload['secure_url'] ?? null;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'category_id' => $validated['category_id'],
            'image' => $imageUrl,
            'thumbnailImage' => $thumbUrl,
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Product added successfully!',
            'data' => $product,
        ], 201);
    }

    public function show(string $id)
    {
        $product = Product::with('category')->findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, string $id)
    {
        try {
            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
                'thumbnailImage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            ]);

            $data = [
                'name' => $validated['name'],
                'description' => $validated['description'],
                'price' => $validated['price'],
                'stock' => $validated['stock'],
                'category_id' => $validated['category_id'],
                'slug' => Str::slug($validated['name']),
            ];

            if ($request->hasFile('image')) {
                $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                    'folder' => 'pmfc/products'
                ]);
                $data['image'] = $upload['secure_url'] ?? null;
            }

            if ($request->hasFile('thumbnailImage')) {
                $upload = Cloudinary::uploadApi()->upload($request->file('thumbnailImage')->getRealPath(), [
                    'folder' => 'pmfc/products'
                ]);
                $data['thumbnailImage'] = $upload['secure_url'] ?? null;
            }

            $product->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully!',
                'data' => $product->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully!',
        ]);
    }
}
