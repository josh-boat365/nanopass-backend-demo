<?php

namespace App\Http\Controllers;

use App\Models\Privilege;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class PrivilegeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $privileges = Privilege::all();
            return response()->json([
                'message' => 'Privileges retrieved successfully',
                'data' => $privileges,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving privileges: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve privileges',
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
            'priv_id' => 'required|integer|unique:privileges,priv_id',
            'name' => 'required|string|min:2|max:255|unique:privileges,name',
            'description' => 'nullable|string|max:1000',
        ], [
            'priv_id.required' => 'Privilege ID is required.',
            'priv_id.integer' => 'Privilege ID must be a number.',
            'priv_id.unique' => 'This Privilege ID already exists.',
            'name.required' => 'Privilege name is required.',
            'name.unique' => 'This privilege name already exists.',
            'name.min' => 'Privilege name must be at least 2 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ]);

        try {
            // Create the privilege
            $privilege = Privilege::create([
                'priv_id' => $validated['priv_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            return response()->json([
                'message' => 'Privilege created successfully',
                'data' => $privilege,
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error during privilege creation: ' . $e->getMessage());
            return response()->json([
                'message' => 'Privilege creation failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Privilege creation error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Privilege creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Privilege $privilege)
    {
        try {
            return response()->json([
                'message' => 'Privilege retrieved successfully',
                'data' => $privilege,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving privilege: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve privilege',
                'error' => 'An unexpected error occurred',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Privilege $privilege)
    {
        // Validate input
        $validated = $request->validate([
            'priv_id' => 'sometimes|integer|unique:privileges,priv_id,' . $privilege->id,
            'name' => 'sometimes|string|min:2|max:255|unique:privileges,name,' . $privilege->id,
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $privilege->update($validated);

            return response()->json([
                'message' => 'Privilege updated successfully',
                'data' => $privilege,
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during privilege update: ' . $e->getMessage());
            return response()->json([
                'message' => 'Privilege update failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Privilege update error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Privilege update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Privilege $privilege)
    {
        try {
            $privilege->delete();

            return response()->json([
                'message' => 'Privilege deleted successfully',
            ], 200);
        } catch (QueryException $e) {
            Log::error('Database error during privilege deletion: ' . $e->getMessage());
            return response()->json([
                'message' => 'Privilege deletion failed',
                'error' => 'A database error occurred',
            ], 500);
        } catch (\Exception $e) {
            Log::error('Privilege deletion error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Privilege deletion failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
