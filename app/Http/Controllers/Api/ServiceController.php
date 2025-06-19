<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'image1' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
            'image2' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        // Upload images to Cloudinary if present
                    if ($request->hasFile('image1')) {
                    $validated['image1'] = Cloudinary::uploadApi()
                        ->upload($request->file('image1')->getRealPath())['secure_url'];
                }

                if ($request->hasFile('image2')) {
                    $validated['image2'] = Cloudinary::uploadApi()
                        ->upload($request->file('image2')->getRealPath())['secure_url'];
                }


        // dd($validated);

        try {
            $service = Service::create($validated);

            return response()->json([
                'message' => 'Service created successfully!',
                'data' => $service,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create service',
                'details' => $e->getMessage(),
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
    $service = Service::findOrFail($id);

    $validated = $request->validate([
        'title' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric|min:0',
        'image1' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'image2' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    ]);

    $dataToUpdate = [];

    if ($request->filled('title')) {
        $dataToUpdate['title'] = $validated['title'];
    }

    if ($request->filled('description')) {
        $dataToUpdate['description'] = $validated['description'];
    }

    if ($request->filled('price')) {
        $dataToUpdate['price'] = $validated['price'];
    }

    if ($request->hasFile('image1')) {
        $upload1 = Cloudinary::uploadApi()->upload(
            $request->file('image1')->getRealPath()
        );
        $dataToUpdate['image1'] = $upload1['secure_url'] ?? null;
    }

    if ($request->hasFile('image2')) {
        $upload2 = Cloudinary::uploadApi()->upload(
            $request->file('image2')->getRealPath()
        );
        $dataToUpdate['image2'] = $upload2['secure_url'] ?? null;
    }

    $service->update($dataToUpdate);

    return response()->json([
        'message' => 'Service updated successfully!',
        'data' => $service->fresh(),
    ]);
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
