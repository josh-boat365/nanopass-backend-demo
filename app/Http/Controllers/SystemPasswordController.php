<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemPassword;
use App\Models\PasswordCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;

class SystemPasswordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $systemPasswords = SystemPassword::with('category')->get();
            return response()->json([
                'message' => 'System passwords retrieved successfully',
                'data' => $systemPasswords,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving system passwords: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve system passwords',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // First validate basic input
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:8',
            'description' => 'nullable|string|max:1000',
            'passwords_category_id' => 'required|integer|exists:passwords_category,id',
        ], [
            'name.required' => 'System password name is required.',
            'name.min' => 'System password name must be at least 2 characters.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'passwords_category_id.required' => 'Category is required.',
            'passwords_category_id.exists' => 'The selected category does not exist.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        try {
            // Get the category with its policy
            $category = PasswordCategory::with('policy')->findOrFail($validated['passwords_category_id']);

            // Validate password against category's policy regex pattern if policy exists
            if ($category->policy && $category->policy->regex_pattern) {
                if (!preg_match('/' . $category->policy->regex_pattern . '/', $validated['password'])) {
                    return response()->json([
                        'message' => 'Password validation failed: ' . $category->policy->description,
                        'error' => 'Password does not meet the requirements of the selected category policy: ' . $category->policy->name,
                    ], 422);
                }
            }

            // Hash the password
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);

            // Create the system password
            $systemPassword = SystemPassword::create($validated);

            return response()->json([
                'message' => 'System password created successfully',
                'data' => $systemPassword->load('category'),
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during system password creation: ' . $e->getMessage());
            return response()->json([
                'message' => 'System password creation failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('System password creation error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'System password creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SystemPassword $systemPassword)
    {
        try {
            return response()->json([
                'message' => 'System password retrieved successfully',
                'data' => $systemPassword->load('category'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving system password: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve system password',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SystemPassword $systemPassword)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:255',
            'password' => 'sometimes|string|min:8',
            'description' => 'nullable|string|max:1000',
            'passwords_category_id' => 'sometimes|integer|exists:passwords_category,id',
        ]);

        try {
            // If password is being updated, validate against the category's policy
            if (isset($validated['password'])) {
                // Use the new category if provided, otherwise use the current one
                $categoryId = $validated['passwords_category_id'] ?? $systemPassword->passwords_category_id;
                $category = PasswordCategory::with('policy')->findOrFail($categoryId);

                // Validate password against category's policy regex pattern if policy exists
                if ($category->policy && $category->policy->regex_pattern) {
                    if (!preg_match('/' . $category->policy->regex_pattern . '/', $validated['password'])) {
                        return response()->json([
                            'message' => 'Password validation failed: '. $category->policy->description,
                            'error' => 'Password does not meet the requirements of the selected category policy: ' . $category->policy->name,
                        ], 422);
                    }
                }

                $validated['password_hash'] = Hash::make($validated['password']);
                unset($validated['password']);
            }

            $systemPassword->update($validated);

            return response()->json([
                'message' => 'System password updated successfully',
                'data' => $systemPassword->load('category'),
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during system password update: ' . $e->getMessage());
            return response()->json([
                'message' => 'System password update failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('System password update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'System password update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemPassword $systemPassword)
    {
        try {
            $systemPassword->delete();

            return response()->json([
                'message' => 'System password deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during system password deletion: ' . $e->getMessage());
            return response()->json([
                'message' => 'System password deletion failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('System password deletion error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'System password deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
