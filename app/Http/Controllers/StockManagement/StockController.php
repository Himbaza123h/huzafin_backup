<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Stock::with(['product' => function($query) {
                $query->with('category');
            }]);

            // Filter by category if provided
            if ($request->has('category_id')) {
                $query->whereHas('product', function($query) use ($request) {
                    $query->where('category_id', $request->category_id);
                });
            }

            // Get stock statistics
            $statistics = [
                'total_products' => Stock::count(),
                'out_of_stock' => Stock::where('quantity', 0)->count(),
                'low_stock' => Stock::where('quantity', '>', 0)
                    ->where('quantity', '<=', DB::raw('opening_stock * 0.1'))
                    ->count()
            ];

            // Apply filters for stock status if provided
            if ($request->has('stock_status')) {
                switch ($request->stock_status) {
                    case 'out_of_stock':
                        $query->where('quantity', 0);
                        break;
                    case 'low_stock':
                        $query->where('quantity', '>', 0)
                            ->where('quantity', '<=', DB::raw('opening_stock * 0.1'));
                        break;
                    case 'in_stock':
                        $query->where('quantity', '>', 0);
                        break;
                }
            }

            // Get paginated results
            $stocks = $query->paginate($request->input('per_page', 10));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'stocks' => $stocks,
                    'statistics' => $statistics
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch stocks',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
