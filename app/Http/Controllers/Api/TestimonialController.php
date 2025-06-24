<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;

class TestimonialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all approved testimonials
        $testimonials = Testimonial::where('is_approved', true)->latest()->get();
        return response()->json($testimonials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate and submit a testimonial
        $validated = $request->validate([
            'name'=> 'required|string|max:255',
            'review' => 'required|string|max:500',
        ]);

        $testimonial = Testimonial::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'review' => $validated['review'],
        ]);

        return response()->json(['message' => 'Testimonial submitted for review'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Find a specific testimonial by ID
        $testimonial = Testimonial::findOrFail($id);

        return response()->json($testimonial);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validate and update the testimonial
        $validated = $request->validate([
           // 'message' => 'required|string|max:500',
            'is_approved' => 'nullable|boolean',
            'name' => 'sometimes|string|max:255',
            'review' => 'sometimes|string|max:500',
        ]);

        $testimonial = Testimonial::findOrFail($id);

        $testimonial->update($validated);

        return response()->json(['message' => 'Testimonial updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Delete a testimonial by ID
        $testimonial = Testimonial::findOrFail($id);

        $testimonial->delete();

        return response()->json(['message' => 'Testimonial deleted successfully']);
    }
}
