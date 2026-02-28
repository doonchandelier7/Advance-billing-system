@extends('layouts.app')

@section('title', 'Master Setup')
@section('header', 'Master Setup')

@section('content')
{{-- Toast --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif

{{-- Tabs --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; border: 0 !important;">
    <div class="card-body d-flex flex-wrap" style="padding:10px; gap:8px;">
        @foreach(['categories'=>['fas fa-tags','Categories'],'units'=>['fas fa-ruler','Units'],'vendors'=>['fas fa-building','Vendors'],'customers'=>['fas fa-users','Customers'],'products'=>['fas fa-cube','Products']] as $tabKey => $tabInfo)
        <button type="button" class="btn {{ $loop->first ? 'btn-light' : '' }} master-tab-btn" id="tab-btn-{{ $tabKey }}" data-tab="{{ $tabKey }}"
                style="{{ $loop->first ? 'background:#fff !important; color:#1e3c72 !important; font-weight:600;' : 'background:rgba(255,255,255,0.12) !important; color:rgba(255,255,255,0.85) !important; border:0;' }} padding:10px 18px; border-radius:8px; font-size:0.9rem;">
            <i class="{{ $tabInfo[0] }} mr-1"></i> {{ $tabInfo[1] }}
        </button>
        @endforeach
    </div>
</div>

{{-- ===== CATEGORIES ===== --}}
<div id="tab-panel-categories" class="tab-panel">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-tags mr-2" style="color:#667eea;"></i>Categories</h5>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#categoryModal" onclick="resetCategoryForm()"><i class="fas fa-plus mr-1"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Description</th><th>Products</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($categories as $c)
                        <tr>
                            <td style="font-weight:600;">{{ $c->name }}</td>
                            <td class="text-muted">{{ Str::limit($c->description, 50) ?: '—' }}</td>
                            <td><span class="badge badge-info">{{ $c->products_count }}</span></td>
                            <td><span class="badge badge-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-default" onclick="editCategory({{ json_encode($c) }})"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('categories', {{ $c->id }}, '{{ addslashes($c->name) }}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-tags fa-2x mb-3 d-block" style="opacity:0.3;"></i>No categories yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages()) <div class="p-3">{{ $categories->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ===== UNITS ===== --}}
<div id="tab-panel-units" class="tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-ruler mr-2" style="color:#00b894;"></i>Units</h5>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#unitModal" onclick="resetUnitForm()"><i class="fas fa-plus mr-1"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Symbol</th><th>Products</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($units as $u)
                        <tr>
                            <td style="font-weight:600;">{{ $u->name }}</td>
                            <td><code>{{ $u->symbol }}</code></td>
                            <td><span class="badge badge-info">{{ $u->products_count }}</span></td>
                            <td><span class="badge badge-{{ $u->is_active ? 'success' : 'secondary' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-default" onclick="editUnit({{ json_encode($u) }})"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('units', {{ $u->id }}, '{{ addslashes($u->name) }}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5"><i class="fas fa-ruler fa-2x mb-3 d-block" style="opacity:0.3;"></i>No units yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($units->hasPages()) <div class="p-3">{{ $units->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ===== VENDORS ===== --}}
<div id="tab-panel-vendors" class="tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-building mr-2" style="color:#f39c12;"></i>Vendors</h5>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#vendorModal" onclick="resetVendorForm()"><i class="fas fa-plus mr-1"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Contact</th><th>Phone</th><th>City</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($vendors as $v)
                        <tr>
                            <td><strong>{{ $v->name }}</strong><br><small class="text-muted">{{ $v->email ?: '' }}</small></td>
                            <td>{{ $v->contact_person ?: '—' }}</td>
                            <td>{{ $v->phone ?: '—' }}</td>
                            <td>{{ $v->city ?: '—' }}</td>
                            <td><span class="badge badge-{{ $v->is_active ? 'success' : 'secondary' }}">{{ $v->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-default" onclick="editVendor({{ json_encode($v) }})"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('vendors', {{ $v->id }}, '{{ addslashes($v->name) }}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-building fa-2x mb-3 d-block" style="opacity:0.3;"></i>No vendors yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($vendors->hasPages()) <div class="p-3">{{ $vendors->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ===== CUSTOMERS ===== --}}
<div id="tab-panel-customers" class="tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-users mr-2" style="color:#e84393;"></i>Customers</h5>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#customerModal" onclick="resetCustomerForm()"><i class="fas fa-plus mr-1"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Contact</th><th>Phone</th><th>City</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td><strong>{{ $c->name }}</strong><br><small class="text-muted">{{ $c->email ?: '' }}</small></td>
                            <td>{{ $c->contact_person ?: '—' }}</td>
                            <td>{{ $c->phone ?: '—' }}</td>
                            <td>{{ $c->city ?: '—' }}</td>
                            <td><span class="badge badge-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-default" onclick="editCustomer({{ json_encode($c) }})"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('customers', {{ $c->id }}, '{{ addslashes($c->name) }}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5"><i class="fas fa-users fa-2x mb-3 d-block" style="opacity:0.3;"></i>No customers yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages()) <div class="p-3">{{ $customers->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ===== PRODUCTS ===== --}}
<div id="tab-panel-products" class="tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-cube mr-2" style="color:#6c5ce7;"></i>Products</h5>
            <div class="d-flex" style="gap:8px;">
                <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#gstSettingsModal">
                    <i class="fas fa-sliders-h mr-1"></i> GST Settings
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#productModal" onclick="resetProductForm()"><i class="fas fa-plus mr-1"></i> Add</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Name</th><th>Code</th><th>Category</th><th class="text-right">Sale Rate</th><th class="text-right">Stock</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($products as $p)
                        <tr>
                            <td><strong>{{ $p->name }}</strong><br><small class="text-muted">HSN: {{ $p->hsn_code ?: 'N/A' }}</small></td>
                            <td><code>{{ $p->code ?: '—' }}</code></td>
                            <td>{{ $p->category->name ?? '—' }}</td>
                            <td class="text-right" style="font-weight:600; color:#55efc4;">{{ number_format($p->sale_rate, 2) }}</td>
                            <td class="text-right">
                                @if($p->stock <= 0)<span class="badge badge-danger">{{ $p->stock }}</span>
                                @elseif($p->low_stock_threshold && $p->stock <= $p->low_stock_threshold)<span class="badge badge-warning">{{ $p->stock }}</span>
                                @else <span class="badge badge-success">{{ $p->stock }}</span>
                                @endif
                            </td>
                            <td><span class="badge badge-{{ $p->is_active ? 'success' : 'secondary' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <button type="button" class="btn btn-sm btn-info" onclick="openStockModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ $p->stock }}')"><i class="fas fa-boxes"></i></button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="openStockLogs({{ $p->id }}, '{{ addslashes($p->name) }}')"><i class="fas fa-history"></i></button>
                                <button type="button" class="btn btn-sm btn-default" onclick="editProduct({{ json_encode($p) }})"><i class="fas fa-pen"></i></button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('products', {{ $p->id }}, '{{ addslashes($p->name) }}')"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5"><i class="fas fa-cube fa-2x mb-3 d-block" style="opacity:0.3;"></i>No products yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages()) <div class="p-3">{{ $products->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ===== MODALS ===== --}}

{{-- Category --}}
<div class="modal fade" id="categoryModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-tags mr-2"></i><span id="categoryModalTitle">Add Category</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="categoryForm" method="POST" action="{{ route('modules.master-setup.categories.store') }}">@csrf
        <input type="hidden" name="_method" id="categoryMethod" value="POST">
        <div class="modal-body">
            <div class="form-group"><label>Name <span class="text-danger">*</span></label><input type="text" name="name" id="cat-name" class="form-control" required placeholder="Category name"></div>
            <div class="form-group"><label>Description</label><textarea name="description" id="cat-desc" class="form-control" rows="2" placeholder="Optional"></textarea></div>
            <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="cat-active" checked><label class="custom-control-label" for="cat-active">Active</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Unit --}}
<div class="modal fade" id="unitModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#00b894,#00cec9) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-ruler mr-2"></i><span id="unitModalTitle">Add Unit</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="unitForm" method="POST" action="{{ route('modules.master-setup.units.store') }}">@csrf
        <input type="hidden" name="_method" id="unitMethod" value="POST">
        <div class="modal-body">
            <div class="row"><div class="col-6"><div class="form-group"><label>Name *</label><input type="text" name="name" id="unit-name" class="form-control" required></div></div>
            <div class="col-6"><div class="form-group"><label>Symbol *</label><input type="text" name="symbol" id="unit-symbol" class="form-control" required></div></div></div>
            <div class="form-group"><label>Description</label><input type="text" name="description" id="unit-desc" class="form-control" placeholder="Optional"></div>
            <div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="unit-active" checked><label class="custom-control-label" for="unit-active">Active</label></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Vendor --}}
<div class="modal fade" id="vendorModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#f39c12,#e67e22) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-building mr-2"></i><span id="vendorModalTitle">Add Vendor</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="vendorForm" method="POST" action="{{ route('modules.master-setup.vendors.store') }}">@csrf
        <input type="hidden" name="_method" id="vendorMethod" value="POST">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" name="name" id="v-name" class="form-control" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" id="v-contact" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Phone</label><input type="text" name="phone" id="v-phone" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" id="v-email" class="form-control"></div></div>
                <div class="col-12"><div class="form-group"><label>Address</label><input type="text" name="address" id="v-address" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>State</label><input type="text" name="state" id="v-state" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>District</label><input type="text" name="district" id="v-district" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>City</label><input type="text" name="city" id="v-city" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Opening Balance</label><input type="number" name="opening_balance" id="v-bal" class="form-control" step="0.01" value="0"></div></div>
                <div class="col-md-4"><div class="form-group"><label>GSTIN</label><input type="text" name="gstin" id="v-gstin" class="form-control"></div></div>
                <div class="col-md-4"><div class="form-group"><label>PAN</label><input type="text" name="pan" id="v-pan" class="form-control"></div></div>
                <div class="col-md-4 d-flex align-items-end"><div class="form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="v-active" checked><label class="custom-control-label" for="v-active">Active</label></div></div></div>
                <div class="col-12 mt-2"><hr style="border-color:rgba(243,156,18,0.2);"><small class="text-muted"><i class="fas fa-university mr-1"></i>Bank Details</small></div>
                <div class="col-md-6"><div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" id="v-bank-name" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Account No.</label><input type="text" name="bank_account_no" id="v-bank-account" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Branch</label><input type="text" name="bank_branch" id="v-bank-branch" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>IFSC Code</label><input type="text" name="bank_ifsc" id="v-bank-ifsc" class="form-control"></div></div>
                <div class="col-12"><div class="form-group mb-0"><label>Notes</label><textarea name="notes" id="v-notes" class="form-control" rows="2"></textarea></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Customer --}}
<div class="modal fade" id="customerModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#e84393,#fd79a8) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-users mr-2"></i><span id="customerModalTitle">Add Customer</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="customerForm" method="POST" action="{{ route('modules.master-setup.customers.store') }}">@csrf
        <input type="hidden" name="_method" id="customerMethod" value="POST">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" name="name" id="c-name" class="form-control" required></div></div>
                <div class="col-md-6"><div class="form-group"><label>Contact Person</label><input type="text" name="contact_person" id="c-contact" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Phone</label><input type="text" name="phone" id="c-phone" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Email</label><input type="email" name="email" id="c-email" class="form-control"></div></div>
                <div class="col-12"><div class="form-group"><label>Address</label><input type="text" name="address" id="c-address" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>State</label><input type="text" name="state" id="c-state" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>District</label><input type="text" name="district" id="c-district" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>City</label><input type="text" name="city" id="c-city" class="form-control"></div></div>
                <div class="col-md-3"><div class="form-group"><label>Opening Balance</label><input type="number" name="opening_balance" id="c-bal" class="form-control" step="0.01" value="0"></div></div>
                <div class="col-md-4"><div class="form-group"><label>GSTIN</label><input type="text" name="gstin" id="c-gstin" class="form-control"></div></div>
                <div class="col-md-4"><div class="form-group"><label>PAN</label><input type="text" name="pan" id="c-pan" class="form-control"></div></div>
                <div class="col-md-4 d-flex align-items-end"><div class="form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="c-active" checked><label class="custom-control-label" for="c-active">Active</label></div></div></div>
                <div class="col-12 mt-2"><hr style="border-color:rgba(232,67,147,0.2);"><small class="text-muted"><i class="fas fa-university mr-1"></i>Bank Details</small></div>
                <div class="col-md-6"><div class="form-group"><label>Bank Name</label><input type="text" name="bank_name" id="c-bank-name" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Account No.</label><input type="text" name="bank_account_no" id="c-bank-account" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>Branch</label><input type="text" name="bank_branch" id="c-bank-branch" class="form-control"></div></div>
                <div class="col-md-6"><div class="form-group"><label>IFSC Code</label><input type="text" name="bank_ifsc" id="c-bank-ifsc" class="form-control"></div></div>
                <div class="col-12"><div class="form-group mb-0"><label>Notes</label><textarea name="notes" id="c-notes" class="form-control" rows="2"></textarea></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Product --}}
<div class="modal fade" id="productModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#6c5ce7,#a29bfe) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-cube mr-2"></i><span id="productModalTitle">Add Product</span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="productForm" method="POST" action="{{ route('modules.master-setup.products.store') }}">@csrf
        <input type="hidden" name="_method" id="productMethod" value="POST">
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6"><div class="form-group"><label>Name *</label><input type="text" name="name" id="p-name" class="form-control" required></div></div>
                <div class="col-md-3"><div class="form-group"><label>Code</label><input type="text" name="code" id="p-code" class="form-control"></div></div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>HSN</label>
                        <input type="text" name="hsn_code" id="p-hsn" class="form-control" pattern="\d{4}(\d{2}(\d{2})?)?" maxlength="8" placeholder="4/6/8 digit code">
                        <small class="text-muted">Allowed: 4, 6, or 8 digits.</small>
                    </div>
                </div>
                <div class="col-md-6"><div class="form-group"><label>Category</label><select name="category_id" id="p-cat" class="form-control"><option value="">--</option>@foreach($allCategories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div></div>
                <div class="col-md-6"><div class="form-group"><label>Unit</label><select name="unit_id" id="p-unit" class="form-control"><option value="">--</option>@foreach($allUnits as $u)<option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>@endforeach</select></div></div>
                <div class="col-md-4"><div class="form-group"><label>Purchase Rate</label><input type="number" name="purchase_rate" id="p-pr" class="form-control" step="0.0001" value="0"></div></div>
                <div class="col-md-4"><div class="form-group"><label>Sale Rate</label><input type="number" name="sale_rate" id="p-sr" class="form-control" step="0.0001" value="0"></div></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>GST %</label>
                        <select name="gst_percent" id="p-gst" class="form-control" onchange="toggleCustomGstInput()">
                            <option value="">-- Select GST --</option>
                            @foreach($gstRates as $rate)
                            <option value="{{ $rate }}">{{ $rate }}%</option>
                            @endforeach
                            <option value="__custom__">Custom</option>
                        </select>
                        <input type="number" id="p-gst-custom" class="form-control mt-2 d-none" step="0.01" min="0" max="100" placeholder="Enter custom GST %" oninput="syncCustomGstValue()">
                    </div>
                </div>
                <div class="col-md-6"><div class="form-group"><label>Low Stock Threshold</label><input type="number" name="low_stock_threshold" id="p-low" class="form-control" step="0.001"></div></div>
                <div class="col-md-6 d-flex align-items-end"><div class="form-group"><div class="custom-control custom-switch"><input type="checkbox" class="custom-control-input" name="is_active" value="1" id="p-active" checked><label class="custom-control-label" for="p-active">Active</label></div></div></div>
                <div class="col-12"><div class="form-group mb-0"><label>Description</label><input type="text" name="description" id="p-desc" class="form-control"></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save</button></div>
    </form>
</div></div></div>

{{-- Stock Management --}}
<div class="modal fade" id="stockModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#00b894,#00cec9) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-boxes mr-2"></i>Manage Stock</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form id="stockForm" method="POST">@csrf
        <div class="modal-body">
            <div class="alert alert-info mb-3 py-2">
                <strong id="stock-product-name">Product</strong><br>
                Current Stock: <strong id="stock-current-value">0</strong>
            </div>
            <div class="form-group">
                <label>Operation</label>
                <select name="operation" id="stock-operation" class="form-control">
                    <option value="add">Add Stock</option>
                    <option value="remove">Remove Stock</option>
                    <option value="set">Set Exact Stock</option>
                </select>
            </div>
            <div class="form-group">
                <label>Quantity</label>
                <input type="number" name="quantity" id="stock-quantity" class="form-control" step="0.001" min="0.001" required>
            </div>
            <div class="form-group mb-0">
                <label>Notes</label>
                <input type="text" name="notes" id="stock-notes" class="form-control" placeholder="Optional note for audit trail">
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Update Stock</button></div>
    </form>
</div></div></div>

{{-- Stock Logs --}}
<div class="modal fade" id="stockLogsModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#636e72,#2d3436) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-history mr-2"></i>Stock Logs - <span id="stock-log-product-name"></span></h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead><tr><th>Date</th><th>Type</th><th class="text-right">Qty</th><th class="text-right">Before</th><th class="text-right">After</th><th>Ref</th><th>User</th><th>Notes</th></tr></thead>
                <tbody id="stock-log-body">
                    <tr><td colspan="8" class="text-center text-muted py-3">No logs found.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div></div></div>

{{-- GST Settings --}}
<div class="modal fade" id="gstSettingsModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#6c5ce7,#a29bfe) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;"><i class="fas fa-sliders-h mr-2"></i>Product GST Settings</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <form method="POST" action="{{ route('modules.master-setup.products.settings.gst') }}">@csrf
        <div class="modal-body">
            <div class="form-group">
                <label>Allowed GST Rates (%)</label>
                <input type="text" name="gst_rates" class="form-control" value="{{ implode(',', $gstRates) }}" placeholder="0,5,12,18,28" required>
                <small class="text-muted">Comma-separated values. Example: 0,5,12,18,28</small>
            </div>
            <div class="form-group mb-0">
                <label>Default GST Rate (%)</label>
                <input type="number" name="default_gst_rate" class="form-control" value="{{ $defaultGstRate }}" step="0.01" min="0" max="100" placeholder="18">
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Settings</button></div>
    </form>
</div></div></div>

{{-- Delete --}}
<div class="modal fade" id="deleteModal" tabindex="-1"><div class="modal-dialog modal-sm modal-dialog-centered"><div class="modal-content">
    <div class="modal-header" style="background:linear-gradient(135deg,#ff7675,#d63031) !important; border:0;">
        <h5 class="modal-title" style="color:#fff;">Confirm Delete</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
    </div>
    <div class="modal-body text-center">
        <i class="fas fa-trash-alt fa-3x mb-3" style="color:#ff7675;"></i>
        <p>Delete <strong id="deleteItemName"></strong>?<br><small class="text-muted">This cannot be undone.</small></p>
    </div>
    <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <form id="deleteForm" method="POST">@csrf @method('DELETE')<button type="submit" class="btn btn-danger"><i class="fas fa-trash mr-1"></i> Delete</button></form>
    </div>
</div></div></div>

<script>
// Tabs
(function(){
    var tabs = ['categories','units','vendors','customers','products'];
    tabs.forEach(function(id){
        var btn = document.getElementById('tab-btn-'+id);
        if(!btn) return;
        btn.addEventListener('click', function(){
            tabs.forEach(function(t){
                var b=document.getElementById('tab-btn-'+t), p=document.getElementById('tab-panel-'+t);
                if(b){b.style.background='rgba(255,255,255,0.12)'; b.style.color='rgba(255,255,255,0.85)'; b.style.fontWeight='400';}
                if(p) p.classList.add('d-none');
            });
            btn.style.background='#fff'; btn.style.color='#1e3c72'; btn.style.fontWeight='600';
            document.getElementById('tab-panel-'+id).classList.remove('d-none');
            if(history.replaceState) history.replaceState(null,'','#'+id);
        });
    });
    var h=(location.hash||'').replace('#','');
    if(tabs.indexOf(h)!==-1){var el=document.getElementById('tab-btn-'+h); if(el)el.click();}
})();

function confirmDelete(type,id,name){
    document.getElementById('deleteItemName').textContent=name;
    document.getElementById('deleteForm').action='{{ url("modules/master-setup") }}/'+type+'/'+id;
    $('#deleteModal').modal('show');
}

// Category
function resetCategoryForm(){document.getElementById('categoryModalTitle').textContent='Add Category';document.getElementById('categoryForm').action='{{ route("modules.master-setup.categories.store") }}';document.getElementById('categoryMethod').value='POST';document.getElementById('cat-name').value='';document.getElementById('cat-desc').value='';document.getElementById('cat-active').checked=true;}
function editCategory(c){document.getElementById('categoryModalTitle').textContent='Edit Category';document.getElementById('categoryForm').action='{{ url("modules/master-setup/categories") }}/'+c.id;document.getElementById('categoryMethod').value='PUT';document.getElementById('cat-name').value=c.name;document.getElementById('cat-desc').value=c.description||'';document.getElementById('cat-active').checked=c.is_active;$('#categoryModal').modal('show');}

// Unit
function resetUnitForm(){document.getElementById('unitModalTitle').textContent='Add Unit';document.getElementById('unitForm').action='{{ route("modules.master-setup.units.store") }}';document.getElementById('unitMethod').value='POST';document.getElementById('unit-name').value='';document.getElementById('unit-symbol').value='';document.getElementById('unit-desc').value='';document.getElementById('unit-active').checked=true;}
function editUnit(u){document.getElementById('unitModalTitle').textContent='Edit Unit';document.getElementById('unitForm').action='{{ url("modules/master-setup/units") }}/'+u.id;document.getElementById('unitMethod').value='PUT';document.getElementById('unit-name').value=u.name;document.getElementById('unit-symbol').value=u.symbol;document.getElementById('unit-desc').value=u.description||'';document.getElementById('unit-active').checked=u.is_active;$('#unitModal').modal('show');}

// Vendor
function resetVendorForm(){document.getElementById('vendorModalTitle').textContent='Add Vendor';document.getElementById('vendorForm').action='{{ route("modules.master-setup.vendors.store") }}';document.getElementById('vendorMethod').value='POST';['v-name','v-contact','v-phone','v-email','v-address','v-city','v-district','v-state','v-gstin','v-pan','v-bank-name','v-bank-account','v-bank-branch','v-bank-ifsc','v-notes'].forEach(function(id){document.getElementById(id).value='';});document.getElementById('v-bal').value=0;document.getElementById('v-active').checked=true;}
function editVendor(v){document.getElementById('vendorModalTitle').textContent='Edit Vendor';document.getElementById('vendorForm').action='{{ url("modules/master-setup/vendors") }}/'+v.id;document.getElementById('vendorMethod').value='PUT';document.getElementById('v-name').value=v.name;document.getElementById('v-contact').value=v.contact_person||'';document.getElementById('v-phone').value=v.phone||'';document.getElementById('v-email').value=v.email||'';document.getElementById('v-address').value=v.address||'';document.getElementById('v-city').value=v.city||'';document.getElementById('v-district').value=v.district||'';document.getElementById('v-state').value=v.state||'';document.getElementById('v-gstin').value=v.gstin||'';document.getElementById('v-pan').value=v.pan||'';document.getElementById('v-bank-name').value=v.bank_name||'';document.getElementById('v-bank-account').value=v.bank_account_no||'';document.getElementById('v-bank-branch').value=v.bank_branch||'';document.getElementById('v-bank-ifsc').value=v.bank_ifsc||'';document.getElementById('v-bal').value=v.opening_balance;document.getElementById('v-active').checked=v.is_active;document.getElementById('v-notes').value=v.notes||'';$('#vendorModal').modal('show');}

// Customer
function resetCustomerForm(){document.getElementById('customerModalTitle').textContent='Add Customer';document.getElementById('customerForm').action='{{ route("modules.master-setup.customers.store") }}';document.getElementById('customerMethod').value='POST';['c-name','c-contact','c-phone','c-email','c-address','c-city','c-district','c-state','c-gstin','c-pan','c-bank-name','c-bank-account','c-bank-branch','c-bank-ifsc','c-notes'].forEach(function(id){document.getElementById(id).value='';});document.getElementById('c-bal').value=0;document.getElementById('c-active').checked=true;}
function editCustomer(c){document.getElementById('customerModalTitle').textContent='Edit Customer';document.getElementById('customerForm').action='{{ url("modules/master-setup/customers") }}/'+c.id;document.getElementById('customerMethod').value='PUT';document.getElementById('c-name').value=c.name;document.getElementById('c-contact').value=c.contact_person||'';document.getElementById('c-phone').value=c.phone||'';document.getElementById('c-email').value=c.email||'';document.getElementById('c-address').value=c.address||'';document.getElementById('c-city').value=c.city||'';document.getElementById('c-district').value=c.district||'';document.getElementById('c-state').value=c.state||'';document.getElementById('c-gstin').value=c.gstin||'';document.getElementById('c-pan').value=c.pan||'';document.getElementById('c-bank-name').value=c.bank_name||'';document.getElementById('c-bank-account').value=c.bank_account_no||'';document.getElementById('c-bank-branch').value=c.bank_branch||'';document.getElementById('c-bank-ifsc').value=c.bank_ifsc||'';document.getElementById('c-bal').value=c.opening_balance;document.getElementById('c-active').checked=c.is_active;document.getElementById('c-notes').value=c.notes||'';$('#customerModal').modal('show');}

// Product
function resetProductForm(){document.getElementById('productModalTitle').textContent='Add Product';document.getElementById('productForm').action='{{ route("modules.master-setup.products.store") }}';document.getElementById('productMethod').value='POST';['p-name','p-code','p-hsn','p-desc','p-gst-custom'].forEach(function(id){document.getElementById(id).value='';});document.getElementById('p-cat').value='';document.getElementById('p-unit').value='';document.getElementById('p-pr').value=0;document.getElementById('p-sr').value=0;document.getElementById('p-gst').value='{{ $defaultGstRate !== null && $defaultGstRate !== '' ? $defaultGstRate : '' }}';document.getElementById('p-low').value='';document.getElementById('p-active').checked=true;toggleCustomGstInput();}
function editProduct(p){document.getElementById('productModalTitle').textContent='Edit Product';document.getElementById('productForm').action='{{ url("modules/master-setup/products") }}/'+p.id;document.getElementById('productMethod').value='PUT';document.getElementById('p-name').value=p.name;document.getElementById('p-code').value=p.code||'';document.getElementById('p-cat').value=p.category_id||'';document.getElementById('p-unit').value=p.unit_id||'';document.getElementById('p-hsn').value=p.hsn_code||'';document.getElementById('p-pr').value=p.purchase_rate;document.getElementById('p-sr').value=p.sale_rate;document.getElementById('p-low').value=p.low_stock_threshold||'';document.getElementById('p-active').checked=p.is_active;document.getElementById('p-desc').value=p.description||'';applyGstValue(p.gst_percent);$('#productModal').modal('show');}
function applyGstValue(gst){var gstSelect=document.getElementById('p-gst');var normalized=(gst===null||gst===undefined||gst==='')?'':String(parseFloat(gst));if(normalized===''||normalized==='NaN'){gstSelect.value='';document.getElementById('p-gst-custom').value='';toggleCustomGstInput();return;}var existing=Array.from(gstSelect.options).some(function(opt){return opt.value===normalized;});if(existing){gstSelect.value=normalized;document.getElementById('p-gst-custom').value='';}else{gstSelect.value='__custom__';document.getElementById('p-gst-custom').value=normalized;}toggleCustomGstInput();}
function toggleCustomGstInput(){var gstSelect=document.getElementById('p-gst');var custom=document.getElementById('p-gst-custom');if(gstSelect.value==='__custom__'){custom.classList.remove('d-none');custom.setAttribute('name','gst_percent');gstSelect.removeAttribute('name');}else{custom.classList.add('d-none');custom.removeAttribute('name');gstSelect.setAttribute('name','gst_percent');}}
function syncCustomGstValue(){var val=document.getElementById('p-gst-custom').value;if(val!==''&&parseFloat(val)>=0&&parseFloat(val)<=100){return;}}
function openStockModal(id,name,currentStock){document.getElementById('stockForm').action='{{ url("modules/master-setup/products") }}/'+id+'/stock';document.getElementById('stock-product-name').textContent=name;document.getElementById('stock-current-value').textContent=currentStock;document.getElementById('stock-operation').value='add';document.getElementById('stock-quantity').value='';document.getElementById('stock-notes').value='';$('#stockModal').modal('show');}
function openStockLogs(id,name){document.getElementById('stock-log-product-name').textContent=name;var body=document.getElementById('stock-log-body');body.innerHTML='<tr><td colspan="8" class="text-center text-muted py-3">Loading logs...</td></tr>';$('#stockLogsModal').modal('show');fetch('{{ url("modules/master-setup/products") }}/'+id+'/stock-logs').then(function(r){return r.json();}).then(function(data){if(!data.logs||!data.logs.length){body.innerHTML='<tr><td colspan="8" class="text-center text-muted py-3">No logs found.</td></tr>';return;}body.innerHTML='';data.logs.forEach(function(l){var typeText=(l.reference_type||l.type||'').replace(/_/g,' ').toUpperCase();var row='<tr><td>'+(l.created_at||'-')+'</td><td>'+typeText+'</td><td class="text-right">'+Number(l.quantity).toFixed(3)+'</td><td class="text-right">'+Number(l.stock_before).toFixed(3)+'</td><td class="text-right">'+Number(l.stock_after).toFixed(3)+'</td><td>'+(l.reference_type||'-')+'</td><td>'+(l.user||'-')+'</td><td>'+(l.notes||'-')+'</td></tr>';body.insertAdjacentHTML('beforeend',row);});}).catch(function(){body.innerHTML='<tr><td colspan="8" class="text-center text-danger py-3">Failed to load logs.</td></tr>';});}
document.getElementById('productForm').addEventListener('submit', function(){var h=document.getElementById('p-hsn');if(h&&h.value){h.value=h.value.replace(/\D+/g,'');}});
toggleCustomGstInput();
</script>
@endsection
