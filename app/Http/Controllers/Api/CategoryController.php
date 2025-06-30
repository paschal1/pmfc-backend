<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Validator;


class CategoryController extends Controller
{
        public function index() 
        {
            $categories = Category::with('products')->paginate(16);
            return $this->respondWithData($categories);
        }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailimage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'pmfc/categories'
            ]);
            $imageUrl = $upload['secure_url'] ?? null;
        }

        $thumbUrl = null;
        if ($request->hasFile('thumbnailimage')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('thumbnailimage')->getRealPath(), [
                'folder' => 'pmfc/categories'
            ]);
            $thumbUrl = $upload['secure_url'] ?? null;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'image' => $imageUrl,
            'thumbnailimage' => $thumbUrl,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully!',
            'data' => $category,
        ], 201);
    }

    public function show(string $id)
    {
        $category = Category::with('products')->findOrFail($id);
        $this->respondWithData($category);
    }

   public function update(Request $request, string $id)
{
    try {
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailimage' => 'sometimes|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        if ($request->hasFile('image')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'pmfc/categories'
            ]);
            $data['image'] = $upload['secure_url'] ?? null;
        }

        if ($request->hasFile('thumbnailimage')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('thumbnailimage')->getRealPath(), [
                'folder' => 'pmfc/categories'
            ]);
            $data['thumbnailimage'] = $upload['secure_url'] ?? null;
        }

        $category->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully!',
            'data' => $category->fresh(),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error updating category',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully!',
        ]);
    }
}
