<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Project::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|max:2048', // optional: add max 2MB
    ]);

    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('projects');
        $validated['image'] = $path;
    }

    $project = Project::create($validated);

    // If you want to return the **full image URL**, build it:
    if ($project->image) {
        $project->image_url = Storage::url($project->image);
    } else {
        $project->image_url = null;
    }

    return response()->json([
        'message' => 'Project created successfully!',
        'project' => $project
    ], 201);
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Project::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image',
            'status' => 'nullable|in:ongoing,completed',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('projects');
        }

        $project->update($validated);

        return response()->json(['message' => 'Project updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Project::destroy($id);

        return response()->json(['message' => 'Project deleted successfully!']);
    }
}
