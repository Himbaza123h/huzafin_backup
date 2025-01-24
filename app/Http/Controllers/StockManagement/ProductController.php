<?php

namespace App\Http\Controllers\StockManagement;

use App\Models\Product;
use App\Models\Report;
use App\Models\Stock;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Product::select([
                'products.id',
                'products.entry_code',
                'products.category_id',
                'products.name',
                'products.track_stock',
                'products.opening_stock',
                'products.unit_price',
                'products.purchase_price',
                'products.description',
                'products.status',
                'products.created_at',
                DB::raw('COALESCE(stock.quantity, 0) as stock_quantity') 
            ])
            ->leftJoin('stock', 'products.id', '=', 'stock.product_id');

            // Add status filter if provided
            if ($request->has('status')) {
                $query->where('products.status', $request->status);
            }

            $products = $query
                ->with('category')
                ->orderBy('products.created_at', 'desc')
                ->paginate(8);

            return $this->successResponse(['products' => $products]);
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred while retrieving products', 500);
        }
    }


    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                'products' => 'required|array|min:1',
                'products.*.category_id' => 'required|exists:categories,id',
                'products.*.name' => 'required|string|max:255|unique:products,name,NULL,id,deleted_at,NULL',
                'products.*.track_stock' => 'required|boolean',
                'products.*.opening_stock' => 'required_if:products.*.track_stock,true|nullable|integer|min:0',
                'products.*.unit_price' => [
                    'required', 
                    'numeric', 
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        $index = explode('.', $attribute)[1];
                        $purchasePrice = $request->input("products.{$index}.purchase_price");
                        
                        if ($value < $purchasePrice) {
                            $fail('Unit price must be greater than or equal to purchase price');
                        }
                    }
                ],
                'products.*.purchase_price' => 'required|numeric|min:0',
                'products.*.description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation errors in your request', 422);
            }

            $entry_code = $request->entry_code ?? 'PRD-' . strtoupper(Str::random(8));
            $products = [];

            foreach ($request->products as $productData) {
                $product = new Product();
                $product->forceFill([
                    'entry_code' => $entry_code,
                    'name' => $productData['name'],
                    'category_id' => $productData['category_id'],
                    'track_stock' => $productData['track_stock'],
                    'opening_stock' => $productData['track_stock'] ? $productData['opening_stock'] : 0,
                    'unit_price' => $productData['unit_price'],
                    'purchase_price' => $productData['purchase_price'],
                    'description' => $productData['description'] ?? null,
                    'status' => 'pending',
                ]);

                $product->save();
                $products[] = $product->fresh();
            }

            DB::commit();
            return $this->successResponse(
                [
                    'products' => $products,
                    'entry_code' => $entry_code,
                ],
                'Products created successfully',
                201,
            );
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while creating the products', 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $product = Product::with('stock')->findOrFail($id);
            return $this->successResponse(['product' => $product], 'Product retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Product not found', 404);
        }
    }


    public function update(Request $request, string $id): JsonResponse
{
    try {
        DB::beginTransaction();

        $product = Product::with('stock')->findOrFail($id);

        if ($product->status == 'rejected') {
            return $this->errorResponse('', 'Cannot update non-pending product', 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:products,name,' . $id . ',id,deleted_at,NULL',
            'category_id' => 'required|exists:categories,id',
            'track_stock' => 'sometimes|boolean',
            'opening_stock' => 'required_if:track_stock,true|nullable|integer|min:0',
            'unit_price' => [
                'sometimes',
                'required',
                'numeric', 
                'min:0',
                function ($attribute, $value, $fail) {
                    $purchasePrice = request('purchase_price');
                    
                    if ($purchasePrice !== null && $value < $purchasePrice) {
                        $fail('Unit price must be greater than or equal to purchase price');
                    }
                }
            ],
            'purchase_price' => 'sometimes|required|numeric|min:0',
            'description' => 'sometimes|nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation errors in your request', 422);
        }

        $product->update($request->only(['name', 'category_id', 'track_stock', 'opening_stock', 'unit_price', 'purchase_price', 'description']));

        DB::commit();
        return $this->successResponse(['product' => $product->fresh('stock')], 'Product updated successfully');
    } catch (Exception $e) {
        DB::rollBack();
        return $this->errorResponse('', 'An error occurred while updating the product', 500);
    }
}

    public function approve(string $entry_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $products = Product::where('entry_code', $entry_code)->where('status', 'pending')->get();

            if ($products->isEmpty()) {
                return $this->errorResponse('', 'No pending products found with this entry code', 404);
            }

            foreach ($products as $product) {
                $product->status = 'approved';
                $product->save();

                // Create stock record for products with track_stock enabled
                if ($product->track_stock) {
                    Stock::create([
                        'id' => Str::uuid(),
                        'product_id' => $product->id,
                        'quantity' => $product->opening_stock,
                        'opening_stock' => $product->opening_stock,
                    ]);
                }

                Report::create([
                    'report_name' => 'Product Entry Approval - ' . $product->name,
                    'report_type' => 'entry',
                    'product_id' => $product->id,
                    'user_id' => Auth::id(),
                    'additional_notes' => [
                        'entry_code' => $entry_code,
                        'opening_stock' => $product->opening_stock,
                        'unit_price' => $product->unit_price,
                        'total_amount' => $product->opening_stock * $product->unit_price,
                        'entry_date' => $product->created_at->toDateString(),
                    ],
                ]);
            }

            DB::commit();
            return $this->successResponse(['products' => $products], 'Products approved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while approving products: ' . $e->getMessage(), 500);
        }
    }

    public function reject(string $entry_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $products = Product::where('entry_code', $entry_code)->where('status', 'pending')->get();

            if ($products->isEmpty()) {
                return $this->errorResponse('', 'No pending products found with this entry code', 404);
            }

            foreach ($products as $product) {
                $product->status = 'rejected';
                $product->save();
            }

            DB::commit();
            return $this->successResponse(['products' => $products], 'Products rejected successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while rejecting products', 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = Product::findOrFail($id);

            if ($product->status !== 'pending') {
                return $this->errorResponse('', 'Cannot delete non-pending product', 422);
            }

            // Check if product has any related transactions
            $hasTransactions = $product->purchases()->exists() || $product->sales()->exists() || $product->refunds()->exists();

            if ($hasTransactions) {
                return $this->errorResponse('', 'Cannot delete product with existing transactions', 422);
            }

            $product->delete();

            DB::commit();
            return $this->successResponse([], 'Product deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while deleting the product', 500);
        }
    }

    public function deleteAll(string $entry_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $products = Product::where('entry_code', $entry_code)->where('status', 'pending')->get();

            if ($products->isEmpty()) {
                return $this->errorResponse('', 'No pending products found with this entry code', 404);
            }

            // Check for transactions
            foreach ($products as $product) {
                $hasTransactions = $product->purchases()->exists() || $product->sales()->exists() || $product->refunds()->exists();

                if ($hasTransactions) {
                    return $this->errorResponse('', "Cannot delete product {$product->name} with existing transactions", 422);
                }
            }

            foreach ($products as $product) {
                $product->delete();
            }

            DB::commit();
            return $this->successResponse([], 'Products deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while deleting products', 500);
        }
    }
}
