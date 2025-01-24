<?php

namespace App\Http\Controllers\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class ReportController extends Controller
{
    private const VALID_REPORT_TYPES = ['entry', 'sale', 'refund', 'purchase'];



    public function index(Request $request): JsonResponse
    {
        try {
            // Validate report type
            $request->validate([
                'date_from' => 'date|nullable',
                'date_to' => 'date|nullable|after_or_equal:date_from',
                'per_page' => 'integer|min:1|max:100|nullable',
            ]);


            // Build base query
            $query = Report::with(['user:id,name', 'product', 'purchase', 'sale', 'refund']);

            // Get paginated results
            $reports = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));


            return response()->json([
                'status' => 'success',
                'data' => $reports,
            ]);

        } catch (ValidationException $e) {
            Log::warning('Report validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Failed to fetch report', [
                'type' => $request->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }



    /**
     * Get report based on type with summary
     */
    public function getReport(Request $request, $type): JsonResponse
    {
        try {
            // Validate report type
            $request->validate([
                'type' => ['required', 'string', 'in:' . implode(',', self::VALID_REPORT_TYPES)],
                'date_from' => 'date|nullable',
                'date_to' => 'date|nullable|after_or_equal:date_from',
                'per_page' => 'integer|min:1|max:100|nullable',
                'entry_code' => 'string|nullable',
            ]);

            $relations = $this->getRelationsForType($type);

            // Build base query
            $query = Report::with(['user:id,name', ...$relations])
                ->where('report_type', $type)
                ->select($this->getSelectFieldsForType($type));

            // Apply filters
            $this->applyFilters($query, $request, $type);

            // Get paginated results
            $reports = $query->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            // Get summary
            $summary = $this->generateSummary($type, $request);

            return response()->json([
                'status' => 'success',
                'data' => $reports,
                'summary' => $summary,
            ]);

        } catch (ValidationException $e) {
            Log::warning('Report validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid input parameters',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Failed to fetch report', [
                'type' => $request->type ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get the appropriate relations based on report type
     */
    private function getRelationsForType(string $type): array
    {
        return match ($type) {
            'entry' => ['product:id,name,entry_code'],
            'sale' => ['sale'],
            'refund' => ['refund'],
            'purchase' => ['purchase'],
            default => [],
        };
    }

    /**
     * Get the appropriate select fields based on report type
     */
    private function getSelectFieldsForType(string $type): array
    {
        $baseFields = ['id', 'report_name', 'user_id', 'created_at', 'additional_notes'];

        $typeSpecificField = match ($type) {
            'entry' => 'product_id',
            'sale' => 'sale_id',
            'refund' => 'refund_id',
            'purchase' => 'purchase_id',
            default => null,
        };

        return $typeSpecificField ? [...$baseFields, $typeSpecificField] : $baseFields;
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, Request $request, string $type): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Special case for entry reports
        if ($type === 'entry' && $request->filled('entry_code')) {
            $query->whereJsonContains('additional_notes->entry_code', $request->entry_code);
        }
    }

    /**
     * Generate summary based on report type
     */
    private function generateSummary(string $type, Request $request): object
    {
        $query = DB::table('reports')
            ->where('report_type', $type);

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return match ($type) {
            'entry' => $query->select(
                DB::raw('COUNT(DISTINCT JSON_EXTRACT(additional_notes, "$.entry_code")) as total_entries'),
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(JSON_EXTRACT(additional_notes, "$.total_stock_value")) as total_stock_value'),
                DB::raw('AVG(JSON_EXTRACT(additional_notes, "$.initial_stock")) as average_initial_stock')
            )->first(),

            'sale' => $query->select(
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(JSON_EXTRACT(additional_notes, "$.total_amount")) as total_amount'),
                DB::raw('AVG(JSON_EXTRACT(additional_notes, "$.total_amount")) as average_sale_amount')
            )->first(),

            'refund' => $query->select(
                DB::raw('COUNT(*) as total_refunds'),
                DB::raw('SUM(JSON_EXTRACT(additional_notes, "$.refund_amount")) as total_refund_amount'),
                DB::raw('AVG(JSON_EXTRACT(additional_notes, "$.refund_amount")) as average_refund_amount')
            )->first(),

            'purchase' => $query->select(
                DB::raw('COUNT(*) as total_purchases'),
                DB::raw('SUM(JSON_EXTRACT(additional_notes, "$.total_amount")) as total_purchase_amount'),
                DB::raw('AVG(JSON_EXTRACT(additional_notes, "$.total_amount")) as average_purchase_amount')
            )->first(),

            default => (object)[],
        };
    }

    /**
     * Display a specific report
     */
    public function show(string $id): JsonResponse
    {
        try {
            $report = Report::with([
                'user:id,name',
                'product:id,name,entry_code',
                'sale:id,invoice_number',
                'purchase:id,invoice_number',
                'refund:id,refund_number'
            ])->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => $report,
            ]);

        } catch (ModelNotFoundException $e) {
            Log::info('Report not found', ['id' => $id]);
            return response()->json([
                'status' => 'error',
                'message' => 'Report not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('Error fetching report', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch report',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
