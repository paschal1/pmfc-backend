<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Project;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json(Project::latest()->get(), 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'status' => 'nullable|in:ongoing,completed',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                'folder' => 'pmfc/projects'
            ]);
            $imageUrl = $upload['secure_url'] ?? null;
        }

        $project = Project::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'image' => $imageUrl,
            'slug' => Str::slug($validated['title']),
            'status' => $validated['status'] ?? 'ongoing',
        ]);

        return response()->json([
            'message' => 'Project created successfully!',
            'project' => $project
        ], 201);
    }

    public function show(string $id)
    {
        $project = Project::findOrFail($id);
        return response()->json($project, 200);
    }

    public function update(Request $request, string $id)
    {
        try {
            $project = Project::findOrFail($id);

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
                'status' => 'nullable|in:ongoing,completed',
            ]);

            $data = [];

            if ($request->filled('title')) {
                $data['title'] = $validated['title'];
                $data['slug'] = Str::slug($validated['title']);
            }

            if ($request->filled('description')) {
                $data['description'] = $validated['description'];
            }

            if (isset($validated['status'])) {
                $data['status'] = $validated['status'];
            }

            if ($request->hasFile('image')) {
                $upload = Cloudinary::uploadApi()->upload($request->file('image')->getRealPath(), [
                    'folder' => 'pmfc/projects'
                ]);
                $data['image'] = $upload['secure_url'] ?? null;
            }

            if (!empty($data)) {
                $project->update($data);
            }

            return response()->json([
                'message' => 'Project updated successfully!',
                'project' => $project->fresh()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while updating the project.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully!'], 200);
    }
}
