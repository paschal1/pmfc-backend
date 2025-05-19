<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Utility\Methods;
use App\Utility\ImageProcessor;
use App\Utility\Strings;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Blog::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Added mime types and size limit
        ]);
    
        $imagePath = null;
    
        // Handle image upload
        if ($request->hasFile('image')) {
           $imageResponse = ImageProcessor::processImage($request, 'image', 'uploads/blogs', 300, 200);
    
            if (!$imageResponse['success']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Image upload failed!',
                    'errors' => $imageResponse['errors'] ?? 'An unknown error occurred.',
                ], 422);
            }
    
            $imagePath = $imageResponse['image_url']; // Get the processed image path
        }
    
        // Prepare data for blog creation
        $blogData = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image' => $imagePath,
            'author_id' => auth()->id(), // Set the authenticated user as the author
        ];
    
        // Create the blog
        $blog = Blog::create($blogData);
    
        // Return success response with the blog details
        return response()->json([
            'message' => 'Blog created successfully!',
            'data' => $blog, // Include the created blog in the response
        ]);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Blog::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = Blog::findOrFail($id);
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required',
        'image' => 'nullable|image',
    ]);

    $imagePath = $blog->image; // Keep existing image path as fallback
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($blog->image) {
            Storage::delete('public/blogs/' . $blog->image);
        }

        $imageResponse = ImageProcessor::processImage($request, 'blogs', 300, 200);
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

    $blog->update($validated);

    return response()->json(['message' => 'Blog updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Blog::destroy($id);
        return response()->json(['message' => 'Blog deleted successfully!']);
    }
}
