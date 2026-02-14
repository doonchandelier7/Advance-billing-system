<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function __construct(
        protected StockService $stock,
    ) {}

    /**
     * Stock history for a product or all.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::query()->with(['product', 'user']);
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        $movements = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20));
        return response()->json($movements);
    }

    /**
     * Stock IN (purchase).
     */
    public function stockIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'reference_type' => 'nullable|string|max:64',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:512',
        ]);
        $product = Product::findOrFail($validated['product_id']);
        $movement = $this->stock->stockIn(
            $product,
            (float) $validated['quantity'],
            $validated['reference_type'] ?? null,
            $validated['reference_id'] ?? null,
            $validated['notes'] ?? null,
        );
        $movement->load(['product', 'user']);
        return response()->json($movement, 201);
    }

    /**
     * Stock OUT (sales).
     */
    public function stockOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'reference_type' => 'nullable|string|max:64',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:512',
        ]);
        $product = Product::findOrFail($validated['product_id']);
        $movement = $this->stock->stockOut(
            $product,
            (float) $validated['quantity'],
            $validated['reference_type'] ?? null,
            $validated['reference_id'] ?? null,
            $validated['notes'] ?? null,
        );
        $movement->load(['product', 'user']);
        return response()->json($movement, 201);
    }

    /**
     * Manual stock adjustment.
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'new_stock' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:512',
        ]);
        $product = Product::findOrFail($validated['product_id']);
        $movement = $this->stock->adjust(
            $product,
            (float) $validated['new_stock'],
            $validated['notes'] ?? null,
        );
        $movement->load(['product', 'user']);
        return response()->json($movement, 201);
    }

    /**
     * Low-stock alerts: products where stock <= low_stock_threshold.
     */
    public function lowStock(Request $request): JsonResponse
    {
        $products = Product::query()->lowStock()->with(['category', 'unit'])->get();
        return response()->json(['data' => $products]);
    }
}
