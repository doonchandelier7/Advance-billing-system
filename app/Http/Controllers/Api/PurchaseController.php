<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Vendor;
use App\Services\PurchaseNumberService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseNumberService $docNumber,
        protected StockService $stock,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Purchase::query()->with(['vendor', 'items.product'])->where('user_id', auth()->id());
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->input('vendor_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('purchase_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('purchase_date', '<=', $request->input('to_date'));
        }
        $purchases = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15));
        return response()->json($purchases);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'purchase_date' => 'nullable|date',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:32',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $purchase = DB::transaction(function () use ($validated) {
            $docNumber = $this->docNumber->generate();
            $purchase = Purchase::create([
                'user_id' => auth()->id(),
                'vendor_id' => $validated['vendor_id'],
                'doc_number' => $docNumber,
                'purchase_date' => $validated['purchase_date'] ?? now()->toDateString(),
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0;
            $gstTotal = 0;
            foreach ($validated['items'] as $i => $row) {
                $qty = (float) $row['quantity'];
                $rate = (float) $row['rate'];
                $gstPct = isset($row['gst_percent']) ? (float) $row['gst_percent'] : null;
                $itemTaxable = round($qty * $rate, 2);
                $itemGst = $gstPct ? round($itemTaxable * ($gstPct / 100), 2) : 0;
                $amount = $itemTaxable + $itemGst;

                $product = \App\Models\Product::find($row['product_id']);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit' => $row['unit'] ?? $product->unit?->symbol,
                    'rate' => $rate,
                    'gst_percent' => $gstPct,
                    'amount' => $amount,
                    'sort_order' => $i,
                ]);
                $this->stock->stockIn($product, $qty, 'purchase', $purchase->id, "Purchase #{$docNumber}", auth()->id());
                $subtotal += $itemTaxable;
                $gstTotal += $itemGst;
            }

            $purchase->update([
                'subtotal' => $subtotal,
                'gst_amount' => $gstTotal,
                'total' => $subtotal + $gstTotal,
            ]);
            return $purchase->load(['vendor', 'items.product']);
        });

        return response()->json($purchase, 201);
    }

    public function show(Purchase $purchase): JsonResponse
    {
        if ($purchase->user_id !== auth()->id()) {
            abort(403);
        }
        $purchase->load(['vendor', 'items.product']);
        return response()->json($purchase);
    }
}
