<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function register(Request $request)
    {
        // Validate the incoming request
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|string|max:255',
        //     'email' => 'required|email|unique:users,email',
        //     'phone' => 'required|string|max:15|unique:users,phone',
        //     'address' => 'required|string|max:255',
        //     'password' => 'required|string|confirmed|min:8',
        // ]);
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => 'Validation errors',
        //         'errors' => $validator->errors(),
        //     ], 422);
        // }

        // Create the user
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'address' => $request->input('address'),
            'password' => Hash::make($request->input('password')),
        ]);


        // Generate a personal access token
        $token = $user->createToken('Personal Access Token')->plainTextToken;
        
         // Log the user in
         auth()->login($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token, // Return the token for immediate use
        ], 201);
    }

}
