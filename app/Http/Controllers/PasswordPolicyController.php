<?php

namespace App\Http\Controllers;

use App\Models\PasswordPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class PasswordPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $policies = PasswordPolicy::with('categories')->get();
            return response()->json([
                'message' => 'Password policies retrieved successfully',
                'data' => $policies,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving password policies: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve password policies',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255|unique:password_policies,name',
            'description' => 'nullable|string|max:1000',
            'regex_pattern' => 'required|string|max:1000',
            'expiration' => 'nullable|integer|min:1',
        ], [
            'name.required' => 'Policy name is required.',
            'name.min' => 'Policy name must be at least 2 characters.',
            'name.unique' => 'This policy name already exists.',
            'regex_pattern.required' => 'Regex pattern is required.',
            'regex_pattern.max' => 'Regex pattern cannot exceed 1000 characters.',
            'expiration.integer' => 'Expiration must be a number.',
            'expiration.min' => 'Expiration must be at least 1.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        try {
            // Create the policy
            $policy = PasswordPolicy::create($validated);

            return response()->json([
                'message' => 'Password policy created successfully',
                'data' => $policy,
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during policy creation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Policy creation failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Policy creation error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Policy creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordPolicy $passwordPolicy)
    {
        try {
            return response()->json([
                'message' => 'Password policy retrieved successfully',
                'data' => $passwordPolicy->load('categories'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving password policy: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve password policy',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PasswordPolicy $passwordPolicy)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:255|unique:password_policies,name,' . $passwordPolicy->id,
            'description' => 'nullable|string|max:1000',
            'regex_pattern' => 'sometimes|string|max:1000',
            'expiration' => 'nullable|integer|min:1',
        ]);

        try {
            $passwordPolicy->update($validated);

            return response()->json([
                'message' => 'Password policy updated successfully',
                'data' => $passwordPolicy,
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during policy update: ' . $e->getMessage());
            return response()->json([
                'message' => 'Policy update failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Policy update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Policy update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordPolicy $passwordPolicy)
    {
        try {
            $passwordPolicy->delete();

            return response()->json([
                'message' => 'Password policy deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during policy deletion: ' . $e->getMessage());
            return response()->json([
                'message' => 'Policy deletion failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Policy deletion error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Policy deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
