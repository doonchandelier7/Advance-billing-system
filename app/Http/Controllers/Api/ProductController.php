<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with(['category', 'unit']);
        if ($request->boolean('active_only')) {
            $query->where('is_active', true);
        }
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('hsn_code', 'like', "%{$s}%");
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('hsn_code')) {
            $query->where('hsn_code', preg_replace('/\D+/', '', (string) $request->input('hsn_code')));
        }
        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }
        $products = $query->orderBy('name')->paginate($request->integer('per_page', 15));
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:64|unique:products,code',
            'hsn_code' => ['nullable', 'string', 'max:32', 'regex:/^\d{4}(\d{2}(\d{2})?)?$/'],
            'description' => 'nullable|string',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        $validated['hsn_code'] = isset($validated['hsn_code']) ? preg_replace('/\D+/', '', $validated['hsn_code']) : null;
        $product = Product::create($validated);
        $product->load(['category', 'unit']);
        return response()->json($product, 201);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'unit']);
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'name' => 'sometimes|string|max:191',
            'code' => 'nullable|string|max:64|unique:products,code,'.$product->id,
            'hsn_code' => ['nullable', 'string', 'max:32', 'regex:/^\d{4}(\d{2}(\d{2})?)?$/'],
            'description' => 'nullable|string',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'nullable|numeric|min:0',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);
        if (array_key_exists('hsn_code', $validated)) {
            $validated['hsn_code'] = preg_replace('/\D+/', '', (string) $validated['hsn_code']);
        }
        $product->update($validated);
        $product->load(['category', 'unit']);
        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
