<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;

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

        $category = Category::create([
            'name' => $validatedData['name'],
            'image' => $validatedData['image'],
            'thumbnailimage' => $validatedData['thumbnailimage'],
            'slug' => Str::slug($validatedData['name']),
        ]);

        return response()->json(['message' => 'Category created successfully', 'category' => $category], 201);
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
                'name' => 'required|string|max:255|unique:categories,name',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
                'thumbnailimage' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            ]);
    


        $category->update([
            'name' => $request->input('name'),
            'image' => $request->input('image'),
            'thumbnailimage' => $request->input('thumbnailimage'),

        ]);

        return response()->json(['message' => 'Category updated successfully', 'category' => $category], 200);
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
