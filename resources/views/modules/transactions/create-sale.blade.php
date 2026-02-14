@extends('layouts.app')

@section('title', 'New Sale')
@section('header', 'New Sale Invoice')

@push('styles')
<style>
    .form-section { border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .form-section label { font-size: 0.75rem; color: var(--invoice-label-color, rgba(255,255,255,0.5)); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .form-section .form-control, .form-section .form-control-sm { font-size: 0.85rem; padding: 8px 12px; }
    .items-scroll { max-height: 300px; overflow-y: auto; }
    .items-table th { font-size: 0.72rem; padding: 8px 10px !important; }
    .items-table td { font-size: 0.82rem; padding: 6px 10px !important; }
    .summary-pill { display: inline-block; min-width: 90px; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-align: center; }
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

{{-- Back Link --}}
<a href="{{ route('modules.transactions') }}#sales" class="btn btn-secondary btn-sm mb-3"><i class="fas fa-arrow-left mr-1"></i> Back to Transactions</a>

<form method="POST" action="{{ route('modules.transactions.sales.store') }}" id="saleForm">@csrf

{{-- Page Header --}}
<div class="card mb-4" style="background:linear-gradient(135deg,#00b894,#00cec9) !important; border:0 !important;">
    <div class="card-body d-flex justify-content-between align-items-center" style="padding:18px 24px;">
        <h5 class="mb-0" style="color:#fff; font-weight:700;"><i class="fas fa-file-invoice-dollar mr-2"></i>SALES INVOICE</h5>
        <span style="color:rgba(255,255,255,0.7); font-size:0.85rem;">Doc No: <strong style="color:#fff;">Auto</strong></span>
    </div>
</div>

{{-- Row 1: Doc details --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="form-section">
            <div class="row">
                <div class="col-md-3 form-group"><label>Doc. Type</label><select name="document_type" class="form-control form-control-sm"><option value="Tax Invoice">TAX INVOICE</option><option value="Bill of Supply">BILL OF SUPPLY</option><option value="Proforma">PROFORMA</option></select></div>
                <div class="col-md-3 form-group"><label>Doc. Date</label><input type="date" name="invoice_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}"></div>
                <div class="col-md-3 form-group"><label>Payment Mode</label><select name="payment_mode" class="form-control form-control-sm"><option value="CASH">CASH</option><option value="CREDIT">CREDIT</option><option value="UPI">UPI</option><option value="BANK">BANK</option></select></div>
                <div class="col-md-3 form-group"><label>Customer</label><select name="customer_id" class="form-control form-control-sm" id="sale-customer"><option value="">Walk-in</option>@foreach($customers as $c)<option value="{{ $c->id }}" data-name="{{ $c->name }}" data-city="{{ $c->city }}" data-state="{{ $c->state }}" data-gstin="{{ $c->gstin }}">{{ $c->name }}</option>@endforeach</select></div>
            </div>
        </div>

        {{-- Party details --}}
        <div class="form-section">
            <div class="row">
                <div class="col-md-4 form-group"><label>Party Name</label><input type="text" name="party_name" class="form-control form-control-sm" id="sale-party-name" placeholder="Party Name"></div>
                <div class="col-md-2 form-group"><label>City</label><input type="text" name="city" class="form-control form-control-sm" id="sale-city"></div>
                <div class="col-md-3 form-group"><label>State</label><input type="text" name="state" class="form-control form-control-sm" id="sale-state"></div>
                <div class="col-md-3 form-group"><label>GSTIN</label><input type="text" name="gstin" class="form-control form-control-sm" id="sale-gstin" placeholder="22AAAAA0000A1Z5"></div>
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
                <div class="col-md-3 form-group"><label>Advance Amount</label><input type="number" name="advance_amount" class="form-control form-control-sm" step="0.01" value="0"></div>
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
                <div class="col-md-1 form-group"><label>Unit</label><input type="text" id="sale-add-unit" class="form-control form-control-sm" readonly></div>
                <div class="col-md-1 form-group"><label>HSN</label><input type="text" id="sale-add-hsn" class="form-control form-control-sm" readonly></div>
                <div class="col-md-2 form-group"><label>Price</label><input type="number" id="sale-add-rate" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>Tax %</label><input type="number" id="sale-add-gst" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><button type="button" class="btn btn-success btn-sm btn-block" onclick="addRow()" style="font-weight:600;"><i class="fas fa-plus"></i> ADD</button></div>
            </div>
        </div>

        <div class="items-scroll mt-3">
            <table class="table table-hover items-table mb-0" id="items-table">
                <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>HSN</th><th class="text-right">Price</th><th class="text-right">Amount</th><th>Tax%</th><th class="text-right">Tax</th><th class="text-right">Net Amt</th><th></th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
        <div id="hidden-items"></div>

        {{-- Summary --}}
        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:10px;">
            <span style="font-weight:600; color:var(--text-muted);">ITEMS: <strong id="total-entries" style="color:var(--text-primary);">0</strong></span>
            <span class="summary-pill" style="background:rgba(0,152,234,0.15); color:#0984e3;" id="total-taxable">Taxable: 0.00</span>
            <span class="summary-pill" style="background:rgba(255,118,117,0.15); color:#ff7675;" id="total-tax">GST: 0.00</span>
            <span class="summary-pill" style="background:rgba(0,184,148,0.2); color:#00b894;" id="total-net">Net: 0.00</span>
        </div>
    </div>
</div>

{{-- Action Buttons --}}
<div class="d-flex mb-4" style="gap:12px;">
    <button type="submit" class="btn btn-success" style="padding:10px 28px; font-weight:600;"><i class="fas fa-save mr-2"></i>SAVE INVOICE</button>
    <button type="button" class="btn btn-secondary" onclick="resetForm()"><i class="fas fa-redo mr-1"></i> RESET</button>
    <a href="{{ route('modules.transactions') }}#sales" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> CANCEL</a>
</div>

</form>

<script>
var itemCounter = 0;

// Auto-fill party from customer select
document.getElementById('sale-customer').addEventListener('change', function(){
    var opt = this.options[this.selectedIndex];
    document.getElementById('sale-party-name').value = opt.dataset.name || '';
    document.getElementById('sale-city').value = opt.dataset.city || '';
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
    var idx = itemCounter++;
    var pname = prod.options[prod.selectedIndex].text;
    var tbody = document.querySelector('#items-table tbody');
    var tr = document.createElement('tr'); tr.setAttribute('data-idx', idx);
    tr.innerHTML = '<td style="font-weight:500;">'+pname+'</td><td>'+q+'</td><td>'+unit.value+'</td><td>'+hsn.value+'</td>'
        +'<td class="text-right">'+r.toFixed(2)+'</td><td class="text-right">'+amount.toFixed(2)+'</td>'
        +'<td>'+g+'%</td><td class="text-right" style="color:#ff7675;">'+tax.toFixed(2)+'</td>'
        +'<td class="text-right" style="font-weight:700; color:#55efc4;">'+net.toFixed(2)+'</td>'
        +'<td><button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button></td>';
    tbody.appendChild(tr);
    var hc = document.getElementById('hidden-items');
    var hDiv = document.createElement('div'); hDiv.setAttribute('data-idx', idx);
    hDiv.innerHTML = '<input type="hidden" name="items['+idx+'][product_id]" value="'+prod.value+'"><input type="hidden" name="items['+idx+'][quantity]" value="'+q+'"><input type="hidden" name="items['+idx+'][rate]" value="'+r+'"><input type="hidden" name="items['+idx+'][gst_percent]" value="'+g+'">';
    hc.appendChild(hDiv);
    prod.selectedIndex = 0; qty.value = 1; unit.value = ''; hsn.value = ''; rate.value = ''; gst.value = '';
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
    var count = rows.length, totalAmt = 0, totalTax = 0, totalNet = 0;
    rows.forEach(function(tr){
        var cells = tr.querySelectorAll('td');
        totalAmt += parseFloat(cells[5].textContent) || 0;
        totalTax += parseFloat(cells[7].textContent) || 0;
        totalNet += parseFloat(cells[8].textContent) || 0;
    });
    document.getElementById('total-entries').textContent = count;
    document.getElementById('total-taxable').textContent = 'Taxable: ' + totalAmt.toFixed(2);
    document.getElementById('total-tax').textContent = 'GST: ' + totalTax.toFixed(2);
    document.getElementById('total-net').textContent = 'Net: ' + totalNet.toFixed(2);
}

function resetForm() {
    document.getElementById('saleForm').reset();
    document.querySelector('#items-table tbody').innerHTML = '';
    document.getElementById('hidden-items').innerHTML = '';
    itemCounter = 0;
    updateTotals();
}

// Require at least 1 item
document.getElementById('saleForm').addEventListener('submit', function(e){
    if(document.getElementById('hidden-items').children.length === 0){
        e.preventDefault(); alert('Please add at least one item before saving.');
    }
});
</script>
@endsection
