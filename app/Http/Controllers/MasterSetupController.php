<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MasterSetupController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()->withCount('products')->orderBy('name')->paginate(10, ['*'], 'cat_page');
        $units = Unit::query()->withCount('products')->orderBy('name')->paginate(10, ['*'], 'unit_page');
        $vendors = Vendor::query()->orderBy('name')->paginate(10, ['*'], 'vendor_page');
        $customers = Customer::query()->orderBy('name')->paginate(10, ['*'], 'customer_page');
        $products = Product::query()->with(['category:id,name', 'unit:id,name,symbol'])->orderBy('name')->paginate(10, ['*'], 'product_page');
        $allCategories = Category::orderBy('name')->get(['id', 'name']);
        $allUnits = Unit::orderBy('name')->get(['id', 'name', 'symbol']);
        $gstRates = $this->getConfiguredGstRates();
        $defaultGstRate = Setting::get('product_default_gst_rate');

        return view('modules.master-setup', [
            'categories' => $categories,
            'units' => $units,
            'vendors' => $vendors,
            'customers' => $customers,
            'products' => $products,
            'allCategories' => $allCategories,
            'allUnits' => $allUnits,
            'gstRates' => $gstRates,
            'defaultGstRate' => $defaultGstRate,
        ]);
    }

    // Categories
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191|unique:categories,slug',
            'description' => 'nullable|string|max:512',
            'is_active' => 'nullable|boolean',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        Category::create($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'slug' => 'nullable|string|max:191|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string|max:512',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $category->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('modules.master-setup')->with('error', 'Category has products. Reassign or remove them first.');
        }
        $category->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Category deleted.');
    }

    // Units
    public function storeUnit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'symbol' => 'required|string|max:32|unique:units,symbol',
            'description' => 'nullable|string|max:512',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Unit::create($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Unit created.');
    }

    public function updateUnit(Request $request, Unit $unit): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'symbol' => 'sometimes|string|max:32|unique:units,symbol,' . $unit->id,
            'description' => 'nullable|string|max:512',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $unit->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Unit updated.');
    }

    public function destroyUnit(Unit $unit): RedirectResponse
    {
        if ($unit->products()->exists()) {
            return redirect()->route('modules.master-setup')->with('error', 'Unit has products. Reassign or remove them first.');
        }
        $unit->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Unit deleted.');
    }

    // Vendors
    public function storeVendor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:191',
            'district' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_no' => 'nullable|string|max:64',
            'bank_branch' => 'nullable|string|max:191',
            'bank_ifsc' => 'nullable|string|max:32',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['opening_balance'] = (float) ($validated['opening_balance'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);
        Vendor::create($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Vendor created.');
    }

    public function updateVendor(Request $request, Vendor $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:191',
            'district' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_no' => 'nullable|string|max:64',
            'bank_branch' => 'nullable|string|max:191',
            'bank_ifsc' => 'nullable|string|max:32',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['opening_balance'] = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : $vendor->opening_balance;
        $validated['is_active'] = $request->boolean('is_active', true);
        $vendor->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Vendor updated.');
    }

    public function destroyVendor(Vendor $vendor): RedirectResponse
    {
        $vendor->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Vendor deleted.');
    }

    // Customers
    public function storeCustomer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:191',
            'district' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_no' => 'nullable|string|max:64',
            'bank_branch' => 'nullable|string|max:191',
            'bank_ifsc' => 'nullable|string|max:32',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['opening_balance'] = (float) ($validated['opening_balance'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active', true);
        Customer::create($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Customer created.');
    }

    public function updateCustomer(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'contact_person' => 'nullable|string|max:191',
            'phone' => 'nullable|string|max:32',
            'email' => 'nullable|email|max:191',
            'address' => 'nullable|string|max:1000',
            'city' => 'nullable|string|max:191',
            'district' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
            'bank_name' => 'nullable|string|max:191',
            'bank_account_no' => 'nullable|string|max:64',
            'bank_branch' => 'nullable|string|max:191',
            'bank_ifsc' => 'nullable|string|max:32',
            'opening_balance' => 'nullable|numeric',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);
        $validated['opening_balance'] = isset($validated['opening_balance']) ? (float) $validated['opening_balance'] : $customer->opening_balance;
        $validated['is_active'] = $request->boolean('is_active', true);
        $customer->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Customer updated.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        if ($customer->invoices()->exists()) {
            return redirect()->route('modules.master-setup')->with('error', 'Customer has invoices. Cannot delete.');
        }
        $customer->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Customer deleted.');
    }

    // Products
    public function storeProduct(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:64|unique:products,code',
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'hsn_code' => ['nullable', 'string', 'max:32', 'regex:/^\d{4}(\d{2}(\d{2})?)?$/'],
            'description' => 'nullable|string|max:1000',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['hsn_code'] = $this->normalizeHsnCode($validated['hsn_code'] ?? null);
        $validated['purchase_rate'] = (float) ($validated['purchase_rate'] ?? 0);
        $validated['sale_rate'] = (float) ($validated['sale_rate'] ?? 0);
        $validated['stock'] = 0;
        $validated['is_active'] = $request->boolean('is_active', true);
        Product::create($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Product created.');
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'code' => 'nullable|string|max:64|unique:products,code,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'unit_id' => 'nullable|exists:units,id',
            'hsn_code' => ['nullable', 'string', 'max:32', 'regex:/^\d{4}(\d{2}(\d{2})?)?$/'],
            'description' => 'nullable|string|max:1000',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        if (array_key_exists('hsn_code', $validated)) {
            $validated['hsn_code'] = $this->normalizeHsnCode($validated['hsn_code']);
        }
        $validated['is_active'] = $request->boolean('is_active', true);
        $product->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Product updated.');
    }

    public function adjustProductStock(Request $request, Product $product, StockService $stock): RedirectResponse
    {
        $validated = $request->validate([
            'operation' => 'required|string|in:add,remove,set',
            'quantity' => 'required|numeric|min:0.001',
            'notes' => 'nullable|string|max:512',
        ]);

        $quantity = (float) $validated['quantity'];
        $notes = $validated['notes'] ?? null;

        if ($validated['operation'] === 'add') {
            $stock->stockIn($product, $quantity, 'manual_stock_add', null, $notes ?: "Manual stock add (+{$quantity})", Auth::id());
        } elseif ($validated['operation'] === 'remove') {
            $stock->stockOut($product, $quantity, 'manual_stock_remove', null, $notes ?: "Manual stock remove (-{$quantity})", Auth::id());
        } else {
            $stock->adjust($product, $quantity, $notes ?: "Manual stock set ({$quantity})", Auth::id());
        }

        return redirect()->route('modules.master-setup', ['#products'])->with('success', 'Product stock updated successfully.');
    }

    public function productStockLogs(Product $product): JsonResponse
    {
        $logs = StockMovement::query()
            ->where('product_id', $product->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(function (StockMovement $movement) {
                return [
                    'id' => $movement->id,
                    'type' => $movement->type,
                    'quantity' => (float) $movement->quantity,
                    'stock_before' => (float) $movement->stock_before,
                    'stock_after' => (float) $movement->stock_after,
                    'reference_type' => $movement->reference_type,
                    'notes' => $movement->notes,
                    'user' => $movement->user?->name,
                    'created_at' => optional($movement->created_at)->format('d M Y, h:i A'),
                ];
            });

        return response()->json([
            'product' => ['id' => $product->id, 'name' => $product->name],
            'logs' => $logs,
        ]);
    }

    public function updateProductGstSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gst_rates' => 'required|string|max:191',
            'default_gst_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $rates = $this->parseGstRates($validated['gst_rates']);
        if (empty($rates)) {
            return redirect()->route('modules.master-setup', ['#products'])->with('error', 'Please provide valid GST rates (e.g. 0,5,12,18,28).');
        }

        $defaultRate = isset($validated['default_gst_rate']) ? (string) ((float) $validated['default_gst_rate']) : '';
        Setting::set('product_gst_rates', implode(',', $rates), 'product');
        Setting::set('product_default_gst_rate', $defaultRate, 'product');

        return redirect()->route('modules.master-setup', ['#products'])->with('success', 'GST configuration updated successfully.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists()) {
            return redirect()->route('modules.master-setup')->with('error', 'Product has stock movements. Cannot delete.');
        }
        $product->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Product deleted.');
    }

    protected function normalizeHsnCode(?string $hsnCode): ?string
    {
        if ($hsnCode === null) {
            return null;
        }

        $clean = preg_replace('/\D+/', '', $hsnCode);
        return $clean !== '' ? $clean : null;
    }

    protected function getConfiguredGstRates(): array
    {
        $stored = Setting::get('product_gst_rates', '0,5,12,18,28');
        return $this->parseGstRates((string) $stored);
    }

    protected function parseGstRates(string $input): array
    {
        $parts = preg_split('/[\s,]+/', trim($input));
        $rates = [];

        foreach ($parts as $part) {
            if ($part === '' || !is_numeric($part)) {
                continue;
            }

            $value = (float) $part;
            if ($value < 0 || $value > 100) {
                continue;
            }

            $rates[] = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
        }

        $rates = array_values(array_unique($rates));
        sort($rates, SORT_NUMERIC);

        return $rates;
    }
}
