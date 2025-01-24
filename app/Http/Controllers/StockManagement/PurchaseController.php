<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\ApiController;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Report;
use App\Models\Stock;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Purchase::with(['supplier', 'product'])
                ->orderBy('created_at', 'desc');
    
            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
    
            // Paginate the results
            $purchases = $query->paginate(8);
    
            return $this->successResponse(['purchases' => $purchases]);
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred', 500);
        }
    }
    

    public function show($id): JsonResponse
    {
        try {
            $purchase = Purchase::with(['supplier'])->findOrFail($id);
            return $this->successResponse(['purchase' => $purchase], 'Purchase retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Purchase not found', 404);
        }
    }

        public function store(Request $request): JsonResponse
        {
            try {
                DB::beginTransaction();
        
                $validator = Validator::make($request->all(), [
                    'products' => 'required|array|min:1',
                    'products.*.product_id' => 'nullable|uuid|exists:products,id',
                    'products.*.quantity' => 'required|integer|min:1',
                    'products.*.purchase_price' => [
                        'required',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) use ($request) {
                            $index = explode('.', $attribute)[1];
                            $productId = $request->input("products.{$index}.product_id");
                            $newProduct = $request->input("products.{$index}.new_product");
        
                            // If existing product, validate purchase price
                            if ($productId) {
                                $product = Product::find($productId);
                                if ($product && $value != $product->purchase_price) {
                                    $fail("Purchase price must match the product's original purchase price of {$product->purchase_price}");
                                }
                            }
                        }
                    ],
                    'products.*.new_product' => 'required_without:products.*.product_id|array',
                    'products.*.new_product.name' => 'required_with:products.*.new_product|string|max:255',
                    'products.*.new_product.category_id' => 'required_with:products.*.new_product|exists:categories,id',
                    'products.*.new_product.track_stock' => 'required_with:products.*.new_product|boolean',
                    'products.*.new_product.opening_stock' => 'required_if:products.*.new_product.track_stock,true|integer|min:0',
                    'products.*.new_product.unit_price' => 'required_with:products.*.new_product|numeric|min:0',
                    'products.*.new_product.description' => 'nullable|string',
                    'purchase_date' => 'nullable|date',
                    'supplier_id' => 'nullable|uuid|exists:suppliers,id',
                    'supplier' => 'required_without:supplier_id|array',
                    'supplier.name' => 'required_with:supplier|string|max:255',
                    'supplier.email' => 'nullable|email|max:255',
                    'supplier.phone_contact' => 'required_with:supplier|string|max:20',
                    'supplier.address' => 'nullable|string',
                    'supplier.tin_number' => 'nullable|string|max:50',
                ]);
        
                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors(), 'Validation error', 422);
                }
        
                // Handle supplier creation or use existing supplier
                $supplier_id = $request->supplier_id;
                if (!$supplier_id && $request->has('supplier')) {
                    $supplier = Supplier::create($request->supplier);
                    $supplier_id = $supplier->id;
                }
        
                // Generate a single purchase code for all products
                $purchase_code = 'PUR-' . strtoupper(Str::random(8));
                $purchases = [];
        
                // Create purchase records for each product
                foreach ($request->products as $product) {
                    $product_id = $product['product_id'] ?? null;
        
                    // Create new product if not exists
                    if (!$product_id && isset($product['new_product'])) {
                        $newProductData = $product['new_product'];
                        $newProduct = Product::create([
                            'id' => Str::uuid(),
                            'entry_code' => 'PRD-' . strtoupper(Str::random(8)),
                            'name' => $newProductData['name'],
                            'category_id' => $newProductData['category_id'],
                            'track_stock' => $newProductData['track_stock'],
                            'opening_stock' => $newProductData['track_stock'] ? $product['quantity'] : 0,
                            'unit_price' => $newProductData['unit_price'],
                            'purchase_price' => $product['purchase_price'],
                            'description' => $newProductData['description'] ?? null,
                            'status' => 'pending',
                        ]);
                        $product_id = $newProduct->id;
                    }
        
                    $purchase = Purchase::create([
                        'id' => Str::uuid(),
                        'purchase_code' => $purchase_code,
                        'product_id' => $product_id,
                        'supplier_id' => $supplier_id,
                        'user_id' => auth()->id(),
                        'quantity' => $product['quantity'],
                        'purchase_price' => $product['purchase_price'],
                        'purchase_date' => $request->purchase_date ?? Carbon::now()->toDateString(),
                        'status' => 'pending',
                    ]);
        
                    $purchases[] = $purchase;
                }
        
                DB::commit();
        
                return $this->successResponse(
                    [
                        'purchase_code' => $purchase_code,
                        'purchases' => Purchase::where('purchase_code', $purchase_code)->with(['supplier', 'product'])->get(),
                    ],
                    'Purchases created successfully',
                    201,
                );
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse($e->getMessage(), 'An error occurred while creating purchases', 500);
            }
        }


    public function editPurchase(Request $request, $purchase_code): JsonResponse
{
    try {
        DB::beginTransaction();

        // Find existing purchases with this code
        $existingPurchases = Purchase::where('purchase_code', $purchase_code)->get();

        if ($existingPurchases->isEmpty()) {
            return $this->errorResponse('', 'No purchases found with this code', 404);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'remove_products' => 'sometimes|array',
            'remove_products.*' => 'uuid|exists:purchases,product_id',
            'edit_products' => 'sometimes|array',
            'edit_products.*.id' => 'required|uuid|exists:purchases,product_id',
            'edit_products.*.quantity' => 'sometimes|integer|min:1',
            'add_products' => 'sometimes|array',
            'add_products.*.product_id' => 'required|uuid|exists:products,id',
            'add_products.*.quantity' => 'required|integer|min:1',
            'add_products.*.purchase_price' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    // Extract the product ID from the current attribute
                    $index = explode('.', $attribute)[1];
                    $productId = request("add_products.{$index}.product_id");
                    
                    // Find the product to compare purchase price
                    $product = Product::find($productId);
                    
                    if ($product && $value != $product->purchase_price) {
                        $fail("Purchase price must match the product's original purchase price of {$product->purchase_price}");
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 'Validation error', 422);
        }

        // Remove specified purchases
        if ($request->has('remove_products')) {
            Purchase::whereIn('id', $request->remove_products)->delete();
        }

        // Edit existing purchases
        if ($request->has('edit_products')) {
            foreach ($request->edit_products as $editProduct) {
                $purchase = Purchase::findOrFail($editProduct['id']);
                $purchase->update(
                    collect($editProduct)
                        ->only(['quantity'])
                        ->filter()
                        ->toArray()
                );
            }
        }

        // Add new purchases
        if ($request->has('add_products')) {
            foreach ($request->add_products as $newProduct) {
                Purchase::create([
                    'id' => Str::uuid(),
                    'purchase_code' => $purchase_code,
                    'product_id' => $newProduct['product_id'],
                    'supplier_id' => $existingPurchases->first()->supplier_id,
                    'user_id' => auth()->id(),
                    'quantity' => $newProduct['quantity'],
                    'purchase_price' => $newProduct['purchase_price'],
                    'purchase_date' => $request->purchase_date ?? Carbon::now()->toDateString(),
                    'status' => 'pending',
                ]);
            }
        }

        DB::commit();
        return $this->successResponse(
            ['purchases' => Purchase::where('purchase_code', $purchase_code)->get()], 
            'Purchase updated successfully'
        );
    } catch (Exception $e) {
        DB::rollBack();
        return $this->errorResponse('', 'An error occurred while updating purchase', 500);
    }
}

    public function update(Request $request, $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::findOrFail($id);

            if ($purchase->status !== 'pending') {
                return $this->errorResponse('', 'Cannot update non-pending purchase', 422);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'sometimes|integer|min:1',
                'purchase_price' => [
                    'sometimes',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($purchase) {
                        // Find the original product
                        $product = Product::find($purchase->product_id);
                        
                        if ($product && $value != $product->purchase_price) {
                            $fail("Purchase price must match the product's original purchase price of {$product->purchase_price}");
                        }
                    }
                ],
                'purchase_date' => 'sometimes|date',
                // Supplier validation
                'supplier_id' => 'nullable|uuid|exists:suppliers,id',
                'supplier' => 'required_without:supplier_id|array',
                'supplier.name' => 'required_with:supplier|string|max:255',
                'supplier.email' => 'nullable|email|max:255',
                'supplier.phone_contact' => 'required_with:supplier|string|max:20',
                'supplier.address' => 'nullable|string',
                'supplier.tin_number' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 'Validation error', 422);
            }

            // Handle supplier update
            if ($request->has('supplier_id')) {
                $supplier_id = $request->supplier_id;
            } elseif ($request->has('supplier')) {
                // Create new supplier if not exists
                $supplier = Supplier::create($request->supplier);
                $supplier_id = $supplier->id;
            }

            $data = $request->only(['quantity', 'purchase_price', 'purchase_date']);
            if (isset($supplier_id)) {
                $data['supplier_id'] = $supplier_id;
            }
            $data['purchase_date'] = $data['purchase_date'] ?? Carbon::now()->toDateString();

            $purchase->update($data);

            DB::commit();
            return $this->successResponse(['purchase' => $purchase->load('supplier')], 'Purchase updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'Purchase not found', 404);
        }
    }

        public function approve($purchase_code): JsonResponse
        {
            try {
                DB::beginTransaction();
                $purchases = Purchase::where('purchase_code', $purchase_code)
                    ->where('status', 'pending')
                    ->with(['product', 'supplier'])
                    ->get();
        
                if ($purchases->isEmpty()) {
                    return $this->errorResponse('', 'No pending purchases found with this code', 404);
                }
        
                foreach ($purchases as $purchase) {
                    $stock = Stock::where('product_id', $purchase->product_id)->first();
                    
                    // Create stock if not exists
                    if (!$stock) {
                        $stock = Stock::create([
                            'id' => Str::uuid(),
                            'product_id' => $purchase->product_id,
                            'quantity' => $purchase->quantity,
                            'opening_stock' => $purchase->quantity
                        ]);
                    } else {
                        $stock->quantity += $purchase->quantity;
                        $stock->save();
                    }
        
                    $purchase->status = 'approved';
                    $purchase->save();
        
                    // Create report for each purchase
                    Report::create([
                        'report_name' => 'Purchase Approval - ' . $purchase->product->name,
                        'report_type' => 'purchase',
                        'product_id' => $purchase->product_id,
                        'purchase_id' => $purchase->id,
                        'user_id' => Auth::id(),
                        'additional_notes' => [
                            'purchase_code' => $purchase_code,
                            'supplier_name' => $purchase->supplier ? $purchase->supplier->name : 'N/A',
                            'quantity' => $purchase->quantity,
                            'purchase_price' => $purchase->purchase_price,
                            'total_amount' => $purchase->quantity * $purchase->purchase_price,
                            'purchase_date' => $purchase->purchase_date,
                        ],
                    ]);
                }
        
                DB::commit();
                return $this->successResponse(['purchases' => $purchases], 'Purchases approved successfully');
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse('', 'An error occurred while approving purchases: ' . $e->getMessage(), 500);
            }
        }

    public function reject($purchase_code): JsonResponse
    {
        try {
            $purchases = Purchase::where('purchase_code', $purchase_code)->where('status', 'pending')->get();

            if ($purchases->isEmpty()) {
                return $this->errorResponse('', 'No pending purchases found with this code', 404);
            }

            foreach ($purchases as $purchase) {
                $purchase->status = 'rejected';
                $purchase->save();
            }

            return $this->successResponse(['purchases' => $purchases], 'Purchases rejected successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred while rejecting purchases', 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $purchase = Purchase::findOrFail($id);

            if ($purchase->status !== 'pending') {
                return $this->errorResponse('', 'Cannot delete non-pending purchase', 422);
            }

            $purchase->delete();
            return $this->successResponse([], 'Purchase deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Purchase not found', 404);
        }
    }

    public function deleteAll($purchase_code): JsonResponse
    {
        try {
            $purchases = Purchase::where('purchase_code', $purchase_code)->where('status', 'pending')->get();

            if ($purchases->isEmpty()) {
                return $this->errorResponse('', 'No pending purchases found with this code', 404);
            }

            foreach ($purchases as $purchase) {
                $purchase->delete();
            }

            return $this->successResponse([], 'Purchases deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred while deleting purchases', 500);
        }
    }
}
