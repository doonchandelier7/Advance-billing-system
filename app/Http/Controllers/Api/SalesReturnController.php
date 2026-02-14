<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\ReturnNumberService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function __construct(
        protected ReturnNumberService $docNumber,
        protected StockService $stock,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = SalesReturn::query()->with(['customer', 'invoice', 'items.product'])->where('user_id', auth()->id());
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
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
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
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
            $docNumber = $this->docNumber->salesReturn();
            $return = SalesReturn::create([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'],
                'invoice_id' => $validated['invoice_id'] ?? null,
                'doc_number' => $docNumber,
                'return_date' => $validated['return_date'] ?? now()->toDateString(),
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $subtotal = 0;
            $gstTotal = 0;
            foreach ($validated['items'] as $i => $row) {
                $qty = (float) $row['quantity'];
                $rate = (float) $row['rate'];
                $amount = round($qty * $rate, 2);
                $product = \App\Models\Product::find($row['product_id']);
                SalesReturnItem::create([
                    'sales_return_id' => $return->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit' => $row['unit'] ?? $product->unit?->symbol,
                    'rate' => $rate,
                    'amount' => $amount,
                    'sort_order' => $i,
                ]);
                $this->stock->stockIn($product, $qty, 'sales_return', $return->id, "Sales Return #{$docNumber}", auth()->id());
                $subtotal += $amount;
            }
            $return->update(['subtotal' => $subtotal, 'gst_amount' => 0, 'total' => $subtotal]);
            return $return->load(['customer', 'items.product']);
        });

        return response()->json($return, 201);
    }

    public function show(SalesReturn $salesReturn): JsonResponse
    {
        if ($salesReturn->user_id !== auth()->id()) {
            abort(403);
        }
        $salesReturn->load(['customer', 'invoice', 'items.product']);
        return response()->json($salesReturn);
    }
}
