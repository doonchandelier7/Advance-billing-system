<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardReportController;
use App\Http\Controllers\Api\EwayBillController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\InvoiceTemplateController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SalesReturnController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\VendorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (API-first architecture)
|--------------------------------------------------------------------------
|
| Protected routes use auth:sanctum. Role-based access uses 'role' middleware.
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles');
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    // Phase 1 – Core Master CRUD
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('units', UnitController::class);
    Route::apiResource('vendors', VendorController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('accounts', AccountController::class);

    // Phase 2 – Inventory & Stock
    Route::get('/stock-movements', [StockMovementController::class, 'index']);
    Route::post('/stock-in', [StockMovementController::class, 'stockIn']);
    Route::post('/stock-out', [StockMovementController::class, 'stockOut']);
    Route::post('/stock-adjust', [StockMovementController::class, 'adjust']);
    Route::get('/stock/low-stock', [StockMovementController::class, 'lowStock']);

    // Phase 3 – Billing & Invoice
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);

    // Phase 4 – Invoice Templates
    Route::apiResource('invoice-templates', InvoiceTemplateController::class);
    Route::get('/invoice-templates-for-type/{type}', [InvoiceTemplateController::class, 'forType']);
    Route::get('/invoice-templates/{invoiceTemplate}/versions', [InvoiceTemplateController::class, 'versions']);
    Route::get('/invoices/{invoice}/render/{invoice_template}', [InvoiceTemplateController::class, 'render']);

    // Phase 5 – Purchase, Sales Return, Purchase Return
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show']);
    Route::get('/purchase-returns', [PurchaseReturnController::class, 'index']);
    Route::post('/purchase-returns', [PurchaseReturnController::class, 'store']);
    Route::get('/purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show']);
    Route::get('/sales-returns', [SalesReturnController::class, 'index']);
    Route::post('/sales-returns', [SalesReturnController::class, 'store']);
    Route::get('/sales-returns/{salesReturn}', [SalesReturnController::class, 'show']);

    // Phase 6 – Reports & Dashboard
    Route::get('/reports/sales', [ReportController::class, 'sales']);
    Route::get('/reports/purchase', [ReportController::class, 'purchase']);
    Route::get('/reports/stock', [ReportController::class, 'stock']);
    Route::get('/reports/returns', [ReportController::class, 'returns']);
    Route::get('/reports/gst-summary', [ReportController::class, 'gstSummary']);
    Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
    Route::get('/dashboard/summary', [DashboardReportController::class, 'summary']);

    // Phase 9 – Branches & Settings (white-label)
    Route::apiResource('branches', BranchController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('/settings', [SettingController::class, 'index']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);
    Route::put('/settings', [SettingController::class, 'update']);

    // Phase 8 – E-Way Bill data & QR code
    Route::get('/invoices/{invoice}/eway-data', [EwayBillController::class, 'data']);
    Route::get('/invoices/{invoice}/qr', [EwayBillController::class, 'qrUrl']);
});
