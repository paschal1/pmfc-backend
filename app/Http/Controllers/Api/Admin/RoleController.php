<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

class RoleController extends Controller
{
     /**
     * Get all roles.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return response()->json(['roles' => $roles], 200);
    }

    /**
     * Create a new role with permissions.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array', // Array of permission IDs
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $validatedData['name']]);
        $role->permissions()->sync($validatedData['permissions'] ?? []);

        return response()->json(['message' => 'Role created successfully', 'role' => $role], 201);
    }

    /**
     * Update a role's permissions.
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);
        $role->permissions()->sync($validatedData['permissions'] ?? []);

        return response()->json(['message' => 'Role updated successfully', 'role' => $role], 200);
    }

    /**
     * Delete a role.
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(['message' => 'Role deleted successfully'], 200);
    }

    /**
     * Get all permissions.
     */
    public function getPermissions()
    {
        $permissions = Permission::all();
        return response()->json(['permissions' => $permissions], 200);
    }
}
