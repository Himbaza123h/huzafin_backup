<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\StkInvoice;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class InvoiceManagementController extends Controller
{
    public function purchase($purchase_code): JsonResponse
    {
        try {
            $purchase = Purchase::where('purchase_code', $purchase_code)
                ->with(['supplier', 'product', 'user'])
                ->first();

            if (!$purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found'
                ], 404);
            }

            // Create or retrieve invoice
            $invoice = StkInvoice::firstOrCreate(
                [
                    'purchase_id' => $purchase->id,
                    'type' => 'purchase'
                ],
                [
                    'invoice_number' => 'INV-P-' . strtoupper(Str::random(8)),
                    'user_id' => auth()->id(),
                    'entity_id' => $purchase->supplier_id,
                    'total_amount' => $purchase->quantity * $purchase->purchase_price,
                    'status' => 'final'
                ]
            );

            $invoice->increment('download_count');

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => $invoice,
                    'purchase' => $purchase,
                    'supplier' => $purchase->supplier,
                    'created_by' => $purchase->user,
                    'products' => [
                        [
                            'name' => $purchase->product->name,
                            'quantity' => $purchase->quantity,
                            'price' => $purchase->purchase_price,
                            'total' => $purchase->quantity * $purchase->purchase_price
                        ]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the invoice', $e->getMessage()
            ], 500);
        }
    }

    public function sale($sale_code): JsonResponse
    {
        try {
            $sale = Sale::where('sale_code', $sale_code)
                ->with(['customer', 'product', 'user'])
                ->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sale not found'
                ], 404);
            }

            // Create or retrieve invoice
            $invoice = StkInvoice::firstOrCreate(
                [
                    'sale_id' => $sale->id,
                    'type' => 'sale'
                ],
                [
                    'invoice_number' => 'INV-S-' . strtoupper(Str::random(8)),
                    'user_id' => auth()->id(),
                    'entity_id' => $sale->customer_id,
                    'total_amount' => $sale->quantity * $sale->unit_price,
                    'status' => 'final'
                ]
            );

            $invoice->increment('download_count');

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice' => $invoice,
                    'sale' => $sale,
                    'customer' => $sale->customer,
                    'created_by' => $sale->user,
                    'products' => [
                        [
                            'name' => $sale->product->name,
                            'quantity' => $sale->quantity,
                            'price' => $sale->unit_price,
                            'total' => $sale->quantity * $sale->unit_price
                        ]
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the invoice'
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'type' => 'required|in:purchase,sale',
                'purchase_id' => 'required_if:type,purchase|uuid|exists:purchases,id',
                'sale_id' => 'required_if:type,sale|uuid|exists:sales,id',
                'notes' => 'nullable|string',
                'status' => 'required|in:draft,final,void'
            ]);

            // Get the related transaction
            $transaction = $validated['type'] === 'purchase'
                ? Purchase::find($validated['purchase_id'])
                : Sale::find($validated['sale_id']);

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Related transaction not found'
                ], 404);
            }

            $invoice = StkInvoice::create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'type' => $validated['type'],
                'user_id' => auth()->id(),
                'entity_id' => $validated['type'] === 'purchase'
                    ? $transaction->supplier_id
                    : $transaction->customer_id,
                'purchase_id' => $validated['purchase_id'] ?? null,
                'sale_id' => $validated['sale_id'] ?? null,
                'total_amount' => $transaction->quantity * ($validated['type'] === 'purchase'
                    ? $transaction->purchase_price
                    : $transaction->unit_price),
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => ['invoice' => $invoice]
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the invoice'
            ], 500);
        }
    }
}
