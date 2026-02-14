@extends('layouts.app')

@section('title', 'Create Invoice')
@section('header', 'Create Invoice')

@push('styles')
<style>
    .form-section { border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .form-section label { font-size: 0.75rem; color: var(--invoice-label-color, rgba(255,255,255,0.5)); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .form-section .form-control, .form-section .form-control-sm { font-size: 0.85rem; padding: 8px 12px; }
    .items-scroll { max-height: 300px; overflow-y: auto; }
    .items-table th { font-size: 0.72rem; padding: 8px 10px !important; }
    .items-table td { font-size: 0.82rem; padding: 6px 10px !important; }
    .summary-pill { display: inline-block; min-width: 90px; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-align: center; }
    .template-selector { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    .template-option { position: relative; cursor: pointer; border: 2px solid var(--border-color); border-radius: 10px; padding: 14px 16px; min-width: 160px; transition: all 0.2s ease; }
    .template-option:hover { border-color: #667eea; background: rgba(102,126,234,0.05); }
    .template-option.selected { border-color: #667eea; background: rgba(102,126,234,0.1); box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
    .template-option input[type="radio"] { display: none; }
    .template-option .tpl-name { font-weight: 700; font-size: 0.9rem; }
    .template-option .tpl-type { font-size: 0.68rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .template-option .tpl-check { position: absolute; top: 8px; right: 8px; width: 22px; height: 22px; border-radius: 50%; background: #667eea; color: #fff; display: none; align-items: center; justify-content: center; font-size: 0.7rem; }
    .template-option.selected .tpl-check { display: flex; }
    .type-filter-btn { padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-secondary); cursor: pointer; font-size: 0.78rem; font-weight: 600; transition: all 0.2s ease; }
    .type-filter-btn:hover, .type-filter-btn.active { background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; border-color: #667eea; }
</style>
@endpush

@section('content')

{{-- Toast --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button>@foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach</div>
@endif

<a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm mb-3"><i class="fas fa-arrow-left mr-1"></i> Back to Invoices</a>

<form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">@csrf

{{-- Step 1: Select Template --}}
<div class="card mb-4">
    <div class="card-header" style="background:linear-gradient(135deg,#667eea,#764ba2) !important; border-radius:12px 12px 0 0 !important;">
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-palette mr-2"></i>Step 1: Choose Template</h5>
    </div>
    <div class="card-body">
        @if($templates->count() > 0)
        {{-- Type filter --}}
        <div class="d-flex flex-wrap mb-3" style="gap:8px;">
            <button type="button" class="type-filter-btn active" onclick="filterTemplates('all',this)">All</button>
            @foreach($types as $key => $label)
                @if($templates->where('type', $key)->count() > 0)
                <button type="button" class="type-filter-btn" onclick="filterTemplates('{{ $key }}',this)">{{ $label }}</button>
                @endif
            @endforeach
        </div>
        <div class="template-selector" id="templateSelector">
            @foreach($templates as $tpl)
            <label class="template-option" data-type="{{ $tpl->type }}" onclick="selectTemplate(this)">
                <input type="radio" name="template_id" value="{{ $tpl->id }}" {{ $tpl->is_default ? 'checked' : '' }} required>
                <div class="tpl-check"><i class="fas fa-check"></i></div>
                <div class="tpl-name">{{ $tpl->name }}</div>
                <div class="tpl-type">{{ $types[$tpl->type] ?? $tpl->type }}</div>
                @if($tpl->is_default)
                <span class="badge badge-success mt-1" style="font-size:0.62rem;">Default</span>
                @endif
            </label>
            @endforeach
        </div>
        @else
        <div class="text-center" style="padding:30px;">
            <i class="fas fa-palette d-block" style="font-size:2rem; color:var(--text-muted); margin-bottom:12px;"></i>
            <p class="text-muted">No active templates found. <a href="{{ route('invoices.templates') }}">Create one first.</a></p>
        </div>
        @endif
    </div>
</div>

{{-- Step 2: Invoice Details --}}
<div class="card mb-4">
    <div class="card-header" style="background:linear-gradient(135deg,#00b894,#00cec9) !important; border-radius:0 !important;">
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-file-invoice-dollar mr-2"></i>Step 2: Invoice Details</h5>
    </div>
    <div class="card-body">
        {{-- Doc details --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-3 form-group"><label>Doc. Type</label><select name="document_type" class="form-control form-control-sm"><option value="Tax Invoice">TAX INVOICE</option><option value="Bill of Supply">BILL OF SUPPLY</option><option value="Proforma">PROFORMA</option></select></div>
                <div class="col-md-3 form-group"><label>Doc. Date</label><input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                <div class="col-md-3 form-group"><label>Payment Mode</label><select name="payment_mode" class="form-control form-control-sm"><option value="CASH">CASH</option><option value="CREDIT">CREDIT</option><option value="UPI">UPI</option><option value="BANK">BANK</option></select></div>
                <div class="col-md-3 form-group"><label>Customer</label><select name="customer_id" class="form-control form-control-sm" id="inv-customer"><option value="">Walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}" data-name="{{ $c->name }}" data-city="{{ $c->city }}" data-state="{{ $c->state }}" data-gstin="{{ $c->gstin }}">{{ $c->name }}</option>@endforeach</select></div>
            </div>
        </div>

        {{-- Party details --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-4 form-group"><label>Party Name</label><input type="text" name="party_name" class="form-control form-control-sm" id="inv-party-name" placeholder="Party Name"></div>
                <div class="col-md-2 form-group"><label>City</label><input type="text" name="city" class="form-control form-control-sm" id="inv-city"></div>
                <div class="col-md-3 form-group"><label>State</label><input type="text" name="state" class="form-control form-control-sm" id="inv-state"></div>
                <div class="col-md-3 form-group"><label>GSTIN</label><input type="text" name="gstin" class="form-control form-control-sm" id="inv-gstin" placeholder="22AAAAA0000A1Z5"></div>
            </div>
        </div>

        {{-- Transport details --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-2 form-group"><label>GR No.</label><input type="text" name="gr_number" class="form-control form-control-sm"></div>
                <div class="col-md-2 form-group"><label>GR Date</label><input type="date" name="gr_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                <div class="col-md-2 form-group"><label>Driver Name</label><input type="text" name="driver_name" class="form-control form-control-sm"></div>
                <div class="col-md-2 form-group"><label>Vehicle</label><input type="text" name="vehicle_number" class="form-control form-control-sm"></div>
                <div class="col-md-2 form-group"><label>Transport</label><input type="text" name="transport_name" class="form-control form-control-sm"></div>
                <div class="col-md-2 form-group"><label>Place of Supply</label><input type="text" name="place_of_supply" class="form-control form-control-sm"></div>
            </div>
        </div>

        {{-- E-way & advance --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-4 form-group"><label>E-Way Bill No.</label><input type="text" name="eway_bill_no" class="form-control form-control-sm"></div>
                <div class="col-md-3 form-group"><label>App. Distance in KMs</label><input type="number" name="distance_km" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-3 form-group"><label>Advance Amount</label><input type="number" name="advance_amount" class="form-control form-control-sm" step="0.01" value="0" id="inv-advance"></div>
            </div>
        </div>
    </div>
</div>

{{-- Step 3: Line Items --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-boxes mr-2" style="color:#00b894;"></i>Step 3: Line Items</h5>
    </div>
    <div class="card-body">
        <div class="form-section" style="background:rgba(0,184,148,0.05);">
            <div class="row align-items-end">
                <div class="col-md-3 form-group"><label>Product</label><select id="inv-add-product" class="form-control form-control-sm"><option value="">-- Select --</option>@foreach($products as $pr)<option value="{{ $pr->id }}" data-name="{{ $pr->name }}" data-hsn="{{ $pr->hsn_code }}" data-unit="{{ $pr->unit?->symbol }}" data-rate="{{ $pr->sale_rate }}" data-gst="{{ $pr->gst_percent }}">{{ $pr->name }}</option>@endforeach</select></div>
                <div class="col-md-1 form-group"><label>Qty</label><input type="number" id="inv-add-qty" class="form-control form-control-sm" step="0.001" value="1"></div>
                <div class="col-md-1 form-group"><label>Unit</label><input type="text" id="inv-add-unit" class="form-control form-control-sm" readonly></div>
                <div class="col-md-1 form-group"><label>HSN</label><input type="text" id="inv-add-hsn" class="form-control form-control-sm" readonly></div>
                <div class="col-md-2 form-group"><label>Price</label><input type="number" id="inv-add-rate" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>Tax %</label><input type="number" id="inv-add-gst" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><button type="button" class="btn btn-success btn-sm btn-block" onclick="addRow()" style="font-weight:600;"><i class="fas fa-plus"></i> ADD</button></div>
            </div>
        </div>

        <div class="items-scroll mt-3">
            <table class="table table-hover items-table mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>HSN</th>
                        <th class="text-right">Qty</th>
                        <th>Unit</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Tax%</th>
                        <th class="text-right">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-end mt-3" style="gap:12px; flex-wrap:wrap;">
            <div class="summary-pill" style="background:rgba(102,126,234,0.15); color:#667eea;">Taxable: <span id="sumTaxable">0.00</span></div>
            <div class="summary-pill" style="background:rgba(253,203,110,0.15); color:#fdcb6e;">GST: <span id="sumGst">0.00</span></div>
            <div class="summary-pill" style="background:rgba(0,184,148,0.15); color:#00b894;">Net: <span id="sumNet">0.00</span></div>
        </div>
    </div>
</div>

{{-- Submit --}}
<div class="text-right mb-4">
    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
        <i class="fas fa-file-invoice mr-2"></i> Generate Invoice
    </button>
</div>

</form>

@endsection

@push('scripts')
<script>
var items = [];
var rowIndex = 0;

// Customer auto-fill
document.getElementById('inv-customer').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('inv-party-name').value = opt.dataset.name || '';
    document.getElementById('inv-city').value = opt.dataset.city || '';
    document.getElementById('inv-state').value = opt.dataset.state || '';
    document.getElementById('inv-gstin').value = opt.dataset.gstin || '';
});

// Product auto-fill
document.getElementById('inv-add-product').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('inv-add-unit').value = opt.dataset.unit || '';
    document.getElementById('inv-add-hsn').value = opt.dataset.hsn || '';
    document.getElementById('inv-add-rate').value = opt.dataset.rate || '';
    document.getElementById('inv-add-gst').value = opt.dataset.gst || '';
});

// Template selector
function selectTemplate(label) {
    document.querySelectorAll('.template-option').forEach(el => el.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type="radio"]').checked = true;
}

// Init default
document.querySelectorAll('.template-option input[type="radio"]:checked').forEach(function(r) {
    r.closest('.template-option').classList.add('selected');
});

// Filter templates by type
function filterTemplates(type, btn) {
    document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.template-option').forEach(function(el) {
        el.style.display = (type === 'all' || el.dataset.type === type) ? '' : 'none';
    });
}

function addRow() {
    var productSelect = document.getElementById('inv-add-product');
    var opt = productSelect.options[productSelect.selectedIndex];
    var qty = parseFloat(document.getElementById('inv-add-qty').value) || 0;
    var rate = parseFloat(document.getElementById('inv-add-rate').value) || 0;
    var gst = parseFloat(document.getElementById('inv-add-gst').value) || 0;
    if (qty <= 0 || rate <= 0) { alert('Enter valid quantity and rate.'); return; }

    var productId = productSelect.value;
    var taxable = Math.round(qty * rate * 100) / 100;
    var gstAmt = gst ? Math.round(taxable * (gst / 100) * 100) / 100 : 0;
    var amount = taxable + gstAmt;

    items.push({
        product_id: productId,
        product_name: opt.dataset.name || opt.text,
        hsn: document.getElementById('inv-add-hsn').value,
        unit: document.getElementById('inv-add-unit').value,
        qty: qty,
        rate: rate,
        gst: gst,
        taxable: taxable,
        gstAmt: gstAmt,
        amount: amount,
    });
    renderTable();
    // Reset
    productSelect.value = '';
    document.getElementById('inv-add-qty').value = 1;
    document.getElementById('inv-add-unit').value = '';
    document.getElementById('inv-add-hsn').value = '';
    document.getElementById('inv-add-rate').value = '';
    document.getElementById('inv-add-gst').value = '';
}

function removeRow(idx) {
    items.splice(idx, 1);
    renderTable();
}

function renderTable() {
    var tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    var sumTaxable = 0, sumGst = 0;
    items.forEach(function(item, i) {
        sumTaxable += item.taxable;
        sumGst += item.gstAmt;
        tbody.innerHTML += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td><strong>' + escHtml(item.product_name) + '</strong>' +
                '<input type="hidden" name="items[' + i + '][product_id]" value="' + (item.product_id || '') + '">' +
                '<input type="hidden" name="items[' + i + '][quantity]" value="' + item.qty + '">' +
                '<input type="hidden" name="items[' + i + '][rate]" value="' + item.rate + '">' +
                '<input type="hidden" name="items[' + i + '][gst_percent]" value="' + item.gst + '">' +
            '</td>' +
            '<td>' + escHtml(item.hsn) + '</td>' +
            '<td class="text-right">' + item.qty.toFixed(3) + '</td>' +
            '<td>' + escHtml(item.unit) + '</td>' +
            '<td class="text-right">' + item.rate.toFixed(2) + '</td>' +
            '<td class="text-right">' + (item.gst ? item.gst.toFixed(1) + '%' : '') + '</td>' +
            '<td class="text-right" style="font-weight:700;">' + item.amount.toFixed(2) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(' + i + ')"><i class="fas fa-times"></i></button></td>' +
        '</tr>';
    });
    document.getElementById('sumTaxable').textContent = sumTaxable.toFixed(2);
    document.getElementById('sumGst').textContent = sumGst.toFixed(2);
    document.getElementById('sumNet').textContent = (sumTaxable + sumGst).toFixed(2);
}

function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
}

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one line item.');
        return;
    }
    if (!document.querySelector('input[name="template_id"]:checked')) {
        e.preventDefault();
        alert('Please select a template.');
        return;
    }
});
</script>
@endpush
