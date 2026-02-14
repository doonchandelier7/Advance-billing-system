<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('modules.master-setup', [
            'categories' => $categories,
            'units' => $units,
            'vendors' => $vendors,
            'customers' => $customers,
            'products' => $products,
            'allCategories' => $allCategories,
            'allUnits' => $allUnits,
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
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
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
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
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
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
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
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'pan' => 'nullable|string|max:32',
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
            'hsn_code' => 'nullable|string|max:32',
            'description' => 'nullable|string|max:1000',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
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
            'hsn_code' => 'nullable|string|max:32',
            'description' => 'nullable|string|max:1000',
            'purchase_rate' => 'nullable|numeric|min:0',
            'sale_rate' => 'nullable|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'low_stock_threshold' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $product->update($validated);
        return redirect()->route('modules.master-setup')->with('success', 'Product updated.');
    }

    public function destroyProduct(Product $product): RedirectResponse
    {
        if ($product->stockMovements()->exists()) {
            return redirect()->route('modules.master-setup')->with('error', 'Product has stock movements. Cannot delete.');
        }
        $product->delete();
        return redirect()->route('modules.master-setup')->with('success', 'Product deleted.');
    }
}
