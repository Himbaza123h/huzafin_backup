<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class CustomerController extends Controller
{
    /**
     * Get all customers with specific fields
     */
    public function index(): JsonResponse
    {
        try {
            $customers = Customer::select('id', 'name', 'email', 'phone_contact', 'address', 'tin_number')->get();

            return response()->json([
                'status' => 'success',
                'data' => $customers
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching customers: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch customers'
            ], 500);
        }
    }

    /**
     * Get a specific customer
     */
    public function show($id): JsonResponse
    {
        try {
            $customer = Customer::select('id', 'name', 'email', 'phone_contact', 'address', 'tin_number')->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $customer
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }
    }

    /**
     * Create a new customer
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:customers,email',
                'phone_contact' => 'required|string|max:20',
                'address' => 'nullable|string|max:500',
                'tin_number' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer = Customer::create($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully',
                'data' => $customer
            ], 201);
        } catch (QueryException $e) {
            Log::error('Database error creating customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer'
            ], 500);
        }
    }

    /**
     * Update an existing customer
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => "required|email|max:255|unique:customers,email,{$id}",
                'phone_contact' => 'required|string|max:20',
                'address' => 'nullable|string|max:500',
                'tin_number' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $customer->update($validator->validated());

            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
                'data' => $customer
            ]);
        } catch (QueryException $e) {
            Log::error('Database error updating customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customer due to database error'
            ], 500);
        } catch (Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found or failed to update'
            ], 404);
        }
    }

    /**
     * Soft delete a customer
     */
    public function destroy($id): JsonResponse
    {
        try {
            $customer = Customer::findOrFail($id);

            if ($customer->sales()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete customer with associated sales'
                ], 422);
            }

            $customer->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully'
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customer'
            ], 500);
        }
    }
}
