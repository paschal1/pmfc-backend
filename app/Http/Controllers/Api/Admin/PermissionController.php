<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Create new permissions (single or multiple).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'permissions' => 'required|array', // Expecting an array of permission names
            'permissions.*' => 'required|string|unique:permissions,name', // Each permission must be unique
        ]);

        $createdPermissions = [];

        foreach ($validatedData['permissions'] as $permissionName) {
            $createdPermissions[] = Permission::create(['name' => $permissionName]);
        }

        return response()->json([
            'message' => 'Permissions created successfully',
            'permissions' => $createdPermissions,
        ], 201);
    }

    /**
     * Delete a permission.
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully'], 200);
    }
}
