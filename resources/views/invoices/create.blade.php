@extends('layouts.app')

@section('title', 'Create Invoice')
@section('header', 'Create Invoice')

@push('styles')
<style>
    .form-section { border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: 12px; padding: 18px 20px; margin-bottom: 18px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .form-section .section-title { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.06)); display: flex; align-items: center; gap: 8px; }
    .form-section label { font-size: 0.78rem; color: var(--invoice-label-color, rgba(255,255,255,0.55)); text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 5px; font-weight: 600; }
    .form-section .form-control, .form-section .form-control-sm { font-size: 0.88rem; padding: 9px 13px; border-radius: 8px; }
    .form-section .form-control:focus { box-shadow: 0 0 0 3px rgba(102,126,234,0.15); }
    .items-scroll { max-height: 340px; overflow-y: auto; }
    .items-table th { font-size: 0.74rem; padding: 10px 12px !important; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .items-table td { font-size: 0.85rem; padding: 8px 12px !important; vertical-align: middle; }
    .summary-pill { display: inline-flex; align-items: center; gap: 6px; min-width: 100px; padding: 10px 18px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; text-align: center; }
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

    /* Party Search Dropdown */
    .party-search-wrap { position: relative; }
    .party-search-results { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #dee2e6); border-radius: 0 0 10px 10px; max-height: 320px; overflow-y: auto; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    .search-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.05)); transition: background 0.15s; }
    .search-item:hover { background: rgba(102,126,234,0.08); }
    .search-item:last-child { border-bottom: 0; }
    .search-item .si-name { font-weight: 700; font-size: 0.92rem; }
    .search-item .si-badge { font-size: 0.6rem; padding: 2px 8px; border-radius: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
    .si-badge-customer { background: rgba(232,67,147,0.15); color: #e84393; }
    .si-badge-vendor { background: rgba(243,156,18,0.15); color: #f39c12; }
    .search-item .si-row { display: flex; align-items: center; gap: 6px; margin-top: 4px; font-size: 0.73rem; color: var(--text-muted, #888); }
    .search-item .si-row i { width: 14px; text-align: center; font-size: 0.65rem; opacity: 0.7; }
    .search-item .si-gstin { font-family: 'Courier New', monospace; font-weight: 600; color: #667eea; font-size: 0.76rem; }

    /* Location Autocomplete */
    .loc-ac-wrap { position: relative; }
    .loc-ac-dropdown { position: absolute; top: 100%; left: 0; right: 0; z-index: 1050; background: var(--bg-card, #fff); border: 1px solid var(--border-color, #dee2e6); border-radius: 0 0 8px 8px; max-height: 220px; overflow-y: auto; display: none; box-shadow: 0 6px 20px rgba(0,0,0,0.15); }
    .loc-ac-dropdown .loc-item { padding: 8px 12px; cursor: pointer; font-size: 0.84rem; border-bottom: 1px solid var(--border-color, rgba(255,255,255,0.04)); transition: background 0.12s; }
    .loc-ac-dropdown .loc-item:hover, .loc-ac-dropdown .loc-item.active { background: rgba(102,126,234,0.1); }
    .loc-ac-dropdown .loc-item .loc-sub { font-size: 0.7rem; color: var(--text-muted, #999); }

    /* Auto-filled highlight */
    .form-control.auto-filled { border-color: rgba(0,184,148,0.4) !important; background: rgba(0,184,148,0.04) !important; }
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
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-palette mr-2"></i>Step 1 &mdash; Choose Template</h5>
    </div>
    <div class="card-body">
        @if($templates->count() > 0)
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
                @if($tpl->is_default)<span class="badge badge-success mt-1" style="font-size:0.62rem;">Default</span>@endif
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
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-file-invoice-dollar mr-2"></i>Step 2 &mdash; Invoice Details</h5>
    </div>
    <div class="card-body">

        {{-- Document info --}}
        <div class="form-section">
            <div class="section-title" style="color:#667eea;"><i class="fas fa-file-alt"></i> Document Info</div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Doc. Type</label>
                    <select name="document_type" class="form-control form-control-sm">
                        <option value="Tax Invoice">TAX INVOICE</option>
                        <option value="Bill of Supply">BILL OF SUPPLY</option>
                        <option value="Proforma">PROFORMA</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Doc. Date</label>
                    <input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Payment Mode</label>
                    <select name="payment_mode" class="form-control form-control-sm">
                        <option value="CASH">CASH</option>
                        <option value="CREDIT">CREDIT</option>
                        <option value="UPI">UPI</option>
                        <option value="BANK">BANK TRANSFER</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Customer</label>
                    <select name="customer_id" class="form-control form-control-sm" id="inv-customer">
                        <option value="">-- Walk-in --</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}"
                            data-name="{{ $c->name }}"
                            data-state="{{ $c->state }}"
                            data-district="{{ $c->district }}"
                            data-city="{{ $c->city }}"
                            data-gstin="{{ $c->gstin }}"
                            data-bank-name="{{ $c->bank_name }}"
                            data-bank-account="{{ $c->bank_account_no }}"
                            data-bank-branch="{{ $c->bank_branch }}"
                            data-bank-ifsc="{{ $c->bank_ifsc }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Party / Buyer Details with live search --}}
        <div class="form-section">
            <div class="section-title" style="color:#e84393;"><i class="fas fa-user-tie"></i> Party / Buyer Details <small class="ml-auto" style="font-size:0.65rem; font-weight:400; text-transform:none; letter-spacing:0; color:var(--text-muted);">Type party name to search & auto-fill all fields</small></div>
            <div class="row">
                <div class="col-md-12 form-group party-search-wrap mb-3">
                    <label><i class="fas fa-search mr-1" style="font-size:0.65rem;"></i> Party / Company Name</label>
                    <input type="text" name="party_name" class="form-control" id="inv-party-name" placeholder="Start typing vendor, customer or company name to search..." autocomplete="off" style="font-size:1rem; padding:12px 16px; font-weight:600;">
                    <div class="party-search-results" id="partySearchResults"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 form-group loc-ac-wrap">
                    <label><i class="fas fa-map-marker-alt mr-1" style="font-size:0.6rem;"></i> State</label>
                    <input type="text" name="state" class="form-control form-control-sm" id="inv-state" placeholder="Type state..." autocomplete="off">
                    <div class="loc-ac-dropdown" id="stateDropdown"></div>
                </div>
                <div class="col-md-3 form-group loc-ac-wrap">
                    <label>District</label>
                    <input type="text" name="district" class="form-control form-control-sm" id="inv-district" placeholder="Type district..." autocomplete="off">
                    <div class="loc-ac-dropdown" id="districtDropdown"></div>
                </div>
                <div class="col-md-3 form-group loc-ac-wrap">
                    <label>City</label>
                    <input type="text" name="city" class="form-control form-control-sm" id="inv-city" placeholder="Type city..." autocomplete="off">
                    <div class="loc-ac-dropdown" id="cityDropdown"></div>
                </div>
                <div class="col-md-3 form-group">
                    <label><i class="fas fa-id-card mr-1" style="font-size:0.6rem;"></i> GSTIN</label>
                    <input type="text" name="gstin" class="form-control form-control-sm" id="inv-gstin" placeholder="e.g. 22AAAAA0000A1Z5" style="font-family:'Courier New',monospace; font-weight:600; letter-spacing:1px;">
                </div>
            </div>
        </div>

        {{-- Buyer Bank Details --}}
        <div class="form-section" style="border-color:rgba(102,126,234,0.2); background:rgba(102,126,234,0.03);">
            <div class="section-title" style="color:#667eea;"><i class="fas fa-university"></i> Buyer Bank Details</div>
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Bank Name</label>
                    <input type="text" name="buyer_bank_name" class="form-control form-control-sm" id="inv-bank-name" placeholder="e.g. State Bank of India">
                </div>
                <div class="col-md-3 form-group">
                    <label>Account No.</label>
                    <input type="text" name="buyer_bank_account_no" class="form-control form-control-sm" id="inv-bank-account" placeholder="Account Number" style="font-family:'Courier New',monospace; letter-spacing:1px;">
                </div>
                <div class="col-md-3 form-group">
                    <label>Branch</label>
                    <input type="text" name="buyer_bank_branch" class="form-control form-control-sm" id="inv-bank-branch" placeholder="Branch Name">
                </div>
                <div class="col-md-3 form-group">
                    <label>IFSC Code</label>
                    <input type="text" name="buyer_bank_ifsc" class="form-control form-control-sm" id="inv-bank-ifsc" placeholder="e.g. SBIN0001234" style="font-family:'Courier New',monospace; font-weight:600; letter-spacing:1px; text-transform:uppercase;">
                </div>
            </div>
        </div>

        {{-- Transport Details --}}
        <div class="form-section">
            <div class="section-title" style="color:#f39c12;"><i class="fas fa-truck"></i> Transport & E-Way</div>
            <div class="row">
                <div class="col-md-2 form-group"><label>GR No.</label><input type="text" name="gr_number" class="form-control form-control-sm" placeholder="GR No."></div>
                <div class="col-md-2 form-group"><label>GR Date</label><input type="date" name="gr_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                <div class="col-md-2 form-group"><label>Transport</label><input type="text" name="transport_name" class="form-control form-control-sm" placeholder="Transport name"></div>
                <div class="col-md-2 form-group"><label>Vehicle No.</label><input type="text" name="vehicle_number" class="form-control form-control-sm" placeholder="MH12AB1234" style="text-transform:uppercase;"></div>
                <div class="col-md-2 form-group"><label>Driver Name</label><input type="text" name="driver_name" class="form-control form-control-sm" placeholder="Driver"></div>
                <div class="col-md-2 form-group"><label>Place of Supply</label><input type="text" name="place_of_supply" id="inv-place-of-supply" class="form-control form-control-sm" placeholder="State for IGST"></div>
            </div>
            <div class="row mt-1">
                <div class="col-md-3 form-group"><label>E-Way Bill No.</label><input type="text" name="eway_bill_no" class="form-control form-control-sm" placeholder="E-Way Bill Number"></div>
                <div class="col-md-3 form-group"><label>Distance (KMs)</label><input type="number" name="distance_km" class="form-control form-control-sm" step="0.01" placeholder="0.00"></div>
                <div class="col-md-3 form-group"><label>Advance Amount (&#8377;)</label><input type="number" name="advance_amount" class="form-control form-control-sm" step="0.01" value="0" id="inv-advance"></div>
            </div>
        </div>

    </div>
</div>

{{-- Step 3: Line Items --}}
<div class="card mb-4">
    <div class="card-header" style="background:linear-gradient(135deg,#fdcb6e,#f39c12) !important; border-radius:0 !important;">
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-boxes mr-2"></i>Step 3 &mdash; Line Items</h5>
    </div>
    <div class="card-body">
        <div class="form-section" style="background:rgba(0,184,148,0.04); border-color:rgba(0,184,148,0.15);">
            <div class="row align-items-end">
                <div class="col-md-3 form-group">
                    <label>Product</label>
                    <select id="inv-add-product" class="form-control form-control-sm">
                        <option value="">-- Select Product --</option>
                        @foreach($products as $pr)
                        <option value="{{ $pr->id }}" data-name="{{ $pr->name }}" data-hsn="{{ $pr->hsn_code }}" data-unit="{{ $pr->unit?->symbol }}" data-rate="{{ $pr->sale_rate }}" data-gst="{{ $pr->gst_percent }}">{{ $pr->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 form-group"><label>Qty</label><input type="number" id="inv-add-qty" class="form-control form-control-sm" step="0.001" value="1"></div>
                <div class="col-md-1 form-group"><label>Unit</label><input type="text" id="inv-add-unit" class="form-control form-control-sm" readonly style="background:rgba(255,255,255,0.03);"></div>
                <div class="col-md-1 form-group"><label>HSN</label><input type="text" id="inv-add-hsn" class="form-control form-control-sm" readonly style="background:rgba(255,255,255,0.03);"></div>
                <div class="col-md-2 form-group"><label>Price (&#8377;)</label><input type="number" id="inv-add-rate" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>Tax %</label><input type="number" id="inv-add-gst" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><button type="button" class="btn btn-success btn-sm btn-block" onclick="addRow()" style="font-weight:700; padding:9px 0;"><i class="fas fa-plus mr-1"></i>ADD</button></div>
            </div>
        </div>

        <div class="items-scroll mt-3">
            <table class="table table-hover items-table mb-0" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:35px;">#</th>
                        <th>Product</th>
                        <th>HSN</th>
                        <th class="text-right">Qty</th>
                        <th>Unit</th>
                        <th class="text-right">Rate (&#8377;)</th>
                        <th class="text-right">Taxable</th>
                        <th class="text-right">Tax%</th>
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                        <th class="text-right">IGST</th>
                        <th class="text-right">Total (&#8377;)</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="d-flex justify-content-end mt-3" style="gap:14px; flex-wrap:wrap;">
            <div class="summary-pill" style="background:rgba(102,126,234,0.12); color:#667eea;">
                <i class="fas fa-calculator" style="font-size:0.7rem;"></i> Taxable: &#8377;<span id="sumTaxable">0.00</span>
            </div>
            <div class="summary-pill" style="background:rgba(253,203,110,0.15); color:#e17055;">
                <i class="fas fa-percentage" style="font-size:0.7rem;"></i> GST: &#8377;<span id="sumGst">0.00</span>
            </div>
            <div class="summary-pill" style="background:rgba(0,184,148,0.15); color:#00b894; font-size:1.05rem;">
                <i class="fas fa-rupee-sign" style="font-size:0.8rem;"></i> Total: &#8377;<span id="sumNet">0.00</span>
            </div>
        </div>
    </div>
</div>

{{-- Submit --}}
<div class="text-right mb-4">
    <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" style="padding:12px 36px; border-radius:10px; font-weight:700; font-size:1rem;">
        <i class="fas fa-file-invoice mr-2"></i> Generate Invoice
    </button>
</div>

</form>

@endsection

@push('scripts')
<script>
var items = [];
var searchTimer = null;
var sellerState = @json($sellerState ?? '');

function isIntraState() {
    var buyerState = (document.getElementById('inv-place-of-supply')?.value || document.getElementById('inv-state')?.value || '').trim().toLowerCase().replace(/\s+/g, ' ');
    if (!sellerState || !buyerState) return true;
    return sellerState === buyerState;
}

function splitGst(gstAmt, intraState) {
    if (intraState) {
        var half = Math.round((gstAmt / 2) * 100) / 100;
        return { cgst: half, sgst: Math.round((gstAmt - half) * 100) / 100, igst: 0 };
    }
    return { cgst: 0, sgst: 0, igst: gstAmt };
}

// ──────────── Autofill fields list ────────────
var autoFillIds = ['inv-state','inv-district','inv-city','inv-gstin','inv-bank-name','inv-bank-account','inv-bank-branch','inv-bank-ifsc'];

function markAutoFilled() {
    autoFillIds.forEach(function(id) {
        var el = document.getElementById(id);
        if (el && el.value) el.classList.add('auto-filled');
        else if (el) el.classList.remove('auto-filled');
    });
}

function clearAutoFilled() {
    autoFillIds.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.value = ''; el.classList.remove('auto-filled'); }
    });
}

// ──────────── Location Autocomplete URLs ────────────
var locUrls = {
    states: '{{ route("locations.states") }}',
    districts: '{{ route("locations.districts") }}',
    cities: '{{ route("locations.cities") }}'
};

// ──────────── Generic Autocomplete Engine ────────────
function initLocationAC(inputId, dropdownId, urlKey, labelFn, onSelect, paramsFn) {
    var input = document.getElementById(inputId);
    var dropdown = document.getElementById(dropdownId);
    var timer = null;
    var activeIdx = -1;

    input.addEventListener('input', function() {
        var q = this.value.trim();
        clearTimeout(timer);
        if (q.length < 1) { dropdown.style.display = 'none'; return; }
        timer = setTimeout(function() {
            var params = paramsFn ? paramsFn() : {};
            params.q = q;
            var qs = Object.keys(params).map(function(k) { return k + '=' + encodeURIComponent(params[k]); }).join('&');
            fetch(locUrls[urlKey] + '?' + qs)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.length) { dropdown.style.display = 'none'; return; }
                    activeIdx = -1;
                    dropdown.innerHTML = data.map(function(item, idx) {
                        return '<div class="loc-item" data-idx="' + idx + '">' + labelFn(item) + '</div>';
                    }).join('');
                    dropdown.style.display = 'block';
                    dropdown.querySelectorAll('.loc-item').forEach(function(el, idx) {
                        el.addEventListener('click', function() { onSelect(data[idx]); dropdown.style.display = 'none'; });
                    });
                })
                .catch(function() { dropdown.style.display = 'none'; });
        }, 250);
    });

    input.addEventListener('keydown', function(e) {
        var els = dropdown.querySelectorAll('.loc-item');
        if (!els.length || dropdown.style.display === 'none') return;
        if (e.key === 'ArrowDown') { e.preventDefault(); activeIdx = Math.min(activeIdx + 1, els.length - 1); hlItem(els); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activeIdx = Math.max(activeIdx - 1, 0); hlItem(els); }
        else if (e.key === 'Enter' && activeIdx >= 0) { e.preventDefault(); els[activeIdx].click(); }
        else if (e.key === 'Escape') { dropdown.style.display = 'none'; }
    });

    function hlItem(els) {
        els.forEach(function(el, i) { el.classList.toggle('active', i === activeIdx); });
        if (els[activeIdx]) els[activeIdx].scrollIntoView({ block: 'nearest' });
    }

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.style.display = 'none';
    });
}

// ──────────── State Autocomplete ────────────
initLocationAC('inv-state', 'stateDropdown', 'states',
    function(s) { return '<span>' + escHtml(s.name) + '</span><span class="loc-sub ml-2">(Code: ' + s.code + ')</span>'; },
    function(s) {
        document.getElementById('inv-state').value = s.name;
        document.getElementById('inv-district').value = '';
        document.getElementById('inv-city').value = '';
    }
);

// ──────────── District Autocomplete ────────────
initLocationAC('inv-district', 'districtDropdown', 'districts',
    function(d) { return '<span>' + escHtml(d.district) + '</span>' + (d.state ? '<span class="loc-sub ml-2">' + escHtml(d.state) + '</span>' : ''); },
    function(d) {
        document.getElementById('inv-district').value = d.district;
        if (d.state && !document.getElementById('inv-state').value) document.getElementById('inv-state').value = d.state;
    },
    function() { return { state: document.getElementById('inv-state').value }; }
);

// ──────────── City Autocomplete ────────────
initLocationAC('inv-city', 'cityDropdown', 'cities',
    function(c) { return '<span>' + escHtml(c.city) + '</span>' + (c.state ? '<span class="loc-sub ml-2">' + escHtml(c.state) + '</span>' : ''); },
    function(c) {
        document.getElementById('inv-city').value = c.city;
        if (c.state && !document.getElementById('inv-state').value) document.getElementById('inv-state').value = c.state;
    },
    function() { return { state: document.getElementById('inv-state').value }; }
);

// Recalculate CGST/SGST/IGST when Place of Supply or State changes
['inv-place-of-supply','inv-state'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', function(){ renderTable(); });
});

// ──────────── Customer dropdown auto-fill ────────────
document.getElementById('inv-customer').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    if (!opt.value) { clearAutoFilled(); document.getElementById('inv-party-name').value = ''; return; }
    document.getElementById('inv-party-name').value = opt.dataset.name || '';
    document.getElementById('inv-state').value = opt.dataset.state || '';
    document.getElementById('inv-district').value = opt.dataset.district || '';
    document.getElementById('inv-city').value = opt.dataset.city || '';
    document.getElementById('inv-gstin').value = opt.dataset.gstin || '';
    document.getElementById('inv-bank-name').value = opt.dataset.bankName || '';
    document.getElementById('inv-bank-account').value = opt.dataset.bankAccount || '';
    document.getElementById('inv-bank-branch').value = opt.dataset.bankBranch || '';
    document.getElementById('inv-bank-ifsc').value = opt.dataset.bankIfsc || '';
    markAutoFilled();
});

// ──────────── Party Name live search ────────────
var partyInput = document.getElementById('inv-party-name');
var searchResults = document.getElementById('partySearchResults');

partyInput.addEventListener('input', function() {
    var q = this.value.trim();
    clearTimeout(searchTimer);
    if (q.length < 1) { searchResults.style.display = 'none'; return; }
    searchTimer = setTimeout(function() {
        fetch('{{ route("invoices.search-party") }}?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.length) { searchResults.style.display = 'none'; return; }
                var html = '';
                data.forEach(function(p) {
                    var bc = p.type === 'customer' ? 'si-badge-customer' : 'si-badge-vendor';
                    var bl = p.type === 'customer' ? 'Customer' : 'Vendor';
                    var loc = [p.city, p.district, p.state].filter(Boolean).join(', ');

                    html += '<div class="search-item" data-party=\'' + escJsonAttr(JSON.stringify(p)) + '\'>';
                    html += '<div class="d-flex justify-content-between align-items-center">';
                    html += '<span class="si-name">' + escHtml(p.name) + '</span>';
                    html += '<span class="si-badge ' + bc + '">' + bl + '</span>';
                    html += '</div>';
                    if (p.gstin) {
                        html += '<div class="si-row"><i class="fas fa-id-card"></i> <span class="si-gstin">' + escHtml(p.gstin) + '</span></div>';
                    }
                    if (loc) {
                        html += '<div class="si-row"><i class="fas fa-map-marker-alt"></i> ' + escHtml(loc) + '</div>';
                    }
                    if (p.bank_name) {
                        html += '<div class="si-row"><i class="fas fa-university"></i> ' + escHtml(p.bank_name);
                        if (p.bank_account_no) html += ' &middot; A/C: ' + escHtml(p.bank_account_no);
                        if (p.bank_ifsc) html += ' &middot; IFSC: ' + escHtml(p.bank_ifsc);
                        html += '</div>';
                    }
                    html += '</div>';
                });
                searchResults.innerHTML = html;
                searchResults.style.display = 'block';
                searchResults.querySelectorAll('.search-item').forEach(function(el) {
                    el.addEventListener('click', function() { selectParty(JSON.parse(this.dataset.party)); });
                });
            })
            .catch(function() { searchResults.style.display = 'none'; });
    }, 300);
});

document.addEventListener('click', function(e) {
    if (!partyInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

function selectParty(p) {
    document.getElementById('inv-party-name').value = p.name || '';
    document.getElementById('inv-state').value = p.state || '';
    document.getElementById('inv-district').value = p.district || '';
    document.getElementById('inv-city').value = p.city || '';
    document.getElementById('inv-gstin').value = p.gstin || '';
    document.getElementById('inv-bank-name').value = p.bank_name || '';
    document.getElementById('inv-bank-account').value = p.bank_account_no || '';
    document.getElementById('inv-bank-branch').value = p.bank_branch || '';
    document.getElementById('inv-bank-ifsc').value = p.bank_ifsc || '';
    if (p.type === 'customer') {
        var custSelect = document.getElementById('inv-customer');
        for (var i = 0; i < custSelect.options.length; i++) {
            if (custSelect.options[i].value == p.id) { custSelect.selectedIndex = i; break; }
        }
    }
    markAutoFilled();
    searchResults.style.display = 'none';
}

function escJsonAttr(s) { return s.replace(/'/g, '&#39;'); }

// ──────────── Product auto-fill ────────────
document.getElementById('inv-add-product').addEventListener('change', function() {
    var opt = this.options[this.selectedIndex];
    document.getElementById('inv-add-unit').value = opt.dataset.unit || '';
    document.getElementById('inv-add-hsn').value = opt.dataset.hsn || '';
    document.getElementById('inv-add-rate').value = opt.dataset.rate || '';
    document.getElementById('inv-add-gst').value = opt.dataset.gst || '';
});

// ──────────── Template selector ────────────
function selectTemplate(label) {
    document.querySelectorAll('.template-option').forEach(el => el.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type="radio"]').checked = true;
}
document.querySelectorAll('.template-option input[type="radio"]:checked').forEach(function(r) {
    r.closest('.template-option').classList.add('selected');
});

function filterTemplates(type, btn) {
    document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.template-option').forEach(function(el) {
        el.style.display = (type === 'all' || el.dataset.type === type) ? '' : 'none';
    });
}

// ──────────── Line items ────────────
function addRow() {
    var productSelect = document.getElementById('inv-add-product');
    var opt = productSelect.options[productSelect.selectedIndex];
    var qty = parseFloat(document.getElementById('inv-add-qty').value) || 0;
    var rate = parseFloat(document.getElementById('inv-add-rate').value) || 0;
    var gst = parseFloat(document.getElementById('inv-add-gst').value) || 0;
    if (qty <= 0 || rate <= 0) { alert('Enter valid quantity and rate.'); return; }

    var taxable = Math.round(qty * rate * 100) / 100;
    var gstAmt = gst ? Math.round(taxable * (gst / 100) * 100) / 100 : 0;
    var split = splitGst(gstAmt, isIntraState());

    items.push({
        product_id: productSelect.value,
        product_name: opt.dataset.name || opt.text,
        hsn: document.getElementById('inv-add-hsn').value,
        unit: document.getElementById('inv-add-unit').value,
        qty: qty, rate: rate, gst: gst,
        taxable: taxable, gstAmt: gstAmt, amount: taxable + gstAmt,
        cgst: split.cgst, sgst: split.sgst, igst: split.igst,
    });
    renderTable();
    productSelect.value = '';
    document.getElementById('inv-add-qty').value = 1;
    ['inv-add-unit','inv-add-hsn','inv-add-rate','inv-add-gst'].forEach(function(id) { document.getElementById(id).value = ''; });
    if ($('#inv-add-product').data('select2')) $('#inv-add-product').val(null).trigger('change');
}

function removeRow(idx) { items.splice(idx, 1); renderTable(); }

function renderTable() {
    var tbody = document.getElementById('itemsBody');
    tbody.innerHTML = '';
    var sumTaxable = 0, sumGst = 0, sumTotal = 0;
    items.forEach(function(item, i) {
        sumTaxable += item.taxable;
        sumGst += item.gstAmt;
        sumTotal += item.amount;
        var s = splitGst(item.gstAmt || 0, isIntraState());
        var cgst = s.cgst, sgst = s.sgst, igst = s.igst;
        tbody.innerHTML += '<tr>' +
            '<td style="font-weight:600; color:var(--text-muted);">' + (i + 1) + '</td>' +
            '<td><strong>' + escHtml(item.product_name) + '</strong>' +
                '<input type="hidden" name="items[' + i + '][product_id]" value="' + (item.product_id || '') + '">' +
                '<input type="hidden" name="items[' + i + '][quantity]" value="' + item.qty + '">' +
                '<input type="hidden" name="items[' + i + '][rate]" value="' + item.rate + '">' +
                '<input type="hidden" name="items[' + i + '][gst_percent]" value="' + item.gst + '">' +
            '</td>' +
            '<td><span style="font-family:monospace; font-size:0.8rem;">' + escHtml(item.hsn) + '</span></td>' +
            '<td class="text-right">' + item.qty.toFixed(3) + '</td>' +
            '<td>' + escHtml(item.unit) + '</td>' +
            '<td class="text-right" style="font-family:monospace;">' + item.rate.toFixed(2) + '</td>' +
            '<td class="text-right" style="color:#667eea; font-weight:600;">\u20B9' + item.taxable.toFixed(2) + '</td>' +
            '<td class="text-right">' + (item.gst ? item.gst.toFixed(1) + '%' : '-') + '</td>' +
            '<td class="text-right" style="color:#0984e3;">' + (cgst > 0 ? '\u20B9' + cgst.toFixed(2) : '-') + '</td>' +
            '<td class="text-right" style="color:#0984e3;">' + (sgst > 0 ? '\u20B9' + sgst.toFixed(2) : '-') + '</td>' +
            '<td class="text-right" style="color:#e17055;">' + (igst > 0 ? '\u20B9' + igst.toFixed(2) : '-') + '</td>' +
            '<td class="text-right" style="font-weight:700; font-size:0.92rem;">\u20B9' + item.amount.toFixed(2) + '</td>' +
            '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(' + i + ')" style="border-radius:6px;"><i class="fas fa-trash-alt" style="font-size:0.7rem;"></i></button></td>' +
        '</tr>';
    });
    document.getElementById('sumTaxable').textContent = sumTaxable.toFixed(2);
    document.getElementById('sumGst').textContent = sumGst.toFixed(2);
    document.getElementById('sumNet').textContent = sumTotal.toFixed(2);
}

function escHtml(s) { var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

// ──────────── Form validation ────────────
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    if (items.length === 0) { e.preventDefault(); alert('Please add at least one line item.'); return; }
    if (!document.querySelector('input[name="template_id"]:checked')) { e.preventDefault(); alert('Please select a template.'); return; }
});

// Select2 - searchable Customer & Product
$(function(){
    setTimeout(function(){
        if (typeof $.fn.select2 !== 'undefined') {
            $('#inv-customer').select2({ width: '100%', minimumResultsForSearch: 0, placeholder: 'Search customer...' });
            $('#inv-add-product').select2({ width: '100%', minimumResultsForSearch: 0, placeholder: 'Search product...' });
        }
    }, 50);
});
</script>
@endpush
