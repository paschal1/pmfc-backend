<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Testimonial;

class TestimonialController extends Controller
{

    public function showTestimonial($id)
    {
        $product = Testimonial::findOrFail($id);
        return response()->json($product);
    }

   /**
     * Approve a testimonial.
     */
    public function approveTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_approved = true; // Assuming 'is_approved' is a boolean column in the table
        $testimonial->approved_at = now(); // Optional: track when it was approved
        $testimonial->save();

        return response()->json(['message' => 'Testimonial approved successfully.']);
    }

    /**
     * Update a testimonial.
     */
    public function updateTestimonial(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validatedData = $request->validate([
            'content' => 'required|string|max:500',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $testimonial->update($validatedData);

        return response()->json(['message' => 'Testimonial updated successfully.']);
    } 

    public function ViewTestimonial()
    {
        // Fetch all approved testimonials
        $testimonials = Testimonial::all();
        return response()->json($testimonials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // Submit a testimonial
         $validated = $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $testimonial = Testimonial::create([
            'user_id' => auth()->id(),
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Testimonial submitted for review']);
    }

}