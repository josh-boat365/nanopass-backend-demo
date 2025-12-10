<?php

namespace App\Http\Controllers;

use App\Models\PasswordCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class PasswordCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $categories = PasswordCategory::with('policy', 'passwords')->get();
            return response()->json([
                'message' => 'Password categories retrieved successfully',
                'data' => $categories,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving password categories: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve password categories',
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
            'name' => 'required|string|min:2|max:255|unique:passwords_category,name',
            'description' => 'nullable|string|max:1000',
            'password_policy_id' => 'required|integer|exists:password_policies,id',
        ], [
            'name.required' => 'Category name is required.',
            'name.min' => 'Category name must be at least 2 characters.',
            'name.unique' => 'This category name already exists.',
            'password_policy_id.required' => 'Password policy is required.',
            'password_policy_id.exists' => 'The selected password policy does not exist.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        try {
            // Create the category
            $category = PasswordCategory::create($validated);

            return response()->json([
                'message' => 'Password category created successfully',
                'data' => $category->load('policy'),
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during category creation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Category creation failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Category creation error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Category creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordCategory $passwordCategory)
    {
        try {
            return response()->json([
                'message' => 'Password category retrieved successfully',
                'data' => $passwordCategory->load('policy', 'passwords'),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving password category: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve password category',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PasswordCategory $passwordCategory)
    {
        // Validate input
        $validated = $request->validate([
            'name' => 'sometimes|string|min:2|max:255|unique:passwords_category,name,' . $passwordCategory->id,
            'description' => 'nullable|string|max:1000',
            'password_policy_id' => 'sometimes|integer|exists:password_policies,id',
        ]);

        try {
            $passwordCategory->update($validated);

            return response()->json([
                'message' => 'Password category updated successfully',
                'data' => $passwordCategory->load('policy'),
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during category update: ' . $e->getMessage());
            return response()->json([
                'message' => 'Category update failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Category update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Category update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordCategory $passwordCategory)
    {
        try {
            $passwordCategory->delete();

            return response()->json([
                'message' => 'Password category deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during category deletion: ' . $e->getMessage());
            return response()->json([
                'message' => 'Category deletion failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Category deletion error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Category deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
