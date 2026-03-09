@extends('layouts.app')

@section('title', 'New Sale')
@section('header', 'New Sale Invoice')

@push('styles')
<style>
    .form-section { border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .form-section label { font-size: 0.75rem; color: var(--invoice-label-color, rgba(255,255,255,0.5)); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .form-section .form-control, .form-section .form-control-sm { font-size: 0.85rem; padding: 8px 12px; }
    .doc-date-highlight input { border-color: rgba(102,126,234,0.7) !important; background: rgba(102,126,234,0.12) !important; color: #cfd8ff !important; }
    .gr-date-highlight input { border-color: rgba(0,184,148,0.7) !important; background: rgba(0,184,148,0.12) !important; color: #bff7e8 !important; }
    .party-highlight { border-color: rgba(0,184,148,0.45); background: rgba(0,184,148,0.06); }
    .party-highlight .form-control { font-size: 0.92rem; font-weight: 600; }
    .items-scroll { max-height: 300px; overflow-y: auto; }
    .items-table th { font-size: 0.72rem; padding: 8px 10px !important; }
    .items-table td { font-size: 0.82rem; padding: 6px 10px !important; }
    .summary-pill { display: inline-block; min-width: 90px; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-align: center; }
    .tax-meta-label { font-weight: 700; letter-spacing: 0.6px; }
    .tax-meta-input { font-weight: 700; }
    [data-theme="dark"] .tax-meta-label { color: #8ec5ff !important; }
    [data-theme="light"] .tax-meta-label { color: #1d4ed8 !important; }
    [data-theme="dark"] .tax-meta-input { color: #eaf4ff !important; background: rgba(102,126,234,0.18) !important; border-color: rgba(102,126,234,0.55) !important; }
    [data-theme="light"] .tax-meta-input { color: #0f172a !important; background: rgba(37,99,235,0.08) !important; border-color: rgba(37,99,235,0.40) !important; }
    .items-table td.tax-percent-cell { font-weight: 700; }
    [data-theme="dark"] .items-table td.tax-percent-cell { color: #8ec5ff; }
    [data-theme="light"] .items-table td.tax-percent-cell { color: #1d4ed8; }
</style>
@endpush

@section('content')

{{-- Toast --}}
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    @foreach($errors->messages() as $field => $messages)
        <div><strong>{{ ucfirst(str_replace(['.', '_'], [' ', ' '], $field)) }}:</strong> {{ $messages[0] }}</div>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('modules.transactions.sales.store') }}" id="saleForm">@csrf

{{-- Row 1: Doc details --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="form-section">
            <div class="row">
                <div class="col-md-3 form-group"><label>Doc. Type</label><select name="document_type" class="form-control form-control-sm"><option value="Tax Invoice" @selected(old('document_type', 'Tax Invoice') === 'Tax Invoice')>TAX INVOICE</option><option value="Bill of Supply" @selected(old('document_type') === 'Bill of Supply')>BILL OF SUPPLY</option><option value="Proforma" @selected(old('document_type') === 'Proforma')>PROFORMA</option></select></div>
                <div class="col-md-3 form-group doc-date-highlight"><label>Doc. Date</label><input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ old('invoice_date', date('Y-m-d')) }}"></div>
                <div class="col-md-3 form-group"><label>Payment Mode</label><select name="payment_mode" class="form-control form-control-sm"><option value="CASH" @selected(old('payment_mode', 'CASH') === 'CASH')>CASH</option><option value="CREDIT" @selected(old('payment_mode') === 'CREDIT')>CREDIT</option><option value="UPI" @selected(old('payment_mode') === 'UPI')>UPI</option><option value="BANK" @selected(old('payment_mode') === 'BANK')>BANK</option></select></div>
                <div class="col-md-3 form-group"><label>Customer</label><select name="customer_id" class="form-control form-control-sm" id="sale-customer"><option value="" @selected(!old('customer_id'))>Walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}" data-name="{{ $c->name }}" data-city="{{ $c->city }}" data-district="{{ $c->district }}" data-state="{{ $c->state }}" data-gstin="{{ $c->gstin }}" @selected((string) old('customer_id') === (string) $c->id)>{{ $c->name }}</option>@endforeach</select></div>
            </div>
        </div>

        {{-- Party details --}}
        <div class="form-section party-highlight">
            <div class="row">
                <div class="col-md-3 form-group"><label>Party Name</label><input type="text" name="party_name" class="form-control form-control-sm" id="sale-party-name" placeholder="Party Name" value="{{ old('party_name') }}"></div>
                <div class="col-md-2 form-group"><label>City</label><input type="text" name="city" class="form-control form-control-sm" id="sale-city" value="{{ old('city') }}"></div>
                <div class="col-md-2 form-group"><label>District</label><input type="text" name="district" class="form-control form-control-sm" id="sale-district" value="{{ old('district') }}"></div>
                <div class="col-md-2 form-group"><label>State</label><input type="text" name="state" class="form-control form-control-sm" id="sale-state" value="{{ old('state') }}"></div>
                <div class="col-md-3 form-group"><label>GSTIN</label><input type="text" name="gstin" class="form-control form-control-sm" id="sale-gstin" placeholder="22AAAAA0000A1Z5" value="{{ old('gstin') }}"></div>
            </div>
        </div>

        {{-- Transport details --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-2 form-group"><label>GR No.</label><input type="text" name="gr_number" class="form-control form-control-sm" value="{{ old('gr_number') }}"></div>
                <div class="col-md-2 form-group gr-date-highlight"><label>GR Date</label><input type="date" name="gr_date" class="form-control form-control-sm" value="{{ old('gr_date', date('Y-m-d')) }}"></div>
                <div class="col-md-2 form-group"><label>Driver Name</label><input type="text" name="driver_name" class="form-control form-control-sm" value="{{ old('driver_name') }}"></div>
                <div class="col-md-2 form-group"><label>Vehicle</label><input type="text" name="vehicle_number" class="form-control form-control-sm" value="{{ old('vehicle_number') }}"></div>
                <div class="col-md-2 form-group"><label>Transport</label><input type="text" name="transport_name" class="form-control form-control-sm" value="{{ old('transport_name') }}"></div>
                <div class="col-md-2 form-group"><label>Place of Supply</label><input type="text" name="place_of_supply" id="sale-place-of-supply" class="form-control form-control-sm" value="{{ old('place_of_supply') }}" placeholder="State for IGST"></div>
            </div>
        </div>

        {{-- E-way & advance --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-4 form-group"><label>E-Way Bill No.</label><input type="text" name="eway_bill_no" class="form-control form-control-sm" value="{{ old('eway_bill_no') }}"></div>
                <div class="col-md-3 form-group"><label>App. Distance in KMs</label><input type="number" name="distance_km" class="form-control form-control-sm" step="0.01" value="{{ old('distance_km') }}"></div>
                <div class="col-md-3 form-group"><label>Advance Amount</label><input type="number" name="advance_amount" class="form-control form-control-sm" step="0.01" value="{{ old('advance_amount', 0) }}"></div>
            </div>
        </div>
    </div>
</div>

{{-- Item Entry --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-boxes mr-2" style="color:#00b894;"></i>Line Items</h5></div>
    <div class="card-body">
        <div class="form-section" style="background:rgba(0,184,148,0.05);">
            <div class="row align-items-end">
                <div class="col-md-3 form-group"><label>Product</label><select id="sale-add-product" class="form-control form-control-sm"><option value="">-- Select --</option>@foreach($products as $pr)<option value="{{ $pr->id }}" data-name="{{ $pr->name }}" data-hsn="{{ $pr->hsn_code }}" data-unit="{{ $pr->unit?->symbol }}" data-rate="{{ $pr->sale_rate }}" data-gst="{{ $pr->gst_percent }}">{{ $pr->name }}</option>@endforeach</select></div>
                <div class="col-md-1 form-group"><label>Qty</label><input type="number" id="sale-add-qty" class="form-control form-control-sm" step="0.001" value="1"></div>
                <div class="col-md-1 form-group"><label class="tax-meta-label">Unit</label><input type="text" id="sale-add-unit" class="form-control form-control-sm tax-meta-input" readonly></div>
                <div class="col-md-1 form-group"><label class="tax-meta-label">HSN</label><input type="text" id="sale-add-hsn" class="form-control form-control-sm tax-meta-input" readonly></div>
                <div class="col-md-2 form-group"><label>Price</label><input type="number" id="sale-add-rate" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label class="tax-meta-label">Tax %</label><input type="number" id="sale-add-gst" class="form-control form-control-sm tax-meta-input" step="0.01"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><button type="button" class="btn btn-success btn-sm btn-block" onclick="addRow()" style="font-weight:600;"><i class="fas fa-plus"></i> ADD</button></div>
            </div>
        </div>

        <div class="items-scroll mt-3">
            <table class="table table-hover items-table mb-0" id="items-table">
                <thead><tr><th>Description</th><th class="text-right">Qty</th><th>Unit</th><th>HSN</th><th class="text-right">Rate</th><th class="text-right">Taxable (Qty×Rate)</th><th>Tax%</th><th class="text-right">CGST</th><th class="text-right">SGST</th><th class="text-right">IGST</th><th class="text-right">Total</th><th></th></tr></thead>
                <tbody></tbody>
                <tfoot>
                    <tr style="font-weight:700; background:rgba(102,126,234,0.08);">
                        <td colspan="12">
                            Product Total: <span id="product-total-count">0</span> | Total Qty: <span id="product-total-qty">0.000</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="hidden-items"></div>

        {{-- Summary --}}
        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:10px;">
            <span style="font-weight:600; color:var(--text-muted);">ITEMS: <strong id="total-entries" style="color:var(--text-primary);">0</strong></span>
            <span class="summary-pill" style="background:rgba(0,152,234,0.15); color:#0984e3;" id="total-taxable">Taxable (Qty×Rate): 0.00</span>
            <span class="summary-pill" style="background:rgba(255,118,117,0.15); color:#ff7675;" id="total-tax">GST Amount: 0.00</span>
            <span class="summary-pill" style="background:rgba(0,184,148,0.2); color:#00b894; font-size:1rem;" id="total-net">Overall Total: 0.00</span>
        </div>
        <div class="mt-3 table-responsive">
            <table class="table table-sm table-bordered mb-0" id="tax-slab-table">
                <thead>
                    <tr>
                        <th>GST %</th>
                        <th class="text-right">Taxable</th>
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                        <th class="text-right">IGST</th>
                        <th class="text-right">Tax Total</th>
                        <th class="text-right">Gross</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot>
                    <tr style="font-weight:700;">
                        <td>Grand Total</td>
                        <td class="text-right" id="slab-grand-taxable">0.00</td>
                        <td class="text-right" id="slab-grand-cgst">0.00</td>
                        <td class="text-right" id="slab-grand-sgst">0.00</td>
                        <td class="text-right" id="slab-grand-igst">0.00</td>
                        <td class="text-right" id="slab-grand-tax">0.00</td>
                        <td class="text-right" id="slab-grand-gross">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="d-flex mb-4" style="gap:12px;">
    <a href="{{ route('modules.transactions') }}#sales" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> BACK</a>
    <button type="submit" name="after_action" value="generate" class="btn btn-success" style="padding:10px 22px; font-weight:600;"><i class="fas fa-file-alt mr-2"></i>SAVE &amp; GENERATE</button>
    <button type="submit" name="after_action" value="print" class="btn btn-info" style="padding:10px 22px; font-weight:600;"><i class="fas fa-print mr-2"></i>SAVE &amp; PRINT</button>
    <button type="button" class="btn btn-secondary" onclick="resetForm()"><i class="fas fa-redo mr-1"></i> RESET</button>
    <a href="{{ route('modules.transactions') }}#sales" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> CANCEL</a>
</div>

</form>

<script>
var itemCounter = 0;
var sellerState = @json($sellerState ?? '');

function isIntraState() {
    var buyerState = (document.getElementById('sale-place-of-supply')?.value || document.getElementById('sale-state')?.value || '').trim().toLowerCase().replace(/\s+/g, ' ');
    if (!sellerState || !buyerState) return true; // default intra-state (CGST+SGST)
    return sellerState === buyerState;
}

function splitGst(gstAmt, intraState) {
    if (intraState) {
        var half = Math.round((gstAmt / 2) * 100) / 100;
        return { cgst: half, sgst: Math.round((gstAmt - half) * 100) / 100, igst: 0 };
    }
    return { cgst: 0, sgst: 0, igst: gstAmt };
}

// Recalculate CGST/SGST/IGST when Place of Supply or State changes
['sale-place-of-supply','sale-state'].forEach(function(id){
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', function(){ recalcAllRowsTaxSplit(); });
});

function recalcAllRowsTaxSplit() {
    var rows = document.querySelectorAll('#items-table tbody tr');
    rows.forEach(function(tr){
        var idx = tr.getAttribute('data-idx');
        var hDiv = document.querySelector('#hidden-items div[data-idx="'+idx+'"]');
        if (!hDiv) return;
        var qty = parseFloat(hDiv.querySelector('input[name="items['+idx+'][quantity]"]')?.value) || 0;
        var rate = parseFloat(hDiv.querySelector('input[name="items['+idx+'][rate]"]')?.value) || 0;
        var gst = parseFloat(hDiv.querySelector('input[name="items['+idx+'][gst_percent]"]')?.value) || 0;
        var taxable = Math.round(qty * rate * 100) / 100;
        var tax = gst > 0 ? Math.round(taxable * (gst/100) * 100) / 100 : 0;
        var split = splitGst(tax, isIntraState());
        var cells = tr.querySelectorAll('td');
        if (cells.length >= 11) {
            cells[7].textContent = split.cgst > 0 ? split.cgst.toFixed(2) : '-';
            cells[8].textContent = split.sgst > 0 ? split.sgst.toFixed(2) : '-';
            cells[9].textContent = split.igst > 0 ? split.igst.toFixed(2) : '-';
            cells[10].textContent = (taxable + tax).toFixed(2);
        }
    });
    updateTotals();
}

// Auto-fill party from customer select
document.getElementById('sale-customer').addEventListener('change', function(){
    var opt = this.options[this.selectedIndex];
    document.getElementById('sale-party-name').value = opt.dataset.name || '';
    document.getElementById('sale-city').value = opt.dataset.city || '';
    document.getElementById('sale-district').value = opt.dataset.district || '';
    document.getElementById('sale-state').value = opt.dataset.state || '';
    document.getElementById('sale-gstin').value = opt.dataset.gstin || '';
});

// Auto-fill product fields
document.getElementById('sale-add-product').addEventListener('change', function(){
    var opt = this.options[this.selectedIndex];
    document.getElementById('sale-add-unit').value = opt.dataset.unit || '';
    document.getElementById('sale-add-hsn').value = opt.dataset.hsn || '';
    document.getElementById('sale-add-rate').value = opt.dataset.rate || '';
    document.getElementById('sale-add-gst').value = opt.dataset.gst || '';
});

function addRow() {
    var prod = document.getElementById('sale-add-product');
    var qty = document.getElementById('sale-add-qty');
    var unit = document.getElementById('sale-add-unit');
    var hsn = document.getElementById('sale-add-hsn');
    var rate = document.getElementById('sale-add-rate');
    var gst = document.getElementById('sale-add-gst');
    if(!prod.value || !qty.value || !rate.value) { alert('Select product, enter qty and rate.'); return; }
    var q = parseFloat(qty.value), r = parseFloat(rate.value), g = parseFloat(gst.value) || 0;
    var amount = Math.round(q * r * 100) / 100;
    var tax = g > 0 ? Math.round(amount * (g/100) * 100) / 100 : 0;
    var net = amount + tax;
    var split = splitGst(tax, isIntraState());
    var idx = itemCounter++;
    var pname = prod.options[prod.selectedIndex].text;
    var tbody = document.querySelector('#items-table tbody');
    var tr = document.createElement('tr'); tr.setAttribute('data-idx', idx);
    tr.style.cursor = 'pointer';
    tr.onclick = function(e){ if(e.target.closest('button') || tr.getAttribute('data-editing') === '1') return; enableInlineEdit(tr, idx); };
    tr.innerHTML = '<td style="font-weight:500;">'+pname+'</td><td class="text-right">'+q+'</td><td>'+unit.value+'</td><td>'+hsn.value+'</td>'
        +'<td class="text-right">'+r.toFixed(2)+'</td><td class="text-right" style="color:#667eea;font-weight:600;">'+amount.toFixed(2)+'</td>'
        +'<td class="tax-percent-cell">'+g+'%</td>'
        +'<td class="text-right" style="color:#0984e3;">'+(split.cgst>0?split.cgst.toFixed(2):'-')+'</td>'
        +'<td class="text-right" style="color:#0984e3;">'+(split.sgst>0?split.sgst.toFixed(2):'-')+'</td>'
        +'<td class="text-right" style="color:#e17055;">'+(split.igst>0?split.igst.toFixed(2):'-')+'</td>'
        +'<td class="text-right" style="font-weight:700; color:#55efc4;">'+net.toFixed(2)+'</td>'
        +'<td><button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button></td>';
    tbody.appendChild(tr);
    var hc = document.getElementById('hidden-items');
    var hDiv = document.createElement('div'); hDiv.setAttribute('data-idx', idx);
    hDiv.innerHTML = '<input type="hidden" name="items['+idx+'][product_id]" value="'+prod.value+'"><input type="hidden" name="items['+idx+'][quantity]" value="'+q+'"><input type="hidden" name="items['+idx+'][rate]" value="'+r+'"><input type="hidden" name="items['+idx+'][gst_percent]" value="'+g+'">';
    hc.appendChild(hDiv);
    prod.selectedIndex = 0; qty.value = 1; unit.value = ''; hsn.value = ''; rate.value = ''; gst.value = '';
    if ($('#sale-add-product').data('select2')) $('#sale-add-product').val(null).trigger('change');
    updateTotals();
}

function removeRow(btn, idx) {
    btn.closest('tr').remove();
    var hDiv = document.querySelector('#hidden-items div[data-idx="'+idx+'"]');
    if(hDiv) hDiv.remove();
    updateTotals();
}

function updateTotals() {
    var rows = document.querySelectorAll('#items-table tbody tr');
    var count = rows.length, totalAmt = 0, totalTax = 0, totalNet = 0, totalQty = 0;
    var slabTotals = {};
    var intra = isIntraState();
    rows.forEach(function(tr){
        var cells = tr.querySelectorAll('td');
        totalQty += parseFloat(cells[1].textContent) || 0;
        var taxable = parseFloat(cells[5].textContent) || 0;
        var cgst = parseFloat(String(cells[7].textContent || '').replace('-','0')) || 0;
        var sgst = parseFloat(String(cells[8].textContent || '').replace('-','0')) || 0;
        var igst = parseFloat(String(cells[9].textContent || '').replace('-','0')) || 0;
        var gstAmount = cgst + sgst + igst;
        var gross = parseFloat(cells[10].textContent) || 0;
        var gstPercent = parseFloat(String(cells[6].textContent || '').replace('%','')) || 0;
        var slabKey = String(gstPercent % 1 === 0 ? gstPercent.toFixed(0) : gstPercent.toFixed(2));
        if (!slabTotals[slabKey]) {
            slabTotals[slabKey] = { taxable: 0, cgst: 0, sgst: 0, igst: 0, tax: 0, gross: 0 };
        }
        slabTotals[slabKey].taxable += taxable;
        slabTotals[slabKey].cgst += cgst;
        slabTotals[slabKey].sgst += sgst;
        slabTotals[slabKey].igst += igst;
        slabTotals[slabKey].tax += gstAmount;
        slabTotals[slabKey].gross += gross;
        totalAmt += taxable;
        totalTax += gstAmount;
        totalNet += gross;
    });
    document.getElementById('total-entries').textContent = count;
    document.getElementById('total-taxable').textContent = 'Taxable (Qty\u00d7Rate): ' + totalAmt.toFixed(2);
    document.getElementById('total-tax').textContent = 'GST Amount: ' + totalTax.toFixed(2);
    document.getElementById('total-net').textContent = 'Overall Total: ' + totalNet.toFixed(2);
    document.getElementById('product-total-count').textContent = count;
    document.getElementById('product-total-qty').textContent = totalQty.toFixed(3);
    renderTaxSlabTable(slabTotals);
}

function renderTaxSlabTable(slabTotals) {
    var tbody = document.querySelector('#tax-slab-table tbody');
    var keys = Object.keys(slabTotals).sort(function(a,b){ return parseFloat(a) - parseFloat(b); });
    var grand = { taxable:0, cgst:0, sgst:0, igst:0, tax:0, gross:0 };
    tbody.innerHTML = '';
    keys.forEach(function(key){
        var r = slabTotals[key];
        grand.taxable += r.taxable;
        grand.cgst += r.cgst;
        grand.sgst += r.sgst;
        grand.igst += r.igst;
        grand.tax += r.tax;
        grand.gross += r.gross;
        var tr = document.createElement('tr');
        tr.innerHTML = '<td><strong>'+key+'%</strong></td>'
            +'<td class="text-right">'+r.taxable.toFixed(2)+'</td>'
            +'<td class="text-right">'+r.cgst.toFixed(2)+'</td>'
            +'<td class="text-right">'+r.sgst.toFixed(2)+'</td>'
            +'<td class="text-right">'+r.igst.toFixed(2)+'</td>'
            +'<td class="text-right">'+r.tax.toFixed(2)+'</td>'
            +'<td class="text-right">'+r.gross.toFixed(2)+'</td>';
        tbody.appendChild(tr);
    });
    document.getElementById('slab-grand-taxable').textContent = grand.taxable.toFixed(2);
    document.getElementById('slab-grand-cgst').textContent = grand.cgst.toFixed(2);
    document.getElementById('slab-grand-sgst').textContent = grand.sgst.toFixed(2);
    document.getElementById('slab-grand-igst').textContent = grand.igst.toFixed(2);
    document.getElementById('slab-grand-tax').textContent = grand.tax.toFixed(2);
    document.getElementById('slab-grand-gross').textContent = grand.gross.toFixed(2);
}

function resetForm() {
    document.getElementById('saleForm').reset();
    document.querySelector('#items-table tbody').innerHTML = '';
    document.getElementById('hidden-items').innerHTML = '';
    itemCounter = 0;
    updateTotals();
}

function enableInlineEdit(tr, idx) {
    tr.setAttribute('data-editing', '1');
    var cells = tr.querySelectorAll('td');
    var qty = parseFloat(cells[1].textContent) || 0;
    var rate = parseFloat(cells[4].textContent) || 0;
    var gst = parseFloat(String(cells[6].textContent || '').replace('%','')) || 0;
    cells[1].innerHTML = '<input type="number" class="form-control form-control-sm" id="edit-qty-'+idx+'" value="'+qty+'" step="0.001" min="0.001">';
    cells[4].innerHTML = '<input type="number" class="form-control form-control-sm" id="edit-rate-'+idx+'" value="'+rate+'" step="0.01" min="0">';
    cells[6].innerHTML = '<input type="number" class="form-control form-control-sm tax-meta-input" id="edit-gst-'+idx+'" value="'+gst+'" step="0.01" min="0" max="100">';
    cells[11].innerHTML = '<button type="button" class="btn btn-success btn-sm py-0 px-2 mr-1" onclick="saveInlineEdit('+idx+')"><i class="fas fa-check"></i></button><button type="button" class="btn btn-secondary btn-sm py-0 px-2" onclick="cancelInlineEdit('+idx+')"><i class="fas fa-times"></i></button>';
}

function saveInlineEdit(idx) {
    var tr = document.querySelector('#items-table tbody tr[data-idx="'+idx+'"]');
    if(!tr) return;
    var qty = parseFloat(document.getElementById('edit-qty-'+idx).value) || 0;
    var rate = parseFloat(document.getElementById('edit-rate-'+idx).value) || 0;
    var gst = parseFloat(document.getElementById('edit-gst-'+idx).value) || 0;
    if(qty <= 0 || rate < 0 || gst < 0){ alert('Enter valid values.'); return; }
    var taxable = Math.round(qty * rate * 100) / 100;
    var tax = gst > 0 ? Math.round(taxable * (gst/100) * 100) / 100 : 0;
    var net = taxable + tax;
    var split = splitGst(tax, isIntraState());
    var cells = tr.querySelectorAll('td');
    cells[1].textContent = qty.toString();
    cells[4].textContent = rate.toFixed(2);
    cells[5].textContent = taxable.toFixed(2);
    cells[6].textContent = gst + '%';
    cells[7].textContent = split.cgst > 0 ? split.cgst.toFixed(2) : '-';
    cells[8].textContent = split.sgst > 0 ? split.sgst.toFixed(2) : '-';
    cells[9].textContent = split.igst > 0 ? split.igst.toFixed(2) : '-';
    cells[10].textContent = net.toFixed(2);
    cells[11].innerHTML = '<button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button>';
    var hDiv = document.querySelector('#hidden-items div[data-idx="'+idx+'"]');
    if (hDiv) {
        hDiv.querySelector('input[name="items['+idx+'][quantity]"]').value = qty;
        hDiv.querySelector('input[name="items['+idx+'][rate]"]').value = rate;
        hDiv.querySelector('input[name="items['+idx+'][gst_percent]"]').value = gst;
    }
    tr.setAttribute('data-editing', '0');
    updateTotals();
}

function cancelInlineEdit(idx) {
    var tr = document.querySelector('#items-table tbody tr[data-idx="'+idx+'"]');
    if (!tr) return;
    tr.setAttribute('data-editing', '0');
    var hDiv = document.querySelector('#hidden-items div[data-idx="'+idx+'"]');
    if (!hDiv) return;
    var qty = parseFloat(hDiv.querySelector('input[name="items['+idx+'][quantity]"]').value) || 0;
    var rate = parseFloat(hDiv.querySelector('input[name="items['+idx+'][rate]"]').value) || 0;
    var gst = parseFloat(hDiv.querySelector('input[name="items['+idx+'][gst_percent]"]').value) || 0;
    var taxable = Math.round(qty * rate * 100) / 100;
    var tax = gst > 0 ? Math.round(taxable * (gst/100) * 100) / 100 : 0;
    var net = taxable + tax;
    var split = splitGst(tax, isIntraState());
    var cells = tr.querySelectorAll('td');
    cells[1].textContent = qty.toString();
    cells[4].textContent = rate.toFixed(2);
    cells[5].textContent = taxable.toFixed(2);
    cells[6].textContent = gst + '%';
    cells[7].textContent = split.cgst > 0 ? split.cgst.toFixed(2) : '-';
    cells[8].textContent = split.sgst > 0 ? split.sgst.toFixed(2) : '-';
    cells[9].textContent = split.igst > 0 ? split.igst.toFixed(2) : '-';
    cells[10].textContent = net.toFixed(2);
    cells[11].innerHTML = '<button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button>';
}

// Require at least 1 item
document.getElementById('saleForm').addEventListener('submit', function(e){
    if(document.getElementById('hidden-items').children.length === 0){
        e.preventDefault(); alert('Please add at least one item before saving.');
    }
});

(function restoreOldItems() {
    var oldItems = @json(old('items', []));
    if (!Array.isArray(oldItems) || oldItems.length === 0) return;
    oldItems.forEach(function(item) {
        if (!item || !item.product_id) return;
        var productSelect = document.getElementById('sale-add-product');
        productSelect.value = String(item.product_id);
        var opt = productSelect.options[productSelect.selectedIndex];
        document.getElementById('sale-add-unit').value = (opt && opt.dataset.unit) || '';
        document.getElementById('sale-add-hsn').value = (opt && opt.dataset.hsn) || '';
        document.getElementById('sale-add-qty').value = item.quantity || 1;
        document.getElementById('sale-add-rate').value = item.rate || '';
        document.getElementById('sale-add-gst').value = item.gst_percent ?? ((opt && opt.dataset.gst) || 0);
        addRow();
    });
})();

// Select2 - searchable Customer & Product (minimumResultsForSearch: 0 = always show search box)
$(function(){
    setTimeout(function(){
        if (typeof $.fn.select2 !== 'undefined') {
            $('#sale-customer').select2({ width: '100%', minimumResultsForSearch: 0, placeholder: 'Search customer...' });
            $('#sale-add-product').select2({ width: '100%', minimumResultsForSearch: 0, placeholder: 'Search product...' });
        }
    }, 50);
});
</script>
@endsection
