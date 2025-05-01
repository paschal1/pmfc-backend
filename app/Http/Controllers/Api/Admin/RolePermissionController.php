<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    // Create Permission and assign to Role
    public function createPermission(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        // Create the permission
        $permission = Permission::create([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'message' => 'Permission created successfully!',
            'permission' => $permission
        ]);
    }

    // Create Role and assign Permissions to the Role
    public function createRole(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        // Create the role
        $role = Role::create([
            'name' => $validated['name'],
        ]);

        // Assign permissions to the role
        $role->syncPermissions($validated['permissions']);

        return response()->json([
            'message' => 'Role created and permissions assigned successfully!',
            'role' => $role
        ]);
    }
}
