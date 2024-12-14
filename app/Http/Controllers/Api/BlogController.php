<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'image' => 'nullable|image',
        ]);
    
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('blogs');
        }
    
        $validated['author_id'] = auth()->id();
    
        Blog::create($validated);
    
        return response()->json(['message' => 'Blog created successfully!']);
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

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('blogs');
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
