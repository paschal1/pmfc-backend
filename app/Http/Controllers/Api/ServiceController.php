<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    /**
     * Get all services
     * Optional query parameters:
     * - type=Residential Design (filter by type)
     * - min_price=5000&max_price=50000 (filter by price range)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::latest();

        // Filter by type if provided
        if ($request->has('type')) {
            $type = $request->query('type');
            $query->byType($type);
        }

        // Filter by price range if provided
        if ($request->has('min_price') && $request->has('max_price')) {
            $minPrice = (float) $request->query('min_price');
            $maxPrice = (float) $request->query('max_price');
            $query->byPriceRange($minPrice, $maxPrice);
        }

        $services = $query->get();

        return response()->json([
            'message' => 'Services retrieved successfully.',
            'data' => $services,
        ]);
    }

    /**
     * Get all available service types
     */
    public function getTypes(): JsonResponse
    {
        return response()->json([
            'message' => 'Service types retrieved successfully.',
            'data' => Service::getAvailableTypes(),
        ]);
    }

    /**
     * Get services grouped by type
     */
    public function getByType(): JsonResponse
    {
        $servicesByType = [];

        foreach (Service::getAvailableTypes() as $type) {
            $servicesByType[$type] = Service::byType($type)->get();
        }

        return response()->json([
            'message' => 'Services retrieved by type.',
            'data' => $servicesByType,
        ]);
    }

    /**
     * Create a new service
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            Log::info('Creating service', [
                'request_data' => $request->all(),
            ]);

            $validated = $request->validated();

            Log::info('Validated data for create', [
                'type' => $validated['type'] ?? 'NOT SET',
                'title' => $validated['title'] ?? 'NOT SET',
            ]);

            // Upload to Cloudinary
            if ($request->hasFile('image1')) {
                $validated['image1'] = Cloudinary::uploadApi()
                    ->upload($request->file('image1')->getRealPath())['secure_url'];
            }

            if ($request->hasFile('image2')) {
                $validated['image2'] = Cloudinary::uploadApi()
                    ->upload($request->file('image2')->getRealPath())['secure_url'];
            } else {
                $validated['image2'] = null;
            }

            Log::info('About to create service', [
                'data' => $validated,
            ]);

            // Create the service
            $service = Service::create($validated);

            Log::info('Service created', [
                'id' => $service->id,
                'type' => $service->type,
                'title' => $service->title,
            ]);

            return response()->json([
                'message' => 'Service created successfully!',
                'data' => $service->refresh(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating service', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to create service',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single service by ID
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        return response()->json([
            'message' => 'Service retrieved.',
            'data' => $service,
        ]);
    }

    /**
     * Update a service
     */
    public function update(UpdateServiceRequest $request, string $id): JsonResponse
    {
        try {
            Log::info('Updating service', [
                'id' => $id,
                'request_data' => $request->all(),
            ]);

            $service = Service::findOrFail($id);
            $validated = $request->validated();

            Log::info('Validated data for update', [
                'type' => $validated['type'] ?? 'NOT SET',
                'title' => $validated['title'] ?? 'NOT SET',
            ]);

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

            Log::info('About to update service', [
                'id' => $id,
                'data' => $validated,
            ]);

            // Update the service
            $service->update($validated);

            Log::info('Service updated', [
                'id' => $service->id,
                'type' => $service->fresh()->type,
            ]);

            return response()->json([
                'message' => 'Service updated successfully!',
                'data' => $service->fresh(),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating service', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to update service',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a service
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = Service::destroy($id);

        return response()->json([
            'message' => $deleted ? 'Service deleted successfully.' : 'Service not found.',
        ], $deleted ? 200 : 404);
    }
}