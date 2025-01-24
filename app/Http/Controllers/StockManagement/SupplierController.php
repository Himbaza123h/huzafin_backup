<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class SupplierController extends Controller
{
    /**
     * Get all suppliers
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers = Supplier::select('id', 'name', 'email', 'phone_contact', 'address', 'tin_number')->get();

            return response()->json([
                'status' => 'success',
                'data' => $suppliers
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching suppliers: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch suppliers'
            ], 500);
        }
    }

    /**
     * Get a specific supplier
     */
    public function show($id): JsonResponse
    {
        try {
            $supplier = Supplier::select('id', 'name', 'email', 'phone_contact', 'address', 'tin_number')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $supplier
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }
    }

    /**
     * Create a new supplier
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255|unique:suppliers,email',
                'phone_contact' => 'required|string|max:20|unique:suppliers,phone_contact',
                'address' => 'nullable|string|max:255',
                'tin_number' => 'nullable|string|max:20|unique:suppliers,tin_number'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplier = Supplier::create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier created successfully',
                'data' => $supplier->only(['id', 'name', 'email', 'phone_contact', 'address', 'tin_number'])
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error creating supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create supplier due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create supplier'
            ], 500);
        }
    }

    /**
     * Update an existing supplier
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => "nullable|email|max:255|unique:suppliers,email,{$id}",
                'phone_contact' => "required|string|max:20|unique:suppliers,phone_contact,{$id}",
                'address' => 'nullable|string|max:255',
                'tin_number' => "nullable|string|max:20|unique:suppliers,tin_number,{$id}"
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $supplier->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier updated successfully',
                'data' => $supplier->only(['id', 'name', 'email', 'phone_contact', 'address', 'tin_number'])
            ]);
        } catch (QueryException $e) {
            Log::error('Database error updating supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update supplier due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Supplier not found or failed to update'
            ], 404);
        }
    }

    /**
     * Soft delete a supplier
     */
    public function destroy($id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);

            if ($supplier->purchases()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete supplier with associated purchases'
                ], 422);
            }

            $supplier->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Supplier deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete supplier'
            ], 500);
        }
    }
}
