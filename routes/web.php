<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AiInvoiceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceSetupController;
use App\Http\Controllers\MasterSetupController;
use App\Http\Controllers\ModulePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Master Setup (Categories, Units, Vendors, Customers, Products)
    Route::get('/modules/master-setup', [MasterSetupController::class, 'index'])->name('modules.master-setup');
    Route::post('/modules/master-setup/categories', [MasterSetupController::class, 'storeCategory'])->name('modules.master-setup.categories.store');
    Route::put('/modules/master-setup/categories/{category}', [MasterSetupController::class, 'updateCategory'])->name('modules.master-setup.categories.update');
    Route::delete('/modules/master-setup/categories/{category}', [MasterSetupController::class, 'destroyCategory'])->name('modules.master-setup.categories.destroy');
    Route::post('/modules/master-setup/units', [MasterSetupController::class, 'storeUnit'])->name('modules.master-setup.units.store');
    Route::put('/modules/master-setup/units/{unit}', [MasterSetupController::class, 'updateUnit'])->name('modules.master-setup.units.update');
    Route::delete('/modules/master-setup/units/{unit}', [MasterSetupController::class, 'destroyUnit'])->name('modules.master-setup.units.destroy');
    Route::post('/modules/master-setup/vendors', [MasterSetupController::class, 'storeVendor'])->name('modules.master-setup.vendors.store');
    Route::put('/modules/master-setup/vendors/{vendor}', [MasterSetupController::class, 'updateVendor'])->name('modules.master-setup.vendors.update');
    Route::delete('/modules/master-setup/vendors/{vendor}', [MasterSetupController::class, 'destroyVendor'])->name('modules.master-setup.vendors.destroy');
    Route::post('/modules/master-setup/customers', [MasterSetupController::class, 'storeCustomer'])->name('modules.master-setup.customers.store');
    Route::put('/modules/master-setup/customers/{customer}', [MasterSetupController::class, 'updateCustomer'])->name('modules.master-setup.customers.update');
    Route::delete('/modules/master-setup/customers/{customer}', [MasterSetupController::class, 'destroyCustomer'])->name('modules.master-setup.customers.destroy');
    Route::post('/modules/master-setup/products', [MasterSetupController::class, 'storeProduct'])->name('modules.master-setup.products.store');
    Route::put('/modules/master-setup/products/{product}', [MasterSetupController::class, 'updateProduct'])->name('modules.master-setup.products.update');
    Route::delete('/modules/master-setup/products/{product}', [MasterSetupController::class, 'destroyProduct'])->name('modules.master-setup.products.destroy');

    Route::get('/modules/transactions', [ModulePageController::class, 'transactions'])->name('modules.transactions');
    Route::get('/modules/transactions/sales/create', [\App\Http\Controllers\TransactionController::class, 'createSale'])->name('modules.transactions.sales.create');
    Route::post('/modules/transactions/sales', [\App\Http\Controllers\TransactionController::class, 'storeSale'])->name('modules.transactions.sales.store');
    Route::get('/modules/transactions/purchases/create', [\App\Http\Controllers\TransactionController::class, 'createPurchase'])->name('modules.transactions.purchases.create');
    Route::post('/modules/transactions/purchases', [\App\Http\Controllers\TransactionController::class, 'storePurchase'])->name('modules.transactions.purchases.store');
    Route::get('/modules/transactions/purchase-returns/create', [\App\Http\Controllers\TransactionController::class, 'createPurchaseReturn'])->name('modules.transactions.purchase-returns.create');
    Route::post('/modules/transactions/purchase-returns', [\App\Http\Controllers\TransactionController::class, 'storePurchaseReturn'])->name('modules.transactions.purchase-returns.store');
    Route::get('/modules/transactions/sales-returns/create', [\App\Http\Controllers\TransactionController::class, 'createSalesReturn'])->name('modules.transactions.sales-returns.create');
    Route::post('/modules/transactions/sales-returns', [\App\Http\Controllers\TransactionController::class, 'storeSalesReturn'])->name('modules.transactions.sales-returns.store');
    Route::get('/modules/books-registers', [ModulePageController::class, 'booksRegisters'])->name('modules.books-registers');
    Route::get('/modules/reports', [ModulePageController::class, 'reports'])->name('modules.reports');
    Route::post('/modules/reports/upload', [ModulePageController::class, 'uploadReport'])->name('modules.reports.upload');
    Route::get('/modules/reports/preview/{upload}', [ModulePageController::class, 'previewReport'])->name('modules.reports.preview')->whereNumber('upload');
    Route::delete('/modules/reports/{upload}', [ModulePageController::class, 'destroyReport'])->name('modules.reports.destroy')->whereNumber('upload');

    // Accounting (Chart of Accounts)
    Route::get('/modules/accounting', [AccountingController::class, 'index'])->name('modules.accounting');
    Route::post('/modules/accounting', [AccountingController::class, 'store'])->name('modules.accounting.store');
    Route::put('/modules/accounting/{account}', [AccountingController::class, 'update'])->name('modules.accounting.update');
    Route::delete('/modules/accounting/{account}', [AccountingController::class, 'destroy'])->name('modules.accounting.destroy');

    // Invoice Templates & Generation
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/', [InvoiceSetupController::class, 'index'])->name('index');
        Route::get('/templates', [InvoiceSetupController::class, 'templates'])->name('templates');
        Route::post('/templates', [InvoiceSetupController::class, 'storeTemplate'])->name('templates.store');
        Route::put('/templates/{template}', [InvoiceSetupController::class, 'updateTemplate'])->name('templates.update');
        Route::delete('/templates/{template}', [InvoiceSetupController::class, 'destroyTemplate'])->name('templates.destroy');
        Route::get('/create', [InvoiceSetupController::class, 'create'])->name('create');
        Route::post('/', [InvoiceSetupController::class, 'store'])->name('store');
        Route::get('/{invoice}/generate', [InvoiceSetupController::class, 'generate'])->name('generate');
        Route::get('/{invoice}/print', [InvoiceSetupController::class, 'print'])->name('print');
    });

    // AI Invoice Scan (Phase 4)
    Route::prefix('ai-invoice')->name('ai-invoice.')->group(function () {
        Route::get('/', [AiInvoiceController::class, 'index'])->name('index');
        Route::post('/upload', [AiInvoiceController::class, 'upload'])->name('upload');
        Route::get('/preview/{upload}', [AiInvoiceController::class, 'preview'])->name('preview')->whereNumber('upload');
        Route::post('/process', [AiInvoiceController::class, 'process'])->name('process');
        Route::post('/save', [AiInvoiceController::class, 'save'])->name('save');
        Route::get('/invoice/{invoice}/pdf', [AiInvoiceController::class, 'pdf'])->name('pdf');
        Route::get('/invoice/{invoice}/print', [AiInvoiceController::class, 'print'])->name('print');
    });
});
