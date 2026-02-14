<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceUpload;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ModulePageController extends Controller
{
    /**
     * Placeholder pages for each module (dedicated module page, not everything on home).
     */
    public function masterSetup(Request $request): View
    {
        return view('modules.master-setup', ['title' => 'Master Setup', 'description' => 'Firm, City, Product, Account (Customer/Vendor). Phase 1.']);
    }

    public function transactions(Request $request): View
    {
        $userId = auth()->id();

        // Date filters
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Sales (Invoices)
        $salesQuery = Invoice::with('customer')
            ->where('user_id', $userId)
            ->whereNotNull('invoice_number')
            ->orderByDesc('invoice_date');
        if ($fromDate) $salesQuery->whereDate('invoice_date', '>=', $fromDate);
        if ($toDate) $salesQuery->whereDate('invoice_date', '<=', $toDate);
        $sales = $salesQuery->paginate(15, ['*'], 'sales_page');

        // Purchases
        $purchaseQuery = Purchase::with('vendor')
            ->where('user_id', $userId)
            ->orderByDesc('purchase_date');
        if ($fromDate) $purchaseQuery->whereDate('purchase_date', '>=', $fromDate);
        if ($toDate) $purchaseQuery->whereDate('purchase_date', '<=', $toDate);
        $purchases = $purchaseQuery->paginate(15, ['*'], 'purchase_page');

        // Purchase Returns
        $purchaseReturnQuery = PurchaseReturn::with('vendor')
            ->where('user_id', $userId)
            ->orderByDesc('return_date');
        if ($fromDate) $purchaseReturnQuery->whereDate('return_date', '>=', $fromDate);
        if ($toDate) $purchaseReturnQuery->whereDate('return_date', '<=', $toDate);
        $purchaseReturns = $purchaseReturnQuery->paginate(15, ['*'], 'pr_page');

        // Sales Returns
        $salesReturnQuery = SalesReturn::with('customer')
            ->where('user_id', $userId)
            ->orderByDesc('return_date');
        if ($fromDate) $salesReturnQuery->whereDate('return_date', '>=', $fromDate);
        if ($toDate) $salesReturnQuery->whereDate('return_date', '<=', $toDate);
        $salesReturns = $salesReturnQuery->paginate(15, ['*'], 'sr_page');

        // Summaries
        $salesSummary = [
            'count' => Invoice::where('user_id', $userId)->whereNotNull('invoice_number')->count(),
            'total' => Invoice::where('user_id', $userId)->whereNotNull('invoice_number')->sum('net_amount'),
        ];
        $purchaseSummary = [
            'count' => Purchase::where('user_id', $userId)->count(),
            'total' => Purchase::where('user_id', $userId)->sum('total'),
        ];
        $purchaseReturnSummary = [
            'count' => PurchaseReturn::where('user_id', $userId)->count(),
            'total' => PurchaseReturn::where('user_id', $userId)->sum('total'),
        ];
        $salesReturnSummary = [
            'count' => SalesReturn::where('user_id', $userId)->count(),
            'total' => SalesReturn::where('user_id', $userId)->sum('total'),
        ];

        return view('modules.transactions', compact(
            'sales', 'purchases', 'purchaseReturns', 'salesReturns',
            'salesSummary', 'purchaseSummary', 'purchaseReturnSummary', 'salesReturnSummary',
            'fromDate', 'toDate'
        ));
    }

    public function booksRegisters(Request $request): View
    {
        $userId = auth()->id();

        // Date filters
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        // Purchase Book
        $purchaseQuery = Purchase::with('vendor')
            ->where('user_id', $userId)
            ->orderByDesc('purchase_date');
        if ($fromDate) $purchaseQuery->whereDate('purchase_date', '>=', $fromDate);
        if ($toDate) $purchaseQuery->whereDate('purchase_date', '<=', $toDate);
        $purchases = $purchaseQuery->paginate(15, ['*'], 'purchase_page');

        // Sales Book (invoices with invoice_number)
        $salesQuery = Invoice::with('customer')
            ->where('user_id', $userId)
            ->whereNotNull('invoice_number')
            ->orderByDesc('invoice_date');
        if ($fromDate) $salesQuery->whereDate('invoice_date', '>=', $fromDate);
        if ($toDate) $salesQuery->whereDate('invoice_date', '<=', $toDate);
        $sales = $salesQuery->paginate(15, ['*'], 'sales_page');

        // Purchase Returns
        $purchaseReturnQuery = PurchaseReturn::with('vendor')
            ->where('user_id', $userId)
            ->orderByDesc('return_date');
        if ($fromDate) $purchaseReturnQuery->whereDate('return_date', '>=', $fromDate);
        if ($toDate) $purchaseReturnQuery->whereDate('return_date', '<=', $toDate);
        $purchaseReturns = $purchaseReturnQuery->paginate(15, ['*'], 'pr_page');

        // Sales Returns
        $salesReturnQuery = SalesReturn::with('customer')
            ->where('user_id', $userId)
            ->orderByDesc('return_date');
        if ($fromDate) $salesReturnQuery->whereDate('return_date', '>=', $fromDate);
        if ($toDate) $salesReturnQuery->whereDate('return_date', '<=', $toDate);
        $salesReturns = $salesReturnQuery->paginate(15, ['*'], 'sr_page');

        // Summaries
        $purchaseSummary = [
            'count' => Purchase::where('user_id', $userId)->count(),
            'total' => Purchase::where('user_id', $userId)->sum('total'),
            'gst' => Purchase::where('user_id', $userId)->sum('gst_amount'),
        ];
        $salesSummary = [
            'count' => Invoice::where('user_id', $userId)->whereNotNull('invoice_number')->count(),
            'total' => Invoice::where('user_id', $userId)->whereNotNull('invoice_number')->sum('net_amount'),
            'gst' => Invoice::where('user_id', $userId)->whereNotNull('invoice_number')->sum('gst_amount'),
        ];
        $purchaseReturnSummary = [
            'count' => PurchaseReturn::where('user_id', $userId)->count(),
            'total' => PurchaseReturn::where('user_id', $userId)->sum('total'),
        ];
        $salesReturnSummary = [
            'count' => SalesReturn::where('user_id', $userId)->count(),
            'total' => SalesReturn::where('user_id', $userId)->sum('total'),
        ];

        return view('modules.books-registers', compact(
            'purchases', 'sales', 'purchaseReturns', 'salesReturns',
            'purchaseSummary', 'salesSummary', 'purchaseReturnSummary', 'salesReturnSummary',
            'fromDate', 'toDate'
        ));
    }

    public function reports(Request $request): View
    {
        $uploads = InvoiceUpload::where('user_id', auth()->id())
            ->where('source', 'report')
            ->orderByDesc('created_at')
            ->get();

        return view('modules.reports', compact('uploads'));
    }

    /**
     * Upload an invoice file (PDF / JPG / PNG) for report preview.
     */
    public function uploadReport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('report-uploads/' . auth()->id(), 'local');

        $upload = InvoiceUpload::create([
            'user_id'       => auth()->id(),
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'source'        => 'report',
        ]);

        return response()->json([
            'id'           => $upload->id,
            'original_name'=> $upload->original_name,
            'mime_type'    => $upload->mime_type,
            'size'         => $upload->size,
            'preview_url'  => route('modules.reports.preview', $upload->id),
            'delete_url'   => route('modules.reports.destroy', $upload->id),
            'created_at'   => $upload->created_at->format('d M Y, h:i A'),
        ], 201);
    }

    /**
     * Serve an uploaded report file for inline preview.
     */
    public function previewReport(int $upload): BinaryFileResponse
    {
        $record = InvoiceUpload::where('id', $upload)
            ->where('user_id', auth()->id())
            ->where('source', 'report')
            ->firstOrFail();

        if (! Storage::exists($record->path)) {
            abort(404);
        }

        $fullPath = Storage::path($record->path);

        return response()->file($fullPath, [
            'Content-Type' => $record->mime_type ?? 'application/octet-stream',
        ]);
    }

    /**
     * Delete an uploaded report file.
     */
    public function destroyReport(int $upload): JsonResponse
    {
        $record = InvoiceUpload::where('id', $upload)
            ->where('user_id', auth()->id())
            ->where('source', 'report')
            ->firstOrFail();

        Storage::delete($record->path);
        $record->forceDelete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
