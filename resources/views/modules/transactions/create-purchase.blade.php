@extends('layouts.app')

@section('title', 'New Purchase')
@section('header', 'New Purchase')

@push('styles')
<style>
    .form-section { border: 1px solid var(--border-color, rgba(255,255,255,0.08)); border-radius: 10px; padding: 16px 18px; margin-bottom: 16px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .form-section label { font-size: 0.75rem; color: var(--invoice-label-color, rgba(255,255,255,0.5)); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    .form-section .form-control, .form-section .form-control-sm { font-size: 0.85rem; padding: 8px 12px; }
    .doc-date-highlight input { border-color: rgba(102,126,234,0.7) !important; background: rgba(102,126,234,0.12) !important; color: #cfd8ff !important; }
    .gr-date-highlight input { border-color: rgba(0,184,148,0.7) !important; background: rgba(0,184,148,0.12) !important; color: #bff7e8 !important; }
    .party-highlight { border-color: rgba(102,126,234,0.45); background: rgba(102,126,234,0.08); }
    .party-highlight .form-control { font-size: 0.92rem; font-weight: 600; }
    .items-scroll { max-height: 300px; overflow-y: auto; }
    .items-table th { font-size: 0.72rem; padding: 8px 10px !important; }
    .items-table td { font-size: 0.82rem; padding: 6px 10px !important; }
    .summary-pill { display: inline-block; min-width: 90px; padding: 8px 16px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; text-align: center; }
</style>
@endpush

@section('content')

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

<form method="POST" action="{{ route('modules.transactions.purchases.store') }}" id="purchaseForm">@csrf

<div class="card mb-4">
    <div class="card-body">
        <div class="form-section">
            <div class="row">
                <div class="col-md-3 form-group"><label>Doc. Type</label><select name="document_type" class="form-control form-control-sm"><option value="Tax Invoice" @selected(old('document_type', 'Tax Invoice') === 'Tax Invoice')>TAX INVOICE</option><option value="Bill of Supply" @selected(old('document_type') === 'Bill of Supply')>BILL OF SUPPLY</option></select></div>
                <div class="col-md-3 form-group doc-date-highlight"><label>Doc. Date</label><input type="date" name="purchase_date" class="form-control form-control-sm" value="{{ old('purchase_date', date('Y-m-d')) }}"></div>
                <div class="col-md-3 form-group"><label>Payment Mode</label><select name="payment_mode" class="form-control form-control-sm"><option value="CASH" @selected(old('payment_mode', 'CASH') === 'CASH')>CASH</option><option value="CREDIT" @selected(old('payment_mode') === 'CREDIT')>CREDIT</option><option value="UPI" @selected(old('payment_mode') === 'UPI')>UPI</option><option value="BANK" @selected(old('payment_mode') === 'BANK')>BANK</option></select></div>
                <div class="col-md-3 form-group"><label>Vendor <span class="text-danger">*</span></label><select name="vendor_id" class="form-control form-control-sm" id="pur-vendor" required><option value="">-- Select --</option>@foreach($vendors as $v)<option value="{{ $v->id }}" data-name="{{ $v->name }}" data-city="{{ $v->city }}" data-district="{{ $v->district }}" data-state="{{ $v->state }}" data-gstin="{{ $v->gstin }}" @selected((string) old('vendor_id') === (string) $v->id)>{{ $v->name }}</option>@endforeach</select></div>
            </div>
        </div>
        <div class="form-section party-highlight">
            <div class="row">
                <div class="col-md-3 form-group"><label>Party Name</label><input type="text" name="party_name" class="form-control form-control-sm" id="pur-party-name" value="{{ old('party_name') }}"></div>
                <div class="col-md-2 form-group"><label>City</label><input type="text" name="city" class="form-control form-control-sm" id="pur-city" value="{{ old('city') }}"></div>
                <div class="col-md-2 form-group"><label>District</label><input type="text" class="form-control form-control-sm" id="pur-district"></div>
                <div class="col-md-2 form-group"><label>State</label><input type="text" name="state" class="form-control form-control-sm" id="pur-state" value="{{ old('state') }}"></div>
                <div class="col-md-3 form-group"><label>GSTIN</label><input type="text" name="gstin" class="form-control form-control-sm" id="pur-gstin" value="{{ old('gstin') }}"></div>
            </div>
        </div>
        <div class="form-section">
            <div class="row">
                <div class="col-md-2 form-group"><label>GR No.</label><input type="text" name="gr_number" class="form-control form-control-sm" value="{{ old('gr_number') }}"></div>
                <div class="col-md-2 form-group gr-date-highlight"><label>GR Date</label><input type="date" name="gr_date" class="form-control form-control-sm" value="{{ old('gr_date', date('Y-m-d')) }}"></div>
                <div class="col-md-2 form-group"><label>Driver Name</label><input type="text" name="driver_name" class="form-control form-control-sm" value="{{ old('driver_name') }}"></div>
                <div class="col-md-2 form-group"><label>Vehicle</label><input type="text" name="vehicle_number" class="form-control form-control-sm" value="{{ old('vehicle_number') }}"></div>
                <div class="col-md-2 form-group"><label>Transport</label><input type="text" name="transport_name" class="form-control form-control-sm" value="{{ old('transport_name') }}"></div>
                <div class="col-md-2 form-group"><label>Place of Supply</label><input type="text" name="place_of_supply" class="form-control form-control-sm" value="{{ old('place_of_supply') }}"></div>
            </div>
        </div>
        <div class="form-section">
            <div class="row">
                <div class="col-md-4 form-group"><label>E-Way Bill No.</label><input type="text" name="eway_bill_no" class="form-control form-control-sm" value="{{ old('eway_bill_no') }}"></div>
                <div class="col-md-3 form-group"><label>App. Distance in KMs</label><input type="number" name="distance_km" class="form-control form-control-sm" step="0.01" value="{{ old('distance_km') }}"></div>
                <div class="col-md-3 form-group"><label>Reference / Bill No.</label><input type="text" name="reference" class="form-control form-control-sm" placeholder="Supplier bill no." value="{{ old('reference') }}"></div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-boxes mr-2" style="color:#667eea;"></i>Line Items</h5></div>
    <div class="card-body">
        <div class="form-section" style="background:rgba(102,126,234,0.05);">
            <div class="row align-items-end">
                <div class="col-md-3 form-group"><label>Product</label><select id="add-product" class="form-control form-control-sm"><option value="">-- Select --</option>@foreach($products as $pr)<option value="{{ $pr->id }}" data-name="{{ $pr->name }}" data-hsn="{{ $pr->hsn_code }}" data-unit="{{ $pr->unit?->symbol }}" data-rate="{{ $pr->purchase_rate }}" data-gst="{{ $pr->gst_percent }}">{{ $pr->name }}</option>@endforeach</select></div>
                <div class="col-md-1 form-group"><label>Qty</label><input type="number" id="add-qty" class="form-control form-control-sm" step="0.001" value="1"></div>
                <div class="col-md-1 form-group"><label>Unit</label><input type="text" id="add-unit" class="form-control form-control-sm" readonly></div>
                <div class="col-md-1 form-group"><label>HSN</label><input type="text" id="add-hsn" class="form-control form-control-sm" readonly></div>
                <div class="col-md-2 form-group"><label>Price</label><input type="number" id="add-rate" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>Tax %</label><input type="number" id="add-gst" class="form-control form-control-sm" step="0.01"></div>
                <div class="col-md-1 form-group"><label>&nbsp;</label><button type="button" class="btn btn-primary btn-sm btn-block" onclick="addRow()" style="font-weight:600;"><i class="fas fa-plus"></i> ADD</button></div>
            </div>
        </div>
        <div class="items-scroll mt-3">
            <table class="table table-hover items-table mb-0" id="items-table">
                <thead><tr><th>Description</th><th>Qty</th><th>Unit</th><th>HSN</th><th class="text-right">Price</th><th class="text-right">Amount</th><th>Tax%</th><th class="text-right">Tax</th><th class="text-right">Net Amt</th><th></th></tr></thead>
                <tbody></tbody>
                <tfoot>
                    <tr style="font-weight:700; background:rgba(102,126,234,0.08);">
                        <td colspan="10">Product Total: <span id="product-total-count">0</span> | Total Qty: <span id="product-total-qty">0.000</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div id="hidden-items"></div>
        <div class="d-flex flex-wrap align-items-center mt-3" style="gap:10px;">
            <span style="font-weight:600; color:var(--text-muted);">ITEMS: <strong id="total-entries" style="color:var(--text-primary);">0</strong></span>
            <span class="summary-pill" style="background:rgba(0,152,234,0.15); color:#0984e3;" id="total-taxable">Taxable: 0.00</span>
            <span class="summary-pill" style="background:rgba(255,118,117,0.15); color:#ff7675;" id="total-tax">GST: 0.00</span>
            <span class="summary-pill" style="background:rgba(102,126,234,0.2); color:#667eea;" id="total-net">Net: 0.00</span>
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
        <small class="text-muted d-block mt-2">Tip: click any added product row to edit inline.</small>
    </div>
</div>

<div class="d-flex mb-4" style="gap:12px;">
    <a href="{{ route('modules.transactions') }}#purchases" class="btn btn-secondary"><i class="fas fa-arrow-left mr-1"></i> BACK</a>
    <button type="submit" name="after_action" value="generate" class="btn btn-primary" style="padding:10px 22px; font-weight:600;"><i class="fas fa-file-alt mr-2"></i>SAVE &amp; GENERATE</button>
    <button type="submit" name="after_action" value="print" class="btn btn-info" style="padding:10px 22px; font-weight:600;"><i class="fas fa-print mr-2"></i>SAVE &amp; PRINT</button>
    <button type="button" class="btn btn-secondary" onclick="resetForm()"><i class="fas fa-redo mr-1"></i> RESET</button>
    <a href="{{ route('modules.transactions') }}#purchases" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> CANCEL</a>
</div>

</form>

<script>
var itemCounter = 0;
document.getElementById('pur-vendor').addEventListener('change', function(){ var o=this.options[this.selectedIndex]; document.getElementById('pur-party-name').value=o.dataset.name||''; document.getElementById('pur-city').value=o.dataset.city||''; document.getElementById('pur-district').value=o.dataset.district||''; document.getElementById('pur-state').value=o.dataset.state||''; document.getElementById('pur-gstin').value=o.dataset.gstin||''; });
document.getElementById('add-product').addEventListener('change', function(){ var o=this.options[this.selectedIndex]; document.getElementById('add-unit').value=o.dataset.unit||''; document.getElementById('add-hsn').value=o.dataset.hsn||''; document.getElementById('add-rate').value=o.dataset.rate||''; document.getElementById('add-gst').value=o.dataset.gst||''; });

function addRow() {
    var prod=document.getElementById('add-product'), qty=document.getElementById('add-qty'), unit=document.getElementById('add-unit'), hsn=document.getElementById('add-hsn'), rate=document.getElementById('add-rate'), gst=document.getElementById('add-gst');
    if(!prod.value||!qty.value||!rate.value){alert('Select product, enter qty and rate.');return;}
    var q=parseFloat(qty.value),r=parseFloat(rate.value),g=parseFloat(gst.value)||0, amount=Math.round(q*r*100)/100, tax=g>0?Math.round(amount*(g/100)*100)/100:0, net=amount+tax, idx=itemCounter++, pname=prod.options[prod.selectedIndex].text;
    var tbody=document.querySelector('#items-table tbody'), tr=document.createElement('tr'); tr.setAttribute('data-idx',idx); tr.style.cursor='pointer'; tr.onclick=function(e){ if(e.target.closest('button') || tr.getAttribute('data-editing')==='1') return; enableInlineEdit(tr, idx); };
    tr.innerHTML='<td style="font-weight:500;">'+pname+'</td><td>'+q+'</td><td>'+unit.value+'</td><td>'+hsn.value+'</td><td class="text-right">'+r.toFixed(2)+'</td><td class="text-right">'+amount.toFixed(2)+'</td><td>'+g+'%</td><td class="text-right" style="color:#ff7675;">'+tax.toFixed(2)+'</td><td class="text-right" style="font-weight:700; color:#a4b4f4;">'+net.toFixed(2)+'</td><td><button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button></td>';
    tbody.appendChild(tr);
    var hc=document.getElementById('hidden-items'), hDiv=document.createElement('div'); hDiv.setAttribute('data-idx',idx);
    hDiv.innerHTML='<input type="hidden" name="items['+idx+'][product_id]" value="'+prod.value+'"><input type="hidden" name="items['+idx+'][quantity]" value="'+q+'"><input type="hidden" name="items['+idx+'][rate]" value="'+r+'"><input type="hidden" name="items['+idx+'][gst_percent]" value="'+g+'">';
    hc.appendChild(hDiv);
    prod.selectedIndex=0; qty.value=1; unit.value=''; hsn.value=''; rate.value=''; gst.value='';
    updateTotals();
}
function removeRow(btn,idx){ btn.closest('tr').remove(); var h=document.querySelector('#hidden-items div[data-idx="'+idx+'"]'); if(h)h.remove(); updateTotals(); }
function updateTotals(){ var rows=document.querySelectorAll('#items-table tbody tr'), count=rows.length, tA=0, tT=0, tN=0, tQ=0, slabTotals={}; rows.forEach(function(tr){ var c=tr.querySelectorAll('td'), qty=parseFloat(c[1].textContent)||0, taxable=parseFloat(c[5].textContent)||0, gstAmt=parseFloat(c[7].textContent)||0, gross=parseFloat(c[8].textContent)||0, gstPct=parseFloat(String(c[6].textContent||'').replace('%',''))||0, slabKey=(gstPct%1===0?gstPct.toFixed(0):gstPct.toFixed(2)); if(!slabTotals[slabKey]) slabTotals[slabKey]={taxable:0,cgst:0,sgst:0,igst:0,tax:0,gross:0}; var half=gstAmt/2; slabTotals[slabKey].taxable+=taxable; slabTotals[slabKey].cgst+=half; slabTotals[slabKey].sgst+=half; slabTotals[slabKey].tax+=gstAmt; slabTotals[slabKey].gross+=gross; tQ+=qty; tA+=taxable; tT+=gstAmt; tN+=gross; }); document.getElementById('total-entries').textContent=count; document.getElementById('total-taxable').textContent='Taxable: '+tA.toFixed(2); document.getElementById('total-tax').textContent='GST: '+tT.toFixed(2); document.getElementById('total-net').textContent='Net: '+tN.toFixed(2); document.getElementById('product-total-count').textContent=count; document.getElementById('product-total-qty').textContent=tQ.toFixed(3); renderTaxSlabTable(slabTotals); }
function renderTaxSlabTable(slabTotals){ var tbody=document.querySelector('#tax-slab-table tbody'); var keys=Object.keys(slabTotals).sort(function(a,b){ return parseFloat(a)-parseFloat(b); }), g={taxable:0,cgst:0,sgst:0,igst:0,tax:0,gross:0}; tbody.innerHTML=''; keys.forEach(function(k){ var r=slabTotals[k]; g.taxable+=r.taxable; g.cgst+=r.cgst; g.sgst+=r.sgst; g.igst+=r.igst; g.tax+=r.tax; g.gross+=r.gross; var tr=document.createElement('tr'); tr.innerHTML='<td><strong>'+k+'%</strong></td><td class="text-right">'+r.taxable.toFixed(2)+'</td><td class="text-right">'+r.cgst.toFixed(2)+'</td><td class="text-right">'+r.sgst.toFixed(2)+'</td><td class="text-right">'+r.igst.toFixed(2)+'</td><td class="text-right">'+r.tax.toFixed(2)+'</td><td class="text-right">'+r.gross.toFixed(2)+'</td>'; tbody.appendChild(tr); }); document.getElementById('slab-grand-taxable').textContent=g.taxable.toFixed(2); document.getElementById('slab-grand-cgst').textContent=g.cgst.toFixed(2); document.getElementById('slab-grand-sgst').textContent=g.sgst.toFixed(2); document.getElementById('slab-grand-igst').textContent=g.igst.toFixed(2); document.getElementById('slab-grand-tax').textContent=g.tax.toFixed(2); document.getElementById('slab-grand-gross').textContent=g.gross.toFixed(2); }
function enableInlineEdit(tr, idx){ tr.setAttribute('data-editing','1'); var c=tr.querySelectorAll('td'), qty=parseFloat(c[1].textContent)||0, rate=parseFloat(c[4].textContent)||0, gst=parseFloat(String(c[6].textContent||'').replace('%',''))||0; c[1].innerHTML='<input type="number" class="form-control form-control-sm" id="edit-qty-'+idx+'" value="'+qty+'" step="0.001" min="0.001">'; c[4].innerHTML='<input type="number" class="form-control form-control-sm" id="edit-rate-'+idx+'" value="'+rate+'" step="0.01" min="0">'; c[6].innerHTML='<input type="number" class="form-control form-control-sm" id="edit-gst-'+idx+'" value="'+gst+'" step="0.01" min="0" max="100">'; c[9].innerHTML='<button type="button" class="btn btn-success btn-sm py-0 px-2 mr-1" onclick="saveInlineEdit('+idx+')"><i class="fas fa-check"></i></button><button type="button" class="btn btn-secondary btn-sm py-0 px-2" onclick="cancelInlineEdit('+idx+')"><i class="fas fa-times"></i></button>'; }
function saveInlineEdit(idx){ var tr=document.querySelector('#items-table tbody tr[data-idx="'+idx+'"]'); if(!tr) return; var qty=parseFloat(document.getElementById('edit-qty-'+idx).value)||0, rate=parseFloat(document.getElementById('edit-rate-'+idx).value)||0, gst=parseFloat(document.getElementById('edit-gst-'+idx).value)||0; if(qty<=0||rate<0||gst<0){ alert('Enter valid values.'); return; } var amount=Math.round(qty*rate*100)/100, tax=gst>0?Math.round(amount*(gst/100)*100)/100:0, net=amount+tax, c=tr.querySelectorAll('td'); c[1].textContent=qty.toString(); c[4].textContent=rate.toFixed(2); c[5].textContent=amount.toFixed(2); c[6].textContent=gst+'%'; c[7].textContent=tax.toFixed(2); c[8].textContent=net.toFixed(2); c[9].innerHTML='<button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button>'; var h=document.querySelector('#hidden-items div[data-idx="'+idx+'"]'); if(h){ h.querySelector('input[name="items['+idx+'][quantity]"]').value=qty; h.querySelector('input[name="items['+idx+'][rate]"]').value=rate; h.querySelector('input[name="items['+idx+'][gst_percent]"]').value=gst; } tr.setAttribute('data-editing','0'); updateTotals(); }
function cancelInlineEdit(idx){ var tr=document.querySelector('#items-table tbody tr[data-idx="'+idx+'"]'); if(!tr) return; tr.setAttribute('data-editing','0'); var h=document.querySelector('#hidden-items div[data-idx="'+idx+'"]'); if(!h) return; var qty=parseFloat(h.querySelector('input[name="items['+idx+'][quantity]"]').value)||0, rate=parseFloat(h.querySelector('input[name="items['+idx+'][rate]"]').value)||0, gst=parseFloat(h.querySelector('input[name="items['+idx+'][gst_percent]"]').value)||0, amount=Math.round(qty*rate*100)/100, tax=gst>0?Math.round(amount*(gst/100)*100)/100:0, net=amount+tax, c=tr.querySelectorAll('td'); c[1].textContent=qty.toString(); c[4].textContent=rate.toFixed(2); c[5].textContent=amount.toFixed(2); c[6].textContent=gst+'%'; c[7].textContent=tax.toFixed(2); c[8].textContent=net.toFixed(2); c[9].innerHTML='<button type="button" class="btn btn-danger btn-sm py-0 px-1" onclick="removeRow(this,'+idx+')"><i class="fas fa-trash" style="font-size:0.7rem;"></i></button>'; }
function resetForm(){ document.getElementById('purchaseForm').reset(); document.querySelector('#items-table tbody').innerHTML=''; document.getElementById('hidden-items').innerHTML=''; itemCounter=0; updateTotals(); }
document.getElementById('purchaseForm').addEventListener('submit', function(e){ if(document.getElementById('hidden-items').children.length===0){ e.preventDefault(); alert('Please add at least one item before saving.'); } });

(function restoreOldItems(){ var oldItems=@json(old('items', [])); if(!Array.isArray(oldItems) || oldItems.length===0) return; oldItems.forEach(function(item){ if(!item || !item.product_id) return; var prod=document.getElementById('add-product'); prod.value=String(item.product_id); var o=prod.options[prod.selectedIndex]; document.getElementById('add-unit').value=(o&&o.dataset.unit)||''; document.getElementById('add-hsn').value=(o&&o.dataset.hsn)||''; document.getElementById('add-qty').value=item.quantity||1; document.getElementById('add-rate').value=item.rate||''; document.getElementById('add-gst').value=item.gst_percent ?? ((o&&o.dataset.gst)||0); addRow(); }); })();
</script>
@endsection
