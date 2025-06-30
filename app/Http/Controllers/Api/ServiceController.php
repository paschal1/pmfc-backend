<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'message' => 'Services retrieved successfully.',
            'data' => Service::latest()->get(),
        ]);
    }

  public function store(StoreServiceRequest $request): JsonResponse
{
    $validated = $request->validated();

    // Upload to Cloudinary
    if ($request->hasFile('image1')) {
        $validated['image1'] = Cloudinary::uploadApi()
            ->upload($request->file('image1')->getRealPath())['secure_url'];
    }

    if ($request->hasFile('image2')) {
        $validated['image2'] = Cloudinary::uploadApi()
            ->upload($request->file('image2')->getRealPath())['secure_url'];
    } else {
        $validated['image2'] = null; // Ensure image2 is set to null if not provided
    }

    $service = Service::create($validated);

    return response()->json([
        'message' => 'Service created successfully!',
        'data' => $service,
    ], 201);
}

    public function show(string $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        return response()->json([
            'message' => 'Service retrieved.',
            'data' => $service,
        ]);
    
    }

    public function update(UpdateServiceRequest $request, string $id): JsonResponse
{
    $service = Service::findOrFail($id);
    $validated = $request->validated();

    // Handle image1 upload if present
    if ($request->hasFile('image1')) {
        $validated['image1'] = Cloudinary::uploadApi()
            ->upload($request->file('image1')->getRealPath())['secure_url'];
    }

    // Handle image2 upload if present
    if ($request->hasFile('image2')) {
        $validated['image2'] = Cloudinary::uploadApi()
            ->upload($request->file('image2')->getRealPath())['secure_url'];
    }

    $service->update($validated);

    return response()->json([
        'message' => 'Service updated successfully!',
        'data' => $service->fresh(),
    ]);
}


    public function destroy(string $id): JsonResponse
    {
        $deleted = Service::destroy($id);

        return response()->json([
            'message' => $deleted ? 'Service deleted successfully.' : 'Service not found.',
        ], $deleted ? 200 : 404);
    }
}