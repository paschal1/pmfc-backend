<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Service::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    // Validate input fields
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'price' => 'required|numeric|min:0',
        'image1' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'image2' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    // Handle file upload for image1
    if ($request->hasFile('image1')) {
        $validated['image1'] = $request->file('image1')->store('Services', 'public');
    }

    // Handle file upload for image2
    if ($request->hasFile('image2')) {
        $validated['image2'] = $request->file('image2')->store('Services', 'public');
    }

    try {
        // Create the service and capture the created model
        $service = Service::create($validated);

        // Return the saved service data using a resource (optional) or raw data
        return response()->json([
            'message' => 'Service created successfully!',
            'data' => $service
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to create service',
            'details' => $e->getMessage()
        ], 500);
    }
}

    

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Service::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image1' => 'nullable|image',
            'image2' => 'nullable|image',
        ]);

        if ($request->hasFile('image1')) {
            $validated['image1'] = $request->file('image1')->store('Services');
        }

        
        if ($request->hasFile('image2')) {
            $validated['image2'] = $request->file('image2')->store('Services');
        }


        $Service->update($validated);

        return response()->json(['message' => 'Service updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Service::destroy($id);

        return response()->json(['message' => 'Service deleted successfully!']);
    }
}
