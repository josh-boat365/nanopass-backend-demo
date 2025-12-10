<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users,username|regex:/^[a-zA-Z0-9_-]+$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        try {
            // Generate API token
            $apiToken = Str::random(80);

            // Create the user
            $user = User::create([
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'api_token' => $apiToken,
            ]);

            return response()->json([
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                ],
                'token' => $apiToken,
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during registration: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Registration error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * User login function
     */
    public function login(Request $request)
    {
        // Validate input - accept either username or email
        $validated = $request->validate([
            'login_credentials' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by username or email
        $user = User::where('username', $validated['login_credentials'])
            ->orWhere('email', $validated['login_credentials'])
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login_credentials' => 'The provided credentials are incorrect.',
            ]);
        }

        // Generate or regenerate API token
        $apiToken = Str::random(80);
        $user->update(['api_token' => $apiToken]);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ],
            'token' => $apiToken,
        ], 200);
    }
}
