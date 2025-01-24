<?php

use App\Http\Controllers\ForTestController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StockManagement\CustomerController;
use App\Http\Controllers\StockManagement\SupplierController;
use App\Http\Controllers\StockManagement\CategoryController;
use App\Http\Controllers\StockManagement\InvoiceManagementController;
use App\Http\Controllers\StockManagement\ProductController;
use App\Http\Controllers\StockManagement\PurchaseController;
use App\Http\Controllers\StockManagement\RefundController;
use App\Http\Controllers\StockManagement\ReportController;
use App\Http\Controllers\StockManagement\SaleController;
use App\Http\Controllers\StockManagement\StockController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\XeroCallbackController;
use App\Http\Controllers\XeroController;
use App\Http\Controllers\XeroWebHookController;
use App\Http\Controllers\SMSController;
use App\Http\Middleware\CorsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'cors', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResources([
        'invoices' => InvoiceController::class,
    ]);
    Route::apiResources(
        [
            'system-settings' => SystemSettingsController::class,
        ],
        ['only' => ['index', 'store']],
    );
    Route::get('/xero-invoices', [XeroController::class, 'getInvoices']);
    Route::get('/xero-company-logo', [XeroController::class, 'getCompanyLogo']);
    Route::prefix('/user')->group(function () {
        Route::patch('/change-password/{id}', [UserController::class, 'changePassword']);
        Route::patch('/update-profile/{id}', [UserController::class, 'updateProfile']);
        Route::delete('/delete/{id}', [UserController::class, 'delete']);
    });
});
Route::post('/xero-webhook', XeroWebHookController::class);

Route::get('/generate-pdf', [InvoiceController::class, 'generateReceiptPDF']);

Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'store']);
Route::get('/unauthenticated', function () {
    return response()->error([], 'Invalid Token', 422);
})->name('unauthenticated');

Route::middleware(['web'])->group(function () {
    Route::post('/sms/send-sms', [SMSController::class, 'sendSMS']);
});

Route::prefix('/v3')
    ->middleware('auth:sanctum')
    ->group(function () {
        // Stock managements api endpoints

        Route::prefix('/categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/{id}', [CategoryController::class, 'show']);
            Route::post('/', [CategoryController::class, 'store']);
            Route::patch('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
        });

        Route::prefix('/suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index']);
            Route::get('/{id}', [SupplierController::class, 'show']);
            Route::post('/', [SupplierController::class, 'store']);
            Route::patch('/{id}', [SupplierController::class, 'update']);
            Route::delete('/{id}', [SupplierController::class, 'destroy']);
        });

        Route::prefix('/customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::get('/{id}', [CustomerController::class, 'show']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::patch('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy']);
        });
        Route::prefix('/products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::post('/', [ProductController::class, 'store']);
            Route::patch('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::patch('/approve/{entry_code}', [ProductController::class, 'approve']);
            Route::patch('/reject/{entry_code}', [ProductController::class, 'reject']);
            Route::delete('/delete/{entry_code}', [ProductController::class, 'deleteAll']);
        });

        Route::prefix('/invoices')->group(function () {
            Route::get('/purchase/{purchase_code}', [InvoiceManagementController::class, 'purchase']);
            Route::get('/sale/{sale_code}', [InvoiceManagementController::class, 'sale']);
            Route::post('/invoice', [InvoiceManagementController::class, 'store']);
        });

        Route::prefix('/purchases')->group(function () {
            Route::get('/', [PurchaseController::class, 'index']);
            Route::get('/{id}', [PurchaseController::class, 'show']);
            Route::post('/', [PurchaseController::class, 'store']);
            Route::post('/{purchase_code}', [PurchaseController::class, 'editPurchase']);
            Route::patch('/{id}', [PurchaseController::class, 'update']);
            Route::delete('/{id}', [PurchaseController::class, 'destroy']);
            Route::patch('/approve/{purchase_code}', [PurchaseController::class, 'approve']);
            Route::patch('/reject/{purchase_code}', [PurchaseController::class, 'reject']);
            Route::delete('/delete/{purchase_code}', [PurchaseController::class, 'deleteAll']);
        });


        Route::get('sales/completed', [SaleController::class, 'completed']);
        Route::prefix('/sales')->group(function () {
            Route::get('/', [SaleController::class, 'completed']);
            Route::get('/{id}', [SaleController::class, 'show']);
            Route::post('/', [SaleController::class, 'store']);
            Route::post('/{sale_code}', [SaleController::class, 'editSale']);
            Route::patch('/{id}', [SaleController::class, 'update']);
            Route::delete('/{id}', [SaleController::class, 'destroy']);
            Route::patch('/approve/{sale_code}', [SaleController::class, 'approve']);
            Route::patch('/reject/{sale_code}', [SaleController::class, 'reject']);
            Route::delete('/delete/{sale_code}', [SaleController::class, 'deleteAll']);
        });

        Route::prefix('/refunds')->group(function () {
            Route::get('/', [RefundController::class, 'index']);
            Route::get('/sales/{sale_code}', [RefundController::class, 'getSalesBySaleCode']);
            Route::get('/{id}', [RefundController::class, 'show']);
            Route::post('/', [RefundController::class, 'store']);
            Route::patch('/{sale_code}', [RefundController::class, 'refund']);
            Route::patch('/{id}', [RefundController::class, 'update']);
            Route::delete('/{id}', [RefundController::class, 'destroy']);
            Route::patch('/approve/{sale_code}', [RefundController::class, 'approve']);
            Route::patch('/reject/{sale_code}', [RefundController::class, 'reject']);
            Route::post('/bulk-approve', [RefundController::class, 'bulkApprove']);
            Route::post('/bulk-reject', [RefundController::class, 'bulkReject']);
        });

        Route::prefix('/stocks')->group(function () {
            Route::get('/', [StockController::class, 'index']);
        });

        // Report Routes
        // Replace the existing report routes with:
        Route::prefix('/reports')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::get('/{type}', [ReportController::class, 'getReport']);
            Route::get('/{id}', [ReportController::class, 'show']);
        });
    });
