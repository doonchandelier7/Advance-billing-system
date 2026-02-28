<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTemplate;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\InvoiceNumberService;
use App\Services\InvoiceTemplateBindingService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InvoiceSetupController extends Controller
{
    public function __construct(
        protected InvoiceNumberService $invoiceNumber,
        protected InvoiceTemplateBindingService $binding,
        protected StockService $stock,
    ) {}

    /**
     * Invoice Templates management page.
     */
    public function templates(): View
    {
        $templates = InvoiceTemplate::orderBy('type')->orderBy('name')->get();
        $types = InvoiceTemplate::types();
        return view('invoices.templates', compact('templates', 'types'));
    }

    /**
     * Store a new invoice template.
     */
    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|string|in:' . implode(',', array_keys(InvoiceTemplate::types())),
            'header_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'colors' => 'nullable|array',
            'colors.primary' => 'nullable|string|max:20',
            'colors.secondary' => 'nullable|string|max:20',
            'colors.accent' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            InvoiceTemplate::where('type', $validated['type'])->update(['is_default' => false]);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_default'] = $validated['is_default'] ?? false;

        InvoiceTemplate::create($validated);

        return redirect()->route('invoices.templates')->with('success', 'Template created successfully!');
    }

    /**
     * Update an invoice template.
     */
    public function updateTemplate(Request $request, InvoiceTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'type' => 'required|string|in:' . implode(',', array_keys(InvoiceTemplate::types())),
            'header_html' => 'nullable|string',
            'body_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'colors' => 'nullable|array',
            'colors.primary' => 'nullable|string|max:20',
            'colors.secondary' => 'nullable|string|max:20',
            'colors.accent' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if (!empty($validated['is_default'])) {
            InvoiceTemplate::where('type', $validated['type'])->where('id', '!=', $template->id)->update(['is_default' => false]);
        }

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['is_default'] = $validated['is_default'] ?? false;

        $template->update($validated);
        $template->increment('version');

        return redirect()->route('invoices.templates')->with('success', 'Template updated successfully!');
    }

    /**
     * Delete an invoice template.
     */
    public function destroyTemplate(InvoiceTemplate $template): RedirectResponse
    {
        $template->delete();
        return redirect()->route('invoices.templates')->with('success', 'Template deleted successfully!');
    }

    /**
     * Invoice creation page with template selection.
     */
    public function create(): View
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get();
        $products = Product::with('unit')->where('is_active', true)->orderBy('name')->get();
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $types = InvoiceTemplate::types();
        return view('invoices.create', compact('customers', 'vendors', 'products', 'templates', 'types'));
    }

    /**
     * Search customers and vendors by name for invoice auto-fill.
     */
    public function searchParty(Request $request): JsonResponse
    {
        $q = $request->query('q', '');
        if (strlen($q) < 1) {
            return response()->json([]);
        }

        $customers = Customer::where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id'              => $c->id,
                'type'            => 'customer',
                'name'            => $c->name,
                'contact_person'  => $c->contact_person,
                'phone'           => $c->phone,
                'email'           => $c->email,
                'address'         => $c->address,
                'city'            => $c->city,
                'district'        => $c->district,
                'state'           => $c->state,
                'gstin'           => $c->gstin,
                'pan'             => $c->pan,
                'bank_name'       => $c->bank_name,
                'bank_account_no' => $c->bank_account_no,
                'bank_branch'     => $c->bank_branch,
                'bank_ifsc'       => $c->bank_ifsc,
            ]);

        $vendors = Vendor::where('is_active', true)
            ->where('name', 'like', "%{$q}%")
            ->limit(10)
            ->get()
            ->map(fn ($v) => [
                'id'              => $v->id,
                'type'            => 'vendor',
                'name'            => $v->name,
                'contact_person'  => $v->contact_person,
                'phone'           => $v->phone,
                'email'           => $v->email,
                'address'         => $v->address,
                'city'            => $v->city,
                'district'        => $v->district,
                'state'           => $v->state,
                'gstin'           => $v->gstin,
                'pan'             => $v->pan,
                'bank_name'       => $v->bank_name,
                'bank_account_no' => $v->bank_account_no,
                'bank_branch'     => $v->bank_branch,
                'bank_ifsc'       => $v->bank_ifsc,
            ]);

        return response()->json($customers->concat($vendors)->values());
    }

    /**
     * Store invoice and redirect to generate view.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:invoice_templates,id',
            'customer_id' => 'nullable|exists:customers,id',
            'document_type' => 'nullable|string|max:64',
            'invoice_date' => 'nullable|date',
            'payment_mode' => 'nullable|string|max:32',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'district' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'buyer_bank_name' => 'nullable|string|max:191',
            'buyer_bank_account_no' => 'nullable|string|max:64',
            'buyer_bank_branch' => 'nullable|string|max:191',
            'buyer_bank_ifsc' => 'nullable|string|max:32',
            'gr_number' => 'nullable|string|max:64',
            'gr_date' => 'nullable|date',
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
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $invoice = DB::transaction(function () use ($validated) {
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
                    'district' => $validated['district'] ?? $customer?->district,
                    'state' => $validated['state'] ?? $customer?->state,
                    'gstin' => $validated['gstin'] ?? $customer?->gstin,
                    'buyer_bank_name' => $validated['buyer_bank_name'] ?? $customer?->bank_name,
                    'buyer_bank_account_no' => $validated['buyer_bank_account_no'] ?? $customer?->bank_account_no,
                    'buyer_bank_branch' => $validated['buyer_bank_branch'] ?? $customer?->bank_branch,
                    'buyer_bank_ifsc' => $validated['buyer_bank_ifsc'] ?? $customer?->bank_ifsc,
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

                return $invoice;
            });

            return redirect()->route('invoices.generate', [
                'invoice' => $invoice->id,
                'template' => $validated['template_id'],
            ])->with('success', 'Invoice created successfully!');
        } catch (\Exception $e) {
            return redirect()->route('invoices.create')->with('error', 'Failed to create invoice: ' . $e->getMessage());
        }
    }

    /**
     * Generate / preview invoice with selected template.
     */
    public function generate(Request $request, Invoice $invoice): View
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoice->load(['customer', 'items.product']);
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();

        $templateId = $request->query('template', $templates->where('is_default', true)->first()?->id ?? $templates->first()?->id);
        $template = InvoiceTemplate::find($templateId);

        $renderedHtml = $template ? $this->binding->bind($invoice, $template) : null;

        return view('invoices.generate', compact('invoice', 'templates', 'template', 'renderedHtml'));
    }

    /**
     * Print-ready view of generated invoice.
     */
    public function print(Request $request, Invoice $invoice): View
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }

        $invoice->load(['customer', 'items.product']);
        $templateId = $request->query('template');
        $template = InvoiceTemplate::find($templateId);

        $renderedHtml = $template ? $this->binding->bind($invoice, $template) : null;

        return view('invoices.print', compact('invoice', 'template', 'renderedHtml'));
    }

    /**
     * List all invoices with template selection for re-generation.
     */
    public function index(): View
    {
        $invoices = Invoice::with(['customer', 'items'])
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);
        $templates = InvoiceTemplate::where('is_active', true)->orderBy('type')->orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'templates'));
    }
}
