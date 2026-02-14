<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\InvoiceNumberService;
use App\Services\PurchaseNumberService;
use App\Services\ReturnNumberService;
use App\Services\StockService;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumber,
        protected PurchaseNumberService $purchaseNumber,
        protected ReturnNumberService $returnNumber,
        protected StockService $stock,
    ) {}

    /**
     * Show create sale page.
     */
    public function createSale(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        return view('modules.transactions.create-sale', compact('customers', 'products'));
    }

    /**
     * Show create purchase page.
     */
    public function createPurchase(): View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        return view('modules.transactions.create-purchase', compact('vendors', 'products'));
    }

    /**
     * Show create purchase return page.
     */
    public function createPurchaseReturn(): View
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        return view('modules.transactions.create-purchase-return', compact('vendors', 'products'));
    }

    /**
     * Show create sales return page.
     */
    public function createSalesReturn(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        return view('modules.transactions.create-sales-return', compact('customers', 'products'));
    }

    /**
     * Store a new Sale (Invoice).
     */
    public function storeSale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'document_type' => 'nullable|string|max:64',
            'invoice_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:32',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'gr_number' => 'nullable|string|max:64',
            'gr_date' => 'nullable|date',
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $invoiceNumber = $this->invoiceNumber->generate();
                $customer = isset($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;

                $invoice = Invoice::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'invoice_number' => $invoiceNumber,
                    'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                    'doc_number' => $invoiceNumber,
                    'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'party_name' => $validated['party_name'] ?? $customer?->name,
                    'city' => $validated['city'] ?? $customer?->city,
                    'state' => $validated['state'] ?? $customer?->state,
                    'gstin' => $validated['gstin'] ?? $customer?->gstin,
                    'gr_number' => $validated['gr_number'] ?? null,
                    'gr_date' => $validated['gr_date'] ?? null,
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

                foreach ($validated['items'] as $i => $row) {
                    $quantity = (float) $row['quantity'];
                    $rate = (float) $row['rate'];
                    $gstPercent = isset($row['gst_percent']) && $row['gst_percent'] !== null ? (float) $row['gst_percent'] : null;
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
                        'product_name' => $product?->name,
                        'hsn_code' => $product?->hsn_code,
                        'quantity' => $quantity,
                        'unit' => $product?->unit?->symbol,
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
                    'igst_amount' => 0,
                    'net_amount' => $netAmount,
                    'advance_amount' => $advance > 0 ? $advance : null,
                    'balance_amount' => $balance != 0 ? $balance : null,
                ]);
            });

            return redirect()->route('modules.transactions', ['#sales'])->with('success', 'Sale invoice created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('modules.transactions', ['#sales'])->with('error', 'Failed to create sale: ' . $e->getMessage());
        }
    }

    /**
     * Store a new Purchase.
     */
    public function storePurchase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'document_type' => 'nullable|string|max:64',
            'purchase_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:32',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'gr_number' => 'nullable|string|max:64',
            'gr_date' => 'nullable|date',
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $docNumber = $this->purchaseNumber->generate();
                $purchase = Purchase::create([
                    'user_id' => auth()->id(),
                    'vendor_id' => $validated['vendor_id'],
                    'doc_number' => $docNumber,
                    'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                    'purchase_date' => $validated['purchase_date'] ?? now()->toDateString(),
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'party_name' => $validated['party_name'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'gstin' => $validated['gstin'] ?? null,
                    'gr_number' => $validated['gr_number'] ?? null,
                    'gr_date' => $validated['gr_date'] ?? null,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                    'transport_name' => $validated['transport_name'] ?? null,
                    'place_of_supply' => $validated['place_of_supply'] ?? null,
                    'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                    'distance_km' => $validated['distance_km'] ?? null,
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $subtotal = 0;
                $gstTotal = 0;
                foreach ($validated['items'] as $i => $row) {
                    $qty = (float) $row['quantity'];
                    $rate = (float) $row['rate'];
                    $gstPct = isset($row['gst_percent']) && $row['gst_percent'] !== null ? (float) $row['gst_percent'] : null;
                    $itemTaxable = round($qty * $rate, 2);
                    $itemGst = $gstPct ? round($itemTaxable * ($gstPct / 100), 2) : 0;
                    $amount = $itemTaxable + $itemGst;

                    $product = Product::find($row['product_id']);
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit' => $product->unit?->symbol,
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
            });

            return redirect()->route('modules.transactions', ['#purchases'])->with('success', 'Purchase recorded successfully!');
        } catch (\Exception $e) {
            return redirect()->route('modules.transactions', ['#purchases'])->with('error', 'Failed to create purchase: ' . $e->getMessage());
        }
    }

    /**
     * Store a new Purchase Return.
     */
    public function storePurchaseReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'document_type' => 'nullable|string|max:64',
            'return_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:32',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'gr_number' => 'nullable|string|max:64',
            'gr_date' => 'nullable|date',
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $docNumber = $this->returnNumber->purchaseReturn();
                $return = PurchaseReturn::create([
                    'user_id' => auth()->id(),
                    'vendor_id' => $validated['vendor_id'],
                    'doc_number' => $docNumber,
                    'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                    'return_date' => $validated['return_date'] ?? now()->toDateString(),
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'party_name' => $validated['party_name'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'gstin' => $validated['gstin'] ?? null,
                    'gr_number' => $validated['gr_number'] ?? null,
                    'gr_date' => $validated['gr_date'] ?? null,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                    'transport_name' => $validated['transport_name'] ?? null,
                    'place_of_supply' => $validated['place_of_supply'] ?? null,
                    'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                    'distance_km' => $validated['distance_km'] ?? null,
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $subtotal = 0;
                $gstTotal = 0;
                foreach ($validated['items'] as $i => $row) {
                    $qty = (float) $row['quantity'];
                    $rate = (float) $row['rate'];
                    $gstPct = isset($row['gst_percent']) && $row['gst_percent'] !== null ? (float) $row['gst_percent'] : null;
                    $itemTaxable = round($qty * $rate, 2);
                    $itemGst = $gstPct ? round($itemTaxable * ($gstPct / 100), 2) : 0;
                    $amount = $itemTaxable + $itemGst;

                    $product = Product::find($row['product_id']);
                    PurchaseReturnItem::create([
                        'purchase_return_id' => $return->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'hsn_code' => $product->hsn_code,
                        'quantity' => $qty,
                        'unit' => $product->unit?->symbol,
                        'rate' => $rate,
                        'gst_percent' => $gstPct,
                        'amount' => $amount,
                        'sort_order' => $i,
                    ]);
                    $this->stock->stockOut($product, $qty, 'purchase_return', $return->id, "Purchase Return #{$docNumber}", auth()->id());
                    $subtotal += $itemTaxable;
                    $gstTotal += $itemGst;
                }
                $return->update(['subtotal' => $subtotal, 'gst_amount' => $gstTotal, 'total' => $subtotal + $gstTotal]);
            });

            return redirect()->route('modules.transactions', ['#purchase-returns'])->with('success', 'Purchase return recorded successfully!');
        } catch (\Exception $e) {
            return redirect()->route('modules.transactions', ['#purchase-returns'])->with('error', 'Failed to create purchase return: ' . $e->getMessage());
        }
    }

    /**
     * Store a new Sales Return.
     */
    public function storeSalesReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'document_type' => 'nullable|string|max:64',
            'return_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:32',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'gr_number' => 'nullable|string|max:64',
            'gr_date' => 'nullable|date',
            'driver_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'reference' => 'nullable|string|max:191',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $docNumber = $this->returnNumber->salesReturn();
                $return = SalesReturn::create([
                    'user_id' => auth()->id(),
                    'customer_id' => $validated['customer_id'],
                    'doc_number' => $docNumber,
                    'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                    'return_date' => $validated['return_date'] ?? now()->toDateString(),
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'party_name' => $validated['party_name'] ?? null,
                    'city' => $validated['city'] ?? null,
                    'state' => $validated['state'] ?? null,
                    'gstin' => $validated['gstin'] ?? null,
                    'gr_number' => $validated['gr_number'] ?? null,
                    'gr_date' => $validated['gr_date'] ?? null,
                    'driver_name' => $validated['driver_name'] ?? null,
                    'vehicle_number' => $validated['vehicle_number'] ?? null,
                    'transport_name' => $validated['transport_name'] ?? null,
                    'place_of_supply' => $validated['place_of_supply'] ?? null,
                    'eway_bill_no' => $validated['eway_bill_no'] ?? null,
                    'distance_km' => $validated['distance_km'] ?? null,
                    'reference' => $validated['reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);

                $subtotal = 0;
                $gstTotal = 0;
                foreach ($validated['items'] as $i => $row) {
                    $qty = (float) $row['quantity'];
                    $rate = (float) $row['rate'];
                    $gstPct = isset($row['gst_percent']) && $row['gst_percent'] !== null ? (float) $row['gst_percent'] : null;
                    $itemTaxable = round($qty * $rate, 2);
                    $itemGst = $gstPct ? round($itemTaxable * ($gstPct / 100), 2) : 0;
                    $amount = $itemTaxable + $itemGst;

                    $product = Product::find($row['product_id']);
                    SalesReturnItem::create([
                        'sales_return_id' => $return->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'hsn_code' => $product->hsn_code,
                        'quantity' => $qty,
                        'unit' => $product->unit?->symbol,
                        'rate' => $rate,
                        'gst_percent' => $gstPct,
                        'amount' => $amount,
                        'sort_order' => $i,
                    ]);
                    $this->stock->stockIn($product, $qty, 'sales_return', $return->id, "Sales Return #{$docNumber}", auth()->id());
                    $subtotal += $itemTaxable;
                    $gstTotal += $itemGst;
                }
                $return->update(['subtotal' => $subtotal, 'gst_amount' => $gstTotal, 'total' => $subtotal + $gstTotal]);
            });

            return redirect()->route('modules.transactions', ['#sales-returns'])->with('success', 'Sales return recorded successfully!');
        } catch (\Exception $e) {
            return redirect()->route('modules.transactions', ['#sales-returns'])->with('error', 'Failed to create sales return: ' . $e->getMessage());
        }
    }
}
