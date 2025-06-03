<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('products')->get();
        return response()->json($categories, 200);  
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'thumbnailimage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories/images', 'public');
        }

        $thumbnailPath = null;
        if ($request->hasFile('thumbnailimage')) {
            $thumbnailPath = $request->file('thumbnailimage')->store('categories/thumbnails', 'public');
        }

        $category = Category::create([
            'name' => $validatedData['name'],
            'image' => $imagePath,
            'thumbnailimage' => $thumbnailPath,
            'slug' => Str::slug($validatedData['name']),
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('products'); // Eager load related products
        return response()->json($category, 200);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(Request $request, string $id)
{
    try {
        $category = Category::findOrFail($id);

        $request->validate([
        'name' => 'required|string|max:255|unique:categories,name,' . $id,
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        'thumbnailimage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
    ]);


        $data = [
            'name' => $request->input('name'),
        ];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('categories', 'public');
            $data['image'] = $imagePath;
        }

        if ($request->hasFile('thumbnailimage')) {
            $thumbPath = $request->file('thumbnailimage')->store('categories/thumbnails', 'public');
            $data['thumbnailimage'] = $thumbPath;
        }

        $category->update($data);

        // Generate full URL
        $category->image = $category->image ? Storage::url($category->image) : null;
        $category->thumbnailimage = $category->thumbnailimage ? Storage::url($category->thumbnailimage) : null;

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while updating the Category.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return response()->json(['message' => 'Cannot delete category with associated products'], 400);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
    
}
