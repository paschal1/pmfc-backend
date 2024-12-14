<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
