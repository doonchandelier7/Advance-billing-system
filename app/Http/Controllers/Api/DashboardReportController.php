<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardReportController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $from = $request->input('from_date', now()->startOfMonth()->toDateString());
        $to = $request->input('to_date', now()->toDateString());

        $salesCount = Invoice::query()->where('user_id', $userId)
            ->whereBetween('invoice_date', [$from, $to])
            ->count();
        $salesTotal = Invoice::query()->where('user_id', $userId)
            ->whereBetween('invoice_date', [$from, $to])
            ->sum('net_amount');

        $purchaseCount = Purchase::query()->where('user_id', $userId)
            ->whereBetween('purchase_date', [$from, $to])
            ->count();
        $purchaseTotal = Purchase::query()->where('user_id', $userId)
            ->whereBetween('purchase_date', [$from, $to])
            ->sum('total');

        $lowStockCount = Product::query()->lowStock()->count();

        return response()->json([
            'from_date' => $from,
            'to_date' => $to,
            'sales' => [
                'count' => $salesCount,
                'total' => round($salesTotal, 2),
            ],
            'purchases' => [
                'count' => $purchaseCount,
                'total' => round($purchaseTotal, 2),
            ],
            'low_stock_products_count' => $lowStockCount,
        ]);
    }
}
