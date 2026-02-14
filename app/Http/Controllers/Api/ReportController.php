<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $query = Invoice::query()->with(['customer', 'items'])
            ->where('user_id', auth()->id())
            ->whereNotNull('invoice_number');
        $this->applyDateFilter($query, $request, 'invoice_date');
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        $data = $query->orderBy('invoice_date')->get();
        $summary = [
            'total_invoices' => $data->count(),
            'total_net_amount' => round($data->sum('net_amount'), 2),
            'total_taxable' => round($data->sum('taxable_amount'), 2),
            'total_gst' => round($data->sum('gst_amount'), 2),
        ];
        return response()->json(['data' => $data, 'summary' => $summary]);
    }

    public function purchase(Request $request): JsonResponse
    {
        $query = Purchase::query()->with(['vendor', 'items'])
            ->where('user_id', auth()->id());
        $this->applyDateFilter($query, $request, 'purchase_date');
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }
        $data = $query->orderBy('purchase_date')->get();
        $summary = [
            'total_purchases' => $data->count(),
            'total_amount' => round($data->sum('total'), 2),
            'total_subtotal' => round($data->sum('subtotal'), 2),
            'total_gst' => round($data->sum('gst_amount'), 2),
        ];
        return response()->json(['data' => $data, 'summary' => $summary]);
    }

    public function stock(Request $request): JsonResponse
    {
        $query = \App\Models\Product::query()->with(['category', 'unit'])
            ->where('is_active', true);
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }
        $data = $query->orderBy('name')->get();
        $summary = [
            'total_products' => $data->count(),
            'total_stock_value' => round($data->sum(fn ($p) => $p->stock * $p->sale_rate), 2),
            'low_stock_count' => $data->filter(fn ($p) => $p->low_stock_threshold !== null && $p->stock <= $p->low_stock_threshold)->count(),
        ];
        return response()->json(['data' => $data, 'summary' => $summary]);
    }

    public function returns(Request $request): JsonResponse
    {
        $salesReturns = SalesReturn::query()->with(['customer', 'items'])
            ->where('user_id', auth()->id());
        $purchaseReturns = PurchaseReturn::query()->with(['vendor', 'items'])
            ->where('user_id', auth()->id());
        $this->applyDateFilter($salesReturns, $request, 'return_date');
        $this->applyDateFilter($purchaseReturns, $request, 'return_date');
        if ($request->filled('customer_id')) {
            $salesReturns->where('customer_id', $request->input('customer_id'));
        }
        if ($request->filled('vendor_id')) {
            $purchaseReturns->where('vendor_id', $request->input('vendor_id'));
        }
        return response()->json([
            'sales_returns' => $salesReturns->orderByDesc('return_date')->get(),
            'purchase_returns' => $purchaseReturns->orderByDesc('return_date')->get(),
        ]);
    }

    public function gstSummary(Request $request): JsonResponse
    {
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to = $request->input('to_date', now()->toDateString());

        $sales = Invoice::query()->where('user_id', auth()->id())
            ->whereBetween('invoice_date', [$from, $to])
            ->selectRaw('SUM(taxable_amount) as taxable, SUM(cgst_amount) as cgst, SUM(sgst_amount) as sgst, SUM(igst_amount) as igst, SUM(gst_amount) as total_gst')
            ->first();

        $purchases = Purchase::query()->where('user_id', auth()->id())
            ->whereBetween('purchase_date', [$from, $to])
            ->selectRaw('SUM(subtotal) as taxable, SUM(gst_amount) as total_gst')
            ->first();

        return response()->json([
            'from_date' => $from,
            'to_date' => $to,
            'sales' => [
                'taxable_value' => (float) ($sales->taxable ?? 0),
                'cgst' => (float) ($sales->cgst ?? 0),
                'sgst' => (float) ($sales->sgst ?? 0),
                'igst' => (float) ($sales->igst ?? 0),
                'total_gst' => (float) ($sales->total_gst ?? 0),
            ],
            'purchases' => [
                'taxable_value' => (float) ($purchases->taxable ?? 0),
                'total_gst' => (float) ($purchases->total_gst ?? 0),
            ],
        ]);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to = $request->input('to_date', now()->toDateString());

        $salesTotal = Invoice::query()->where('user_id', auth()->id())
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('net_amount');

        $purchaseTotal = Purchase::query()->where('user_id', auth()->id())
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('total');

        $salesReturnTotal = SalesReturn::query()->where('user_id', auth()->id())
            ->whereBetween('return_date', [$from, $to])
            ->sum('total');

        $purchaseReturnTotal = PurchaseReturn::query()->where('user_id', auth()->id())
            ->whereBetween('return_date', [$from, $to])
            ->sum('total');

        $grossProfit = $salesTotal - $salesReturnTotal - $purchaseTotal + $purchaseReturnTotal;

        return response()->json([
            'from_date' => $from,
            'to_date' => $to,
            'sales_total' => round($salesTotal, 2),
            'purchase_total' => round($purchaseTotal, 2),
            'sales_return_total' => round($salesReturnTotal, 2),
            'purchase_return_total' => round($purchaseReturnTotal, 2),
            'gross_profit' => round($grossProfit, 2),
        ]);
    }

    protected function applyDateFilter($query, Request $request, string $column): void
    {
        if ($request->filled('from_date')) {
            $query->whereDate($column, '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate($column, '<=', $request->input('to_date'));
        }
    }
}
