<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTemplate;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Services\InvoiceNumberService;
use App\Services\InvoiceTemplateBindingService;
use App\Services\PurchaseNumberService;
use App\Services\ReturnNumberService;
use App\Services\StockService;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumber,
        protected PurchaseNumberService $purchaseNumber,
        protected ReturnNumberService $returnNumber,
        protected StockService $stock,
        protected InvoiceTemplateBindingService $binding,
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
     * Generate / preview purchase with selected template.
     */
    public function generatePurchase(Request $request, Purchase $purchase): View
    {
        if ($purchase->user_id !== Auth::id()) {
            abort(403);
        }

        $purchase->load(['vendor', 'items.product']);
        $document = $this->buildDocumentFromPurchase($purchase);
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $templateId = $request->query('template', $templates->where('is_default', true)->first()?->id ?? $templates->first()?->id);
        $template = $templates->firstWhere('id', (int) $templateId);
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('modules.transactions.generate-document', [
            'document' => $document,
            'record' => $purchase,
            'templates' => $templates,
            'template' => $template,
            'renderedHtml' => $renderedHtml,
            'documentLabel' => 'Purchase',
            'generateRouteName' => 'modules.transactions.purchases.generate',
            'printRouteName' => 'modules.transactions.purchases.print',
            'backTab' => 'purchases',
        ]);
    }

    /**
     * Print-ready purchase view.
     */
    public function printPurchase(Request $request, Purchase $purchase): View
    {
        if ($purchase->user_id !== Auth::id()) {
            abort(403);
        }

        $purchase->load(['vendor', 'items.product']);
        $document = $this->buildDocumentFromPurchase($purchase);
        $template = InvoiceTemplate::find($request->query('template'));
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('invoices.print', ['invoice' => $document, 'template' => $template, 'renderedHtml' => $renderedHtml]);
    }

    /**
     * Generate / preview purchase return with selected template.
     */
    public function generatePurchaseReturn(Request $request, PurchaseReturn $purchaseReturn): View
    {
        if ($purchaseReturn->user_id !== Auth::id()) {
            abort(403);
        }

        $purchaseReturn->load(['vendor', 'items.product']);
        $document = $this->buildDocumentFromPurchaseReturn($purchaseReturn);
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $templateId = $request->query('template', $templates->where('is_default', true)->first()?->id ?? $templates->first()?->id);
        $template = $templates->firstWhere('id', (int) $templateId);
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('modules.transactions.generate-document', [
            'document' => $document,
            'record' => $purchaseReturn,
            'templates' => $templates,
            'template' => $template,
            'renderedHtml' => $renderedHtml,
            'documentLabel' => 'Purchase Return',
            'generateRouteName' => 'modules.transactions.purchase-returns.generate',
            'printRouteName' => 'modules.transactions.purchase-returns.print',
            'backTab' => 'purchase-returns',
        ]);
    }

    /**
     * Print-ready purchase return view.
     */
    public function printPurchaseReturn(Request $request, PurchaseReturn $purchaseReturn): View
    {
        if ($purchaseReturn->user_id !== Auth::id()) {
            abort(403);
        }

        $purchaseReturn->load(['vendor', 'items.product']);
        $document = $this->buildDocumentFromPurchaseReturn($purchaseReturn);
        $template = InvoiceTemplate::find($request->query('template'));
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('invoices.print', ['invoice' => $document, 'template' => $template, 'renderedHtml' => $renderedHtml]);
    }

    /**
     * Generate / preview sales return with selected template.
     */
    public function generateSalesReturn(Request $request, SalesReturn $salesReturn): View
    {
        if ($salesReturn->user_id !== Auth::id()) {
            abort(403);
        }

        $salesReturn->load(['customer', 'items.product']);
        $document = $this->buildDocumentFromSalesReturn($salesReturn);
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $templateId = $request->query('template', $templates->where('is_default', true)->first()?->id ?? $templates->first()?->id);
        $template = $templates->firstWhere('id', (int) $templateId);
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('modules.transactions.generate-document', [
            'document' => $document,
            'record' => $salesReturn,
            'templates' => $templates,
            'template' => $template,
            'renderedHtml' => $renderedHtml,
            'documentLabel' => 'Sales Return',
            'generateRouteName' => 'modules.transactions.sales-returns.generate',
            'printRouteName' => 'modules.transactions.sales-returns.print',
            'backTab' => 'sales-returns',
        ]);
    }

    /**
     * Print-ready sales return view.
     */
    public function printSalesReturn(Request $request, SalesReturn $salesReturn): View
    {
        if ($salesReturn->user_id !== Auth::id()) {
            abort(403);
        }

        $salesReturn->load(['customer', 'items.product']);
        $document = $this->buildDocumentFromSalesReturn($salesReturn);
        $template = InvoiceTemplate::find($request->query('template'));
        $renderedHtml = $template ? $this->binding->bind($document, $template) : null;

        return view('invoices.print', ['invoice' => $document, 'template' => $template, 'renderedHtml' => $renderedHtml]);
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
            'district' => 'nullable|string|max:191',
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
            'after_action' => 'nullable|in:generate,print',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated) {
                $invoiceNumber = $this->invoiceNumber->generate();
                $customer = isset($validated['customer_id']) ? Customer::find($validated['customer_id']) : null;

                $invoice = Invoice::create([
                    'user_id' => Auth::id(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'invoice_number' => $invoiceNumber,
                    'document_type' => $validated['document_type'] ?? 'Tax Invoice',
                    'doc_number' => $invoiceNumber,
                    'invoice_date' => $validated['invoice_date'] ?? now()->toDateString(),
                    'payment_mode' => $validated['payment_mode'] ?? 'CASH',
                    'party_name' => $validated['party_name'] ?? $customer?->name,
                    'city' => $validated['city'] ?? $customer?->city,
                    'district' => $validated['district'] ?? $customer?->district,
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
                        $this->stock->stockOut($product, $quantity, 'sale', $invoice->id, "Invoice #{$invoiceNumber}", Auth::id());
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

                return $invoice;
            });

            $afterAction = $request->input('after_action', 'generate');
            if ($afterAction === 'print') {
                return redirect()->route('invoices.print', ['invoice' => $invoice->id])
                    ->with('success', 'Sale invoice created successfully. Opening print view.');
            }

            return redirect()->route('invoices.generate', ['invoice' => $invoice->id])
                ->with('success', 'Sale invoice created successfully. Opening generate view.');
        } catch (\Throwable $e) {
            $errorKey = stripos($e->getMessage(), 'stock') !== false ? 'items' : 'form';
            return back()
                ->withInput()
                ->withErrors([$errorKey => $e->getMessage()])
                ->with('error', 'Failed to create sale. No data was saved: ' . $e->getMessage());
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
            'after_action' => 'nullable|in:generate,print',
        ]);

        try {
            $purchase = DB::transaction(function () use ($validated) {
                $docNumber = $this->purchaseNumber->generate();
                $purchase = Purchase::create([
                    'user_id' => Auth::id(),
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
                    $this->stock->stockIn($product, $qty, 'purchase', $purchase->id, "Purchase #{$docNumber}", Auth::id());
                    $subtotal += $itemTaxable;
                    $gstTotal += $itemGst;
                }

                $purchase->update([
                    'subtotal' => $subtotal,
                    'gst_amount' => $gstTotal,
                    'total' => $subtotal + $gstTotal,
                ]);

                return $purchase;
            });

            $afterAction = $request->input('after_action', 'generate');
            if ($afterAction === 'print') {
                return redirect()->route('modules.transactions.purchases.print', ['purchase' => $purchase->id])
                    ->with('success', 'Purchase recorded successfully. Opening print view.');
            }

            return redirect()->route('modules.transactions.purchases.generate', ['purchase' => $purchase->id])
                ->with('success', 'Purchase recorded successfully. Opening generate view.');
        } catch (\Throwable $e) {
            $errorKey = stripos($e->getMessage(), 'stock') !== false ? 'items' : 'form';
            return back()
                ->withInput()
                ->withErrors([$errorKey => $e->getMessage()])
                ->with('error', 'Failed to create purchase. No data was saved: ' . $e->getMessage());
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
            'after_action' => 'nullable|in:generate,print',
        ]);

        try {
            $return = DB::transaction(function () use ($validated) {
                $docNumber = $this->returnNumber->purchaseReturn();
                $return = PurchaseReturn::create([
                    'user_id' => Auth::id(),
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
                    $this->stock->stockOut($product, $qty, 'purchase_return', $return->id, "Purchase Return #{$docNumber}", Auth::id());
                    $subtotal += $itemTaxable;
                    $gstTotal += $itemGst;
                }
                $return->update(['subtotal' => $subtotal, 'gst_amount' => $gstTotal, 'total' => $subtotal + $gstTotal]);

                return $return;
            });

            $afterAction = $request->input('after_action', 'generate');
            if ($afterAction === 'print') {
                return redirect()->route('modules.transactions.purchase-returns.print', ['purchaseReturn' => $return->id])
                    ->with('success', 'Purchase return recorded successfully. Opening print view.');
            }

            return redirect()->route('modules.transactions.purchase-returns.generate', ['purchaseReturn' => $return->id])
                ->with('success', 'Purchase return recorded successfully. Opening generate view.');
        } catch (\Throwable $e) {
            $errorKey = stripos($e->getMessage(), 'stock') !== false ? 'items' : 'form';
            return back()
                ->withInput()
                ->withErrors([$errorKey => $e->getMessage()])
                ->with('error', 'Failed to create purchase return. No data was saved: ' . $e->getMessage());
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
            'after_action' => 'nullable|in:generate,print',
        ]);

        try {
            $return = DB::transaction(function () use ($validated) {
                $docNumber = $this->returnNumber->salesReturn();
                $return = SalesReturn::create([
                    'user_id' => Auth::id(),
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
                    $this->stock->stockIn($product, $qty, 'sales_return', $return->id, "Sales Return #{$docNumber}", Auth::id());
                    $subtotal += $itemTaxable;
                    $gstTotal += $itemGst;
                }
                $return->update(['subtotal' => $subtotal, 'gst_amount' => $gstTotal, 'total' => $subtotal + $gstTotal]);

                return $return;
            });

            $afterAction = $request->input('after_action', 'generate');
            if ($afterAction === 'print') {
                return redirect()->route('modules.transactions.sales-returns.print', ['salesReturn' => $return->id])
                    ->with('success', 'Sales return recorded successfully. Opening print view.');
            }

            return redirect()->route('modules.transactions.sales-returns.generate', ['salesReturn' => $return->id])
                ->with('success', 'Sales return recorded successfully. Opening generate view.');
        } catch (\Throwable $e) {
            $errorKey = stripos($e->getMessage(), 'stock') !== false ? 'items' : 'form';
            return back()
                ->withInput()
                ->withErrors([$errorKey => $e->getMessage()])
                ->with('error', 'Failed to create sales return. No data was saved: ' . $e->getMessage());
        }
    }

    /**
     * Convert purchase into an invoice-like document for template binding.
     */
    protected function buildDocumentFromPurchase(Purchase $purchase): Invoice
    {
        $document = new Invoice([
            'invoice_number' => $purchase->doc_number,
            'doc_number' => $purchase->doc_number,
            'document_type' => $purchase->document_type,
            'invoice_date' => $purchase->purchase_date,
            'payment_mode' => $purchase->payment_mode,
            'party_name' => $purchase->party_name ?: $purchase->vendor?->name,
            'city' => $purchase->city ?: $purchase->vendor?->city,
            'state' => $purchase->state ?: $purchase->vendor?->state,
            'gstin' => $purchase->gstin ?: $purchase->vendor?->gstin,
            'gr_number' => $purchase->gr_number,
            'gr_date' => $purchase->gr_date,
            'transport_name' => $purchase->transport_name,
            'vehicle_number' => $purchase->vehicle_number,
            'driver_name' => $purchase->driver_name,
            'place_of_supply' => $purchase->place_of_supply,
            'eway_bill_no' => $purchase->eway_bill_no,
            'distance_km' => $purchase->distance_km,
            'taxable_amount' => $purchase->subtotal,
            'gst_amount' => $purchase->gst_amount,
            'cgst_amount' => (float) $purchase->gst_amount / 2,
            'sgst_amount' => (float) $purchase->gst_amount / 2,
            'igst_amount' => 0,
            'net_amount' => $purchase->total,
            'notes' => $purchase->notes,
        ]);

        $items = $purchase->items->map(function ($item) {
            return new InvoiceItem([
                'product_name' => $item->product_name ?? $item->product?->name,
                'hsn_code' => $item->hsn_code ?? $item->product?->hsn_code,
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? $item->product?->unit?->symbol,
                'rate' => $item->rate,
                'gst_percent' => $item->gst_percent,
                'amount' => $item->amount,
                'sort_order' => $item->sort_order,
            ]);
        });

        $document->setRelation('customer', null);
        $document->setRelation('items', $items);

        return $document;
    }

    /**
     * Convert purchase return into an invoice-like document for template binding.
     */
    protected function buildDocumentFromPurchaseReturn(PurchaseReturn $purchaseReturn): Invoice
    {
        $document = new Invoice([
            'invoice_number' => $purchaseReturn->doc_number,
            'doc_number' => $purchaseReturn->doc_number,
            'document_type' => $purchaseReturn->document_type,
            'invoice_date' => $purchaseReturn->return_date,
            'payment_mode' => $purchaseReturn->payment_mode,
            'party_name' => $purchaseReturn->party_name ?: $purchaseReturn->vendor?->name,
            'city' => $purchaseReturn->city ?: $purchaseReturn->vendor?->city,
            'state' => $purchaseReturn->state ?: $purchaseReturn->vendor?->state,
            'gstin' => $purchaseReturn->gstin ?: $purchaseReturn->vendor?->gstin,
            'gr_number' => $purchaseReturn->gr_number,
            'gr_date' => $purchaseReturn->gr_date,
            'transport_name' => $purchaseReturn->transport_name,
            'vehicle_number' => $purchaseReturn->vehicle_number,
            'driver_name' => $purchaseReturn->driver_name,
            'place_of_supply' => $purchaseReturn->place_of_supply,
            'eway_bill_no' => $purchaseReturn->eway_bill_no,
            'distance_km' => $purchaseReturn->distance_km,
            'taxable_amount' => $purchaseReturn->subtotal,
            'gst_amount' => $purchaseReturn->gst_amount,
            'cgst_amount' => (float) $purchaseReturn->gst_amount / 2,
            'sgst_amount' => (float) $purchaseReturn->gst_amount / 2,
            'igst_amount' => 0,
            'net_amount' => $purchaseReturn->total,
            'notes' => $purchaseReturn->notes,
        ]);

        $items = $purchaseReturn->items->map(function ($item) {
            return new InvoiceItem([
                'product_name' => $item->product_name ?? $item->product?->name,
                'hsn_code' => $item->hsn_code ?? $item->product?->hsn_code,
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? $item->product?->unit?->symbol,
                'rate' => $item->rate,
                'gst_percent' => $item->gst_percent,
                'amount' => $item->amount,
                'sort_order' => $item->sort_order,
            ]);
        });

        $document->setRelation('customer', null);
        $document->setRelation('items', $items);

        return $document;
    }

    /**
     * Convert sales return into an invoice-like document for template binding.
     */
    protected function buildDocumentFromSalesReturn(SalesReturn $salesReturn): Invoice
    {
        $document = new Invoice([
            'invoice_number' => $salesReturn->doc_number,
            'doc_number' => $salesReturn->doc_number,
            'document_type' => $salesReturn->document_type,
            'invoice_date' => $salesReturn->return_date,
            'payment_mode' => $salesReturn->payment_mode,
            'party_name' => $salesReturn->party_name ?: $salesReturn->customer?->name,
            'city' => $salesReturn->city ?: $salesReturn->customer?->city,
            'state' => $salesReturn->state ?: $salesReturn->customer?->state,
            'gstin' => $salesReturn->gstin ?: $salesReturn->customer?->gstin,
            'gr_number' => $salesReturn->gr_number,
            'gr_date' => $salesReturn->gr_date,
            'transport_name' => $salesReturn->transport_name,
            'vehicle_number' => $salesReturn->vehicle_number,
            'driver_name' => $salesReturn->driver_name,
            'place_of_supply' => $salesReturn->place_of_supply,
            'eway_bill_no' => $salesReturn->eway_bill_no,
            'distance_km' => $salesReturn->distance_km,
            'taxable_amount' => $salesReturn->subtotal,
            'gst_amount' => $salesReturn->gst_amount,
            'cgst_amount' => (float) $salesReturn->gst_amount / 2,
            'sgst_amount' => (float) $salesReturn->gst_amount / 2,
            'igst_amount' => 0,
            'net_amount' => $salesReturn->total,
            'notes' => $salesReturn->notes,
        ]);

        $items = $salesReturn->items->map(function ($item) {
            return new InvoiceItem([
                'product_name' => $item->product_name ?? $item->product?->name,
                'hsn_code' => $item->hsn_code ?? $item->product?->hsn_code,
                'quantity' => $item->quantity,
                'unit' => $item->unit ?? $item->product?->unit?->symbol,
                'rate' => $item->rate,
                'gst_percent' => $item->gst_percent,
                'amount' => $item->amount,
                'sort_order' => $item->sort_order,
            ]);
        });

        $document->setRelation('customer', $salesReturn->customer);
        $document->setRelation('items', $items);

        return $document;
    }
}
