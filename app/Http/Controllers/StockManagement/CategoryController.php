<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class CategoryController extends Controller
{
    /**
     * Get all categories with specific fields
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::select('id', 'name', 'description')->get();

            return response()->json([
                'status' => 'success',
                'data' => $categories
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching categories: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories'
            ], 500);
        }
    }

    /**
     * Get a specific category
     */
    public function show($id): JsonResponse
    {
        try {
            $category = Category::select('id', 'name', 'description')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $category
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found'
            ], 404);
        }
    }

    /**
     * Create a new category
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category = Category::create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $category->only(['id', 'name', 'description'])
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error creating category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category'
            ], 500);
        }
    }

    /**
     * Update an existing category
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => "required|string|max:255|unique:categories,name,{$id}",
                'description' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $category->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => $category->only(['id', 'name', 'description'])
            ]);
        } catch (QueryException $e) {
            Log::error('Database error updating category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update category due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error updating category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found or failed to update'
            ], 404);
        }
    }

    /**
     * Soft delete a category
     */
    public function destroy($id): JsonResponse
    {
        try {
            $category = Category::findOrFail($id);

            // Check if category has any related records
            if ($category->products()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete category with associated products'
                ], 422);
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category'
            ], 500);
        }
    }
}
