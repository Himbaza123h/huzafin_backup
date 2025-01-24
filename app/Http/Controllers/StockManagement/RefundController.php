<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Sale;
use App\Models\Stock;
use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RefundController extends ApiController
{
    public function index(): JsonResponse
    {
        try {
            $refunds = Refund::with(['sale.product', 'sale.customer'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return $this->successResponse([
                'refunds' => $refunds
            ], 'Refunds retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Failed to retrieve refunds', 500);
        }
    }


    public function getSalesBySaleCode($sale_code): JsonResponse
        {
            try {

                $sales = Sale::with(['product:id,name,unit_price', 'customer:id,name,email'])
                ->select('id', 'sale_code', 'product_id', 'quantity', 'sale_price', 'sale_date', 'status')
                ->where('sale_code', $sale_code)
                ->where('status', 'completed') 
                ->get();

                if ($sales->isEmpty()) {
                    return $this->errorResponse('', 'No sales found with this code', 404);
                }

                return $this->successResponse([
                    'sales' => $sales,
                    'total_items' => $sales->sum('quantity'),
                    'total_amount' => $sales->sum('sale_price'),
                    'total_refundable_items' => $sales->sum('refundable_quantity')
                ], 'Sales retrieved successfully');
            } catch (Exception $e) {
                return $this->errorResponse(
                    config('app.debug') ? $e->getMessage() : '',
                    'Failed to retrieve sales', 
                    500
                );
            }
        }





public function refund(Request $request, $sale_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate input
            $validator = Validator::make($request->merge(['sale_code' => $sale_code])->all(), [
                'sale_code' => 'required|string|exists:sales,sale_code',
                'products' => 'required|array|min:1',
                'products.*.product_id' => 'required|uuid|exists:products,id',
                'products.*.quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            // Find sales with the given sale code and ensure they are complete
            $existingSales = Sale::where('sale_code', $request->sale_code)
                ->where('status', 'complete')
                ->get();

            if ($existingSales->isEmpty()) {
                return $this->errorResponse(
                    '',
                    'No complete sales found with the given sale code',
                    404
                );
            }

            $refundedSales = [];
            $totalRefundAmount = 0;

            // Process refund for each product
            foreach ($request->products as $productRefund) {
                $sale = $existingSales->first(function ($sale) use ($productRefund) {
                    return $sale->product_id === $productRefund['product_id'];
                });

                if (!$sale) {
                    return $this->errorResponse(
                        '',
                        "Product {$productRefund['product_id']} not found in the original sale",
                        422
                    );
                }

                // Check if refund quantity is valid
                if ($productRefund['quantity'] > $sale->quantity) {
                    return $this->errorResponse(
                        '',
                        "Refund quantity exceeds original sale quantity for product {$productRefund['product_id']}",
                        422
                    );
                }

                // Update the current sale record for refund
                $sale->update([
                    'quantity' => $sale->quantity - $productRefund['quantity'],
                    'status' => $sale->quantity == $productRefund['quantity'] ? 'refunded' : 'partially_refunded'
                ]);

                // Update stock
                $stock = Stock::where('product_id', $sale->product_id)->first();
                if ($stock) {
                    $stock->quantity += $productRefund['quantity'];
                    $stock->save();
                }

                $refundedSales[] = $sale;
                $totalRefundAmount += $sale->sale_price * $productRefund['quantity'];
            }

            DB::commit();

            return $this->successResponse(
                [
                    'sale_code' => $request->sale_code,
                    'refunded_sales' => $refundedSales,
                    'total_refund_amount' => $totalRefundAmount
                ],
                'Refund processed successfully',
                200
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(
                config('app.debug') ? $e->getMessage() : null,
                'Failed to process refund',
                500
            );
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'sale_id' => 'required|uuid|exists:sales,id',
                'quantity' => 'required|integer|min:1',
                'refund_amount' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            $sale = Sale::findOrFail($request->sale_id);

            // Verify sale is approved
            if ($sale->status !== 'approved') {
                return $this->errorResponse('', 'Cannot refund non-approved sale', 422);
            }

            // Calculate remaining refundable quantity
            $refundedQuantity = Refund::where('sale_id', $sale->id)
                ->whereIn('status', ['pending', 'approved'])
                ->sum('quantity');
            
            $remainingQuantity = $sale->quantity - $refundedQuantity;

            if ($request->quantity > $remainingQuantity) {
                return $this->errorResponse('', 'Requested quantity exceeds refundable quantity', 422);
            }

            $refund = Refund::create([
                'id' => Str::uuid(),
                'refund_code' => 'REF-' . strtoupper(Str::random(8)),
                'sale_id' => $sale->id,
                'product_id' => $sale->product_id,
                'user_id' => auth()->id(),
                'quantity' => $request->quantity,
                'refund_amount' => $request->refund_amount,
                'refund_date' => now(),
                'status' => 'pending',
                'notes' => $request->notes ?? null
            ]);

            DB::commit();
            
            return $this->successResponse([
                'refund' => $refund->load(['sale.product', 'sale.customer'])
            ], 'Refund created successfully', 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to create refund', 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $refund = Refund::with(['sale.product', 'sale.customer', 'user'])
                ->findOrFail($id);

            return $this->successResponse([
                'refund' => $refund
            ], 'Refund retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Refund not found', 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $refund = Refund::findOrFail($id);

            if ($refund->status !== 'pending') {
                return $this->errorResponse('', 'Cannot update non-pending refund', 422);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'sometimes|integer|min:1',
                'refund_amount' => 'sometimes|numeric|min:0',
                'notes' => 'sometimes|string|nullable'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            if ($request->has('quantity')) {
                $sale = Sale::findOrFail($refund->sale_id);
                $otherRefundsQuantity = Refund::where('sale_id', $sale->id)
                    ->where('id', '!=', $refund->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->sum('quantity');
                
                $remainingQuantity = $sale->quantity - $otherRefundsQuantity;

                if ($request->quantity > $remainingQuantity) {
                    return $this->errorResponse('', 'Requested quantity exceeds refundable quantity', 422);
                }
            }

            $refund->update($request->only(['quantity', 'refund_amount', 'notes']));

            DB::commit();

            return $this->successResponse([
                'refund' => $refund->load(['sale.product', 'sale.customer'])
            ], 'Refund updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to update refund', 500);
        }
    }

    public function approve($sale_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $refund = Sale::where('sale_code', $sale_code)
                ->where('status', 'pending')
                ->firstOrFail();

            $sale = Sale::findOrFail($refund->sale_id);
            
            // Update stock
            $stock = Stock::where('product_id', $refund->product_id)->firstOrFail();
            $stock->quantity += $refund->quantity;
            $stock->save();

            // Update total refunded amount for sale
            $totalRefundedQuantity = Refund::where('sale_id', $sale->id)
                ->where('status', 'approved')
                ->sum('quantity') + $refund->quantity;

            // Check if entire sale is refunded
            if ($totalRefundedQuantity >= $sale->quantity) {
                $sale->status = 'refunded';
                $sale->save();
            }

            $refund->status = 'approved';
            $refund->approved_by = auth()->id();
            $refund->approved_at = now();
            $refund->save();

            DB::commit();

            return $this->successResponse([
                'refund' => $refund->load(['sale.product', 'sale.customer'])
            ], 'Refund approved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to approve refund', 500);
        }
    }

    public function reject(Request $request, $refund_code): JsonResponse
    {
        try {
            $refund = Refund::where('refund_code', $refund_code)
                ->where('status', 'pending')
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            $refund->status = 'rejected';
            $refund->rejected_by = auth()->id();
            $refund->rejected_at = now();
            $refund->rejection_reason = $request->rejection_reason;
            $refund->save();

            return $this->successResponse([
                'refund' => $refund->load(['sale.product', 'sale.customer'])
            ], 'Refund rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Failed to reject refund', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $refund = Refund::findOrFail($id);

            if ($refund->status !== 'pending') {
                return $this->errorResponse('', 'Cannot delete non-pending refund', 422);
            }

            $refund->delete();

            DB::commit();

            return $this->successResponse([], 'Refund deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to delete refund', 500);
        }
    }

    public function bulkApprove(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'refund_codes' => 'required|array',
                'refund_codes.*' => 'required|string|exists:refunds,refund_code'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            $refunds = Refund::whereIn('refund_code', $request->refund_codes)
                ->where('status', 'pending')
                ->get();

            foreach ($refunds as $refund) {
                $sale = Sale::findOrFail($refund->sale_id);
                
                // Update stock
                $stock = Stock::where('product_id', $refund->product_id)->firstOrFail();
                $stock->quantity += $refund->quantity;
                $stock->save();

                // Calculate total refunded quantity
                $totalRefundedQuantity = Refund::where('sale_id', $sale->id)
                    ->where('status', 'approved')
                    ->sum('quantity') + $refund->quantity;

                if ($totalRefundedQuantity >= $sale->quantity) {
                    $sale->status = 'refunded';
                    $sale->save();
                }

                $refund->status = 'approved';
                $refund->approved_by = auth()->id();
                $refund->approved_at = now();
                $refund->save();
            }

            DB::commit();

            return $this->successResponse([
                'refunds' => $refunds->load(['sale.product', 'sale.customer'])
            ], 'Refunds approved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to approve refunds', 500);
        }
    }

    public function bulkReject(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'refund_codes' => 'required|array',
                'refund_codes.*' => 'required|string|exists:refunds,refund_code',
                'rejection_reason' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            $refunds = Refund::whereIn('refund_code', $request->refund_codes)
                ->where('status', 'pending')
                ->get();

            foreach ($refunds as $refund) {
                $refund->status = 'rejected';
                $refund->rejected_by = auth()->id();
                $refund->rejected_at = now();
                $refund->rejection_reason = $request->rejection_reason;
                $refund->save();
            }

            DB::commit();

            return $this->successResponse([
                'refunds' => $refunds->load(['sale.product', 'sale.customer'])
            ], 'Refunds rejected successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Failed to reject refunds', 500);
        }
    }
}