<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Report;
use App\Models\Sale;
use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SaleController extends ApiController
{
    public function index(): JsonResponse
    {
        try {
            $sales = Sale::with(['customer', 'product'])
                ->orderBy('created_at', 'desc')
                ->paginate(8);
            return $this->successResponse(['sales' => $sales], 'Sales retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred while retrieving sales', 500);
        }
    }


    public function completed(): JsonResponse
    {

        try {
            $completedSales = Sale::with(['customer', 'user', 'product'])
                ->where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->paginate(8);
    
            if ($completedSales->isEmpty()) {
                return $this->errorResponse('', 'No completed sales found', 404);
            }
    
            return $this->successResponse(['sales' => $completedSales], 'Completed sales retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'An error occurred while retrieving completed sales', 500);
        }
    }
    


    public function show($id): JsonResponse
    {
        try {
            $sale = Sale::findOrFail($id);
            return $this->successResponse(['sale' => $sale], 'Sale retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Sale not found', 404);
        }
    }

        public function store(Request $request): JsonResponse
        {
            try {
                DB::beginTransaction();
        
                $validator = Validator::make($request->all(), [
                    'products' => 'required|array|min:1',
                    'products.*.product_id' => 'required|uuid|exists:products,id',
                    'products.*.quantity' => 'required|integer|min:1',
                    'products.*.sale_price' => [
                        'required',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) {
                            $index = explode('.', $attribute)[1];
                            $productId = request("products.{$index}.product_id");
                            
                            $product = Product::find($productId);
                            
                            if ($product && $value < $product->unit_price) {
                                $fail("Sale price must be greater than or equal to unit price of {$product->unit_price}");
                            }
                        }
                    ],
                    'sale_date' => 'nullable|date',
                    'customer_id' => 'nullable|uuid|exists:customers,id',
                    'customer' => 'required_without:customer_id|array',
                    'customer.name' => 'required_with:customer|string|max:255',
                    'customer.email' => 'nullable|email|max:255',
                    'customer.phone_contact' => 'required_with:customer|string|max:20',
                    'customer.address' => 'nullable|string',
                    'customer.tin_number' => 'nullable|string|max:50',
                ]);
        
                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors(), 'Validation error', 422);
                }
        
                // Handle customer creation or use existing customer
                $customer_id = $request->customer_id;
                if (!$customer_id && $request->has('customer')) {
                    $customer = Customer::create($request->customer);
                    $customer_id = $customer->id;
                }
        
                // Validate stock availability for all products first
                foreach ($request->products as $product) {
                    $stock = Stock::where('product_id', $product['product_id'])->first();
        
                    if (!$stock || $stock->quantity < $product['quantity']) {
                        return $this->errorResponse(
                            '',
                            "Insufficient stock available for product " . Product::find($product['product_id'])->name,
                            422
                        );
                    }
                }
        
                // Generate a single sale code for all products
                $sale_code = 'SAL-' . strtoupper(Str::random(8));
                $sales = [];
                $total_profit = 0;
        
                // Create sale records and update stock for each product
                foreach ($request->products as $product) {
                    $productModel = Product::find($product['product_id']);
                    $profit = max(0, ($product['sale_price'] - $productModel->purchase_price) * $product['quantity']);
                    $total_profit += $profit;
        
                    $sale = Sale::create([
                        'id' => Str::uuid(),
                        'sale_code' => $sale_code,
                        'product_id' => $product['product_id'],
                        'customer_id' => $customer_id,
                        'user_id' => auth()->id(),
                        'quantity' => $product['quantity'],
                        'sale_price' => $product['sale_price'],
                        'sale_date' => $request->sale_date ?? now()->toDateString(),
                        'status' => 'pending'
                    ]);
        
                    $sales[] = $sale;
                }
        
                DB::commit();
        
                return $this->successResponse(
                    [
                        'sale_code' => $sale_code,
                        'sales' => Sale::where('sale_code', $sale_code)->with(['customer', 'product'])->get(),
                        'total_profit' => $total_profit
                    ],
                    'Sales created successfully',
                    201
                );
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse(
                    config('app.debug') ? $e->getMessage() : null,
                    'Failed to make sale',
                    500
                );
            }
        }

        public function update(Request $request, $id): JsonResponse
        {
            try {
                DB::beginTransaction();
        
                $sale = Sale::findOrFail($id);
        
                if ($sale->status !== 'pending') {
                    return $this->errorResponse('', 'Cannot update non-pending sale', 422);
                }
        
                $validator = Validator::make($request->all(), [
                    'quantity' => [
                        'sometimes',
                        'integer', 
                        'min:1',
                        function ($attribute, $value, $fail) use ($sale) {
                            $stock = Stock::where('product_id', $sale->product_id)->first();
                            $stockDiff = $sale->quantity - $value;
        
                            if ($stockDiff < 0 && abs($stockDiff) > $stock->quantity) {
                                $fail('Insufficient stock available');
                            }
                        }
                    ],
                    'sale_price' => [
                        'sometimes',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) use ($sale) {
                            $product = Product::find($sale->product_id);
                            if ($value < $product->unit_price) {
                                $fail("Sale price must be greater than or equal to unit price of {$product->unit_price}");
                            }
                        }
                    ],
                    'sale_date' => 'sometimes|date',
                ]);
        
                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors(), 'Validation error', 422);
                }
        
                // Stock validation and adjustment
                if ($request->has('quantity') && $request->quantity != $sale->quantity) {
                    $stock = Stock::where('product_id', $sale->product_id)->firstOrFail();
                    $stockDiff = $sale->quantity - $request->quantity;
        
                    $stock->quantity += $stockDiff;
                    $stock->save();
                }
        
                $sale->update($request->only(['quantity', 'sale_price', 'sale_date']));
        
                DB::commit();
                return $this->successResponse(['sale' => $sale], 'Sale updated successfully');
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse('', 'An error occurred while updating sale', 500);
            }
        }
        
        public function editSale(Request $request, $sale_code): JsonResponse
        {
            try {
                DB::beginTransaction();
        
                // Find existing sales with this code
                $existingSales = Sale::where('sale_code', $sale_code)->get();
        
                if ($existingSales->isEmpty()) {
                    return $this->errorResponse('', 'No sales found with this code', 404);
                }


                $productIds = collect($request->remove_products ?? [])
                ->merge(collect($request->edit_products ?? [])->pluck('id'))
                ->merge(collect($request->add_products ?? [])->pluck('product_id'))
                ->unique();
    
                $missingStocks = $productIds->filter(function($productId) {
                    return !Stock::where('product_id', $productId)->exists();
                });
        
                if ($missingStocks->isNotEmpty()) {
                    return $this->errorResponse('', 'Stock not found for products: ' . $missingStocks->implode(', '), 422);
                }
        
                // Validate input
                $validator = Validator::make($request->all(), [
                    'remove_products' => 'sometimes|array',
                    'remove_products.*' => 'uuid|exists:sales,product_id',
                    'edit_products' => 'sometimes|array',
                    'edit_products.*.id' => 'required|uuid|exists:sales,product_id',
                   'edit_products.*.quantity' => [
                    'sometimes', 
                    'integer', 
                    'min:1',
                    function ($attribute, $value, $fail) use ($request) {
                        $index = explode('.', $attribute)[1];
                        $productId = $request->edit_products[$index]['id'];
                        $sale = Sale::where('product_id', $productId)->first();
                        
                        $stock = Stock::where('product_id', $sale->product_id)->first();
                        $stockDiff = $sale->quantity - $value;

                        if ($stockDiff < 0 && abs($stockDiff) > $stock->quantity) {
                            $fail('Insufficient stock available');
                        }
                    }
                ],
                    'edit_products.*.sale_price' => [
                        'sometimes',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) use ($request) {
                            $index = explode('.', $attribute)[1];
                            $productId = $request->edit_products[$index]['id'];
                            $sale = Sale::where('product_id', $productId)->first();
                            $product = Product::find($sale->product_id);
                            
                            if ($value < $product->unit_price) {
                                $fail("Sale price must be greater than or equal to unit price of {$product->unit_price}");
                            }
                        }
                    ],
                    'add_products' => 'sometimes|array',
                    'add_products.*.product_id' => [
                        'required', 
                        'uuid', 
                        'exists:products,id',
                        function ($attribute, $value, $fail) {
                            $stock = Stock::where('product_id', $value)->first();
                            if (!$stock) {
                                $fail('No stock found for this product');
                            }
                        }
                    ],
                    'add_products.*.quantity' => 'required|integer|min:1',
                    'add_products.*.sale_price' => [
                        'required',
                        'numeric',
                        'min:0',
                        function ($attribute, $value, $fail) use ($request) {
                            $index = explode('.', $attribute)[1];
                            $productId = $request->add_products[$index]['product_id'];
                            
                            $product = Product::find($productId);
                            
                            if ($value < $product->unit_price) {
                                $fail("Sale price must be greater than or equal to unit price of {$product->unit_price}");
                            }
                        }
                    ],
                ]);
        
                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors(), 'Validation error', 422);
                }
        
                // Remove specified sales
                if ($request->has('remove_products')) {
                    $removeSales = Sale::whereIn('product_id', $request->remove_products)->get();
                    
                    foreach ($removeSales as $removedSale) {
                        $stock = Stock::where('product_id', $removedSale->product_id)->firstOrFail();
                        $stock->quantity += $removedSale->quantity;
                        $stock->save();
                        
                        $removedSale->delete();
                    }
                }
        
                // Edit existing sales
                if ($request->has('edit_products')) {
                    foreach ($request->edit_products as $editProduct) {
                        $sale = Sale::where('product_id', $editProduct['id'])->firstOrFail();
                        
                        // Stock adjustment for quantity change
                        if (isset($editProduct['quantity']) && $editProduct['quantity'] != $sale->quantity) {
                            $stock = Stock::where('product_id', $sale->product_id)->firstOrFail();
                            $stockDiff = $sale->quantity - $editProduct['quantity'];
        
                            $stock->quantity += $stockDiff;
                            $stock->save();
                        }
        
                        $sale->update(
                            collect($editProduct)
                                ->only(['quantity', 'sale_price'])
                                ->filter()
                                ->toArray()
                        );
                    }
                }
        
                // Add new sales
                if ($request->has('add_products')) {
                    foreach ($request->add_products as $newProduct) {
                        $stock = Stock::where('product_id', $newProduct['product_id'])->firstOrFail();
                        
                        if ($newProduct['quantity'] > $stock->quantity) {
                            return $this->errorResponse('', 'Insufficient stock available', 422);
                        }
        
                        $stock->quantity -= $newProduct['quantity'];
                        $stock->save();
        
                        Sale::create([
                            'id' => Str::uuid(),
                            'sale_code' => $sale_code,
                            'product_id' => $newProduct['product_id'],
                            'customer_id' => $existingSales->first()->customer_id,
                            'user_id' => auth()->id(),
                            'quantity' => $newProduct['quantity'],
                            'sale_price' => $newProduct['sale_price'],
                            'sale_date' => $request->sale_date ?? Carbon::now()->toDateString(),
                            'status' => 'pending',
                        ]);
                    }
                }
        
                DB::commit();
                return $this->successResponse(
                    ['sales' => Sale::where('sale_code', $sale_code)->get()], 
                    'Sale updated successfully'
                );
            } catch (Exception $e) {
                DB::rollBack();
                return $this->errorResponse($e->getMessage(), 'An error occurred while updating sale', 500);
            }
        }

    public function destroy($id): JsonResponse
    {
        try {
            $sale = Sale::findOrFail($id);

            if ($sale->status !== 'pending') {
                return $this->errorResponse('', 'Cannot delete non-pending sale', 422);
            }

            // Restore stock
            $stock = Stock::where('product_id', $sale->product_id)->firstOrFail();
            $stock->quantity += $sale->quantity;
            $stock->save();

            $sale->delete();
            return $this->successResponse([], 'Sale deleted successfully');
        } catch (Exception $e) {
            return $this->errorResponse('', 'Sale not found', 404);
        }
    }

    public function approve(string $sale_code): JsonResponse
    {
        try {
            DB::beginTransaction();
    
            $sales = Sale::with('product')->where('sale_code', $sale_code)->where('status', 'pending')->get();
    
            if ($sales->isEmpty()) {
                return $this->errorResponse('', 'No pending sales found with this code', 404);
            }
    
            $totalAmount = 0;
            foreach ($sales as $sale) {
                // Update stock quantity (moved before status change and reporting)
                $stock = Stock::where('product_id', $sale->product_id)->first();
                
                if (!$stock || $stock->quantity < $sale->quantity) {
                    DB::rollBack();
                    return $this->errorResponse('', "Insufficient stock for product: {$sale->product->name}", 422);
                }
    
                $stock->quantity -= $sale->quantity;
                $stock->save();
    
                $sale->status = 'completed';
                $sale->save();
    
                $saleTotal = $sale->quantity * $sale->sale_price;
                $totalAmount += $saleTotal;
    
                // Create report for each sale
                Report::create([
                    'report_name' => 'Sale Approval - ' . $sale->product->name,
                    'report_type' => 'sale',
                    'product_id' => $sale->product_id,
                    'sale_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'additional_notes' => [
                        'sale_code' => $sale_code,
                        'quantity' => $sale->quantity,
                        'sale_price' => $sale->sale_price,
                        'total_amount' => $saleTotal,
                        'sale_date' => $sale->sale_date->toDateString(),
                    ],
                ]);
            }
    
            // Create batch summary report
            Report::create([
                'report_name' => 'Batch Sale Approval - ' . $sale_code,
                'report_type' => 'sale',
                'user_id' => Auth::id(),
                'additional_notes' => [
                    'sale_code' => $sale_code,
                    'total_sales' => count($sales),
                    'total_amount' => $totalAmount,
                    'approved_at' => now()->toDateTimeString(),
                ],
            ]);
    
            DB::commit();
            return $this->successResponse(['sales' => $sales], 'Sales approved successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while approving sales', 500);
        }
    }

    public function reject(string $sale_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $sales = Sale::with('product')->where('sale_code', $sale_code)->where('status', 'pending')->get();

            if ($sales->isEmpty()) {
                return $this->errorResponse('', 'No pending sales found with this code', 404);
            }

            foreach ($sales as $sale) {
                // Restore stock
                $stock = Stock::where('product_id', $sale->product_id)->first();
                if ($stock) {
                    $stock->quantity += $sale->quantity;
                    $stock->save();
                }

                $sale->status = 'rejected';
                $sale->save();

                // Create report for each rejected sale
                Report::create([
                    'report_name' => 'Sale Rejection - ' . $sale->product->name,
                    'report_type' => 'sale',
                    'product_id' => $sale->product_id,
                    'sale_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'additional_notes' => [
                        'sale_code' => $sale_code,
                        'quantity' => $sale->quantity,
                        'sale_price' => $sale->sale_price,
                        'rejected_at' => now()->toDateTimeString(),
                    ],
                ]);
            }

            DB::commit();
            return $this->successResponse(['sales' => $sales], 'Sales rejected successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while rejecting sales', 500);
        }
    }
    public function deleteAll($sale_code): JsonResponse
    {
        try {
            DB::beginTransaction();

            $sales = Sale::where('sale_code', $sale_code)->where('status', 'pending')->get();

            if ($sales->isEmpty()) {
                return $this->errorResponse('', 'No pending sales found with this code', 404);
            }

            foreach ($sales as $sale) {
                // Restore stock for each sale
                $stock = Stock::where('product_id', $sale->product_id)->first();
                if ($stock) {
                    $stock->quantity += $sale->quantity;
                    $stock->save();
                }
                $sale->delete();
            }

            DB::commit();
            return $this->successResponse([], 'Sales deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse('', 'An error occurred while deleting sales', 500);
        }
    }
}
