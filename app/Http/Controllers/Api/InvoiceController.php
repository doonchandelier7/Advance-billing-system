<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Services\InvoiceNumberService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumber,
        protected StockService $stock,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()->with(['customer', 'items.product'])->where('user_id', auth()->id());
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->input('to_date'));
        }
        $invoices = $query->orderByDesc('created_at')->paginate($request->integer('per_page', 15));
        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'document_type' => 'nullable|string|max:64',
            'invoice_date' => 'nullable|date',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'driver_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'nullable|string|max:191',
            'items.*.hsn_code' => 'nullable|string|max:32',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:32',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $invoice = DB::transaction(function () use ($validated) {
            $invoiceNumber = $this->invoiceNumber->generate();
            $customer = $validated['customer_id'] ? Customer::find($validated['customer_id']) : null;

            $invoice = Invoice::create([
                'user_id' => auth()->id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'invoice_number' => $invoiceNumber,
                'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                'doc_number' => $invoiceNumber,
                'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
                'party_name' => $validated['party_name'] ?? $customer?->name,
                'city' => $validated['city'] ?? $customer?->city,
                'state' => $validated['state'] ?? $customer?->state,
                'gstin' => $validated['gstin'] ?? $customer?->gstin,
                'transport_name' => $validated['transport_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver_name' => $validated['driver_name'] ?? null,
                'place_of_supply' => $validated['place_of_supply'] ?? null,
                'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                'distance_km' => $validated['distance_km'] ?? null,
                'advance_amount' => $validated['advance_amount'] ?? null,
            ]);

            $taxableAmount = 0;
            $gstAmount = 0;
            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = 0;

            foreach ($validated['items'] as $i => $row) {
                $quantity = (float) $row['quantity'];
                $rate = (float) $row['rate'];
                $gstPercent = isset($row['gst_percent']) ? (float) $row['gst_percent'] : null;
                $product = isset($row['product_id']) ? Product::find($row['product_id']) : null;

                $itemTaxable = round($quantity * $rate, 2);
                $itemGst = $gstPercent ? round($itemTaxable * ($gstPercent / 100), 2) : 0;
                $itemAmount = $itemTaxable + $itemGst;

                $taxableAmount += $itemTaxable;
                $gstAmount += $itemGst;
                if ($gstPercent) {
                    $half = round($itemGst / 2, 2);
                    $cgstAmount += $half;
                    $sgstAmount += $half;
                }

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $row['product_id'] ?? null,
                    'product_name' => $row['product_name'] ?? $product?->name,
                    'hsn_code' => $row['hsn_code'] ?? $product?->hsn_code,
                    'quantity' => $quantity,
                    'unit' => $row['unit'] ?? $product?->unit?->symbol,
                    'rate' => $rate,
                    'gst_percent' => $gstPercent,
                    'amount' => $itemAmount,
                    'sort_order' => $i,
                ]);

                if ($product && $quantity > 0) {
                    $this->stock->stockOut($product, $quantity, 'sale', $invoice->id, "Invoice #{$invoiceNumber}", auth()->id());
                }
            }

            $netAmount = $taxableAmount + $gstAmount;
            $advance = (float) ($validated['advance_amount'] ?? 0);
            $balance = $netAmount - $advance;

            $invoice->update([
                'taxable_amount' => $taxableAmount,
                'gst_amount' => $gstAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'igst_amount' => $igstAmount,
                'net_amount' => $netAmount,
                'advance_amount' => $advance > 0 ? $advance : null,
                'balance_amount' => $balance != 0 ? $balance : null,
            ]);

            return $invoice->load(['customer', 'items.product']);
        });

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        $invoice->load(['customer', 'items.product']);
        return response()->json($invoice);
    }
}
