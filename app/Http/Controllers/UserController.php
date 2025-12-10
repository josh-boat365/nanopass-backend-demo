<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = User::with('privilege', 'systemPasswords')->get();
            return response()->json([
                'message' => 'Users retrieved successfully',
                'data' => $users,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving users: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve users',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:255|unique:users,username|regex:/^[a-zA-Z0-9_-]+$/',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'sometimes|boolean',
            'privilege_id' => 'nullable|exists:privileges,id',
            'system_passwords' => 'nullable|array',
            'system_passwords.*' => 'integer|exists:system_passwords,id',
        ], [
            'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens.',
            'password.confirmed' => 'Password confirmation does not match.',
            'privilege_id.exists' => 'The selected privilege does not exist.',
            'system_passwords.*.exists' => 'One or more selected system passwords do not exist.',
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
                'is_admin' => $validated['is_admin'] ?? false,
                'privilege_id' => $validated['privilege_id'] ?? null,
            ]);

            // Attach system passwords if provided
            if (!empty($validated['system_passwords'])) {
                $user->systemPasswords()->attach($validated['system_passwords']);
            }

            return response()->json([
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'privilege_id' => $user->privilege_id,
                ],
                'system_passwords' => $user->systemPasswords()->pluck('id')->toArray(),
                'token' => $apiToken,
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during user creation: ' . $e->getMessage());
            return response()->json([
                'message' => 'User creation failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('User creation error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'User creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        try {
            return response()->json([
                'message' => 'User retrieved successfully',
                'data' => $user->load('privilege', 'systemPasswords'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving user: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve user',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validate input
        $validated = $request->validate([
            'username' => 'sometimes|string|min:3|max:255|unique:users,username,' . $user->id . '|regex:/^[a-zA-Z0-9_-]+$/',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8|confirmed',
            'is_admin' => 'sometimes|boolean',
            'privilege_id' => 'nullable|exists:privileges,id',
            'system_passwords' => 'nullable|array',
            'system_passwords.*' => 'integer|exists:system_passwords,id',
        ], [
            'username.regex' => 'Username can only contain letters, numbers, underscores, and hyphens.',
            'password.confirmed' => 'Password confirmation does not match.',
            'privilege_id.exists' => 'The selected privilege does not exist.',
            'system_passwords.*.exists' => 'One or more selected system passwords do not exist.',
        ]);

        try {
            // Update user fields
            $updateData = [];
            if (isset($validated['username'])) {
                $updateData['username'] = $validated['username'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }
            if (isset($validated['is_admin'])) {
                $updateData['is_admin'] = $validated['is_admin'];
            }
            if (isset($validated['privilege_id'])) {
                $updateData['privilege_id'] = $validated['privilege_id'];
            }

            $user->update($updateData);

            // Update system passwords if provided
            if (isset($validated['system_passwords'])) {
                $user->systemPasswords()->sync($validated['system_passwords']);
            }

            return response()->json([
                'message' => 'User updated successfully',
                'data' => $user->load('privilege', 'systemPasswords'),
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during user update: ' . $e->getMessage());
            return response()->json([
                'message' => 'User update failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('User update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'User update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Detach all system passwords before deleting
            $user->systemPasswords()->detach();

            // Delete the user
            $user->delete();

            return response()->json([
                'message' => 'User deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during user deletion: ' . $e->getMessage());
            return response()->json([
                'message' => 'User deletion failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('User deletion error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'User deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
