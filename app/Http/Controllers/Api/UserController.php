<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Utility\Methods;
use App\Utility\ImageProcessor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash; 
use App\Utility\Strings;



class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['users' => $users], 200);
    }

    /**
     * Display active users
     */
    public function activeUser()
    {
        $activeUsers = User::with('activityLogs')->get()->filter(fn($user) => $user->isActive());
        return response()->json(['activeUsers' => $activeUsers], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:Admin,Manager,User',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create user
        $user = User::create([
            'name' => Strings::sanitize($request->input('name')),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(['user' => $user], 200);
    }

    /**
     * Update the authenticated user profile
     * POST /api/update-profile
     */
    public function update(Request $request)
    {
        // ✅ Get authenticated user
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update user with sanitized data
        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = Strings::sanitize($request->input('name'));
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->input('email');
        }

        if ($request->has('phone')) {
            $updateData['phone'] = Strings::sanitize($request->input('phone'));
        }

        if ($request->has('address')) {
            $updateData['address'] = Strings::sanitize($request->input('address'));
        }

        $user->update($updateData);

        return response()->json(['message' => 'User updated successfully', 'data' => $user], 200);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    /**
     * Change user password
     * POST /api/change-password
     */
    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string|min:8',
            'newPassword' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify current password is correct
        if (!Hash::check($request->currentPassword, $user->password)) {
            return response()->json([
                'error' => 'Current password is incorrect'
            ], 422);
        }

        // Prevent using same password
        if (Hash::check($request->newPassword, $user->password)) {
            return response()->json([
                'error' => 'New password must be different from current password'
            ], 422);
        }

        // Password strength validation
        if (!$this->isStrongPassword($request->newPassword)) {
            return response()->json([
                'error' => 'Password must contain uppercase, lowercase, and numbers'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ], 200);
    }

    /**
     * Check password strength
     * Requires: uppercase, lowercase, and at least one number
     */
    private function isStrongPassword(string $password): bool
    {
        return preg_match('/[a-z]/', $password) && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
}