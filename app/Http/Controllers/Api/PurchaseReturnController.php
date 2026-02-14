<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\ReturnNumberService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function __construct(
        protected ReturnNumberService $docNumber,
        protected StockService $stock,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = PurchaseReturn::query()->with(['vendor', 'items.product'])->where('user_id', auth()->id());
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->input('to_date'));
        }
        $returns = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15));
        return response()->json($returns);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_id' => 'nullable|exists:purchases,id',
            'return_date' => 'nullable|date',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:32',
            'items.*.rate' => 'required|numeric|min:0',
        ]);

        $return = DB::transaction(function () use ($validated) {
            $docNumber = $this->docNumber->purchaseReturn();
            $return = PurchaseReturn::create([
                'user_id' => auth()->id(),
                'vendor_id' => $validated['vendor_id'],
                'purchase_id' => $validated['purchase_id'] ?? null,
                'doc_number' => $docNumber,
                'return_date' => $validated['return_date'] ?? now()->toDateString(),
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($validated['items'] as $i => $row) {
                $qty = (float) $row['quantity'];
                $rate = (float) $row['rate'];
                $amount = round($qty * $rate, 2);
                $product = \App\Models\Product::find($row['product_id']);
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit' => $row['unit'] ?? $product->unit?->symbol,
                    'rate' => $rate,
                    'amount' => $amount,
                    'sort_order' => $i,
                ]);
                $this->stock->stockOut($product, $qty, 'purchase_return', $return->id, "Purchase Return #{$docNumber}", auth()->id());
                $subtotal += $amount;
            }
            $return->update(['subtotal' => $subtotal, 'gst_amount' => 0, 'total' => $subtotal]);
            return $return->load(['vendor', 'items.product']);
        });

        return response()->json($return, 201);
    }

    public function show(PurchaseReturn $purchaseReturn): JsonResponse
    {
        if ($purchaseReturn->user_id !== auth()->id()) {
            abort(403);
        }
        $purchaseReturn->load(['vendor', 'items.product']);
        return response()->json($purchaseReturn);
    }
}
