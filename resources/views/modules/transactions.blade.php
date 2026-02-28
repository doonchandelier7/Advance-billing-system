@extends('layouts.app')

@section('title', 'Transactions')
@section('header', 'Transactions')

@section('content')

{{-- Toast --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('modules.transactions') }}" class="d-flex flex-wrap align-items-end" style="gap:10px;">
            <div>
                <label class="mb-1 text-muted" style="font-size:0.75rem;">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm">
            </div>
            <div>
                <label class="mb-1 text-muted" style="font-size:0.75rem;">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i> Apply Filter</button>
            <a href="{{ route('modules.transactions') }}" class="btn btn-secondary btn-sm"><i class="fas fa-undo mr-1"></i> Reset</a>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #00b894 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Sales</p>
                        <h4 class="mb-0" style="font-weight:700; color:#55efc4;">{{ number_format($salesSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $salesSummary['count'] }} invoices</small>
                    </div>
                    <div style="width:45px; height:45px; border-radius:10px; background:rgba(0,184,148,0.15); display:flex; align-items:center; justify-content:center;"><i class="fas fa-file-invoice-dollar" style="color:#00b894;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #667eea !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Purchases</p>
                        <h4 class="mb-0" style="font-weight:700; color:#a4b4f4;">{{ number_format($purchaseSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $purchaseSummary['count'] }} entries</small>
                    </div>
                    <div style="width:45px; height:45px; border-radius:10px; background:rgba(102,126,234,0.15); display:flex; align-items:center; justify-content:center;"><i class="fas fa-shopping-cart" style="color:#667eea;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #f39c12 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Purchase Returns</p>
                        <h4 class="mb-0" style="font-weight:700; color:#fdcb6e;">{{ number_format($purchaseReturnSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $purchaseReturnSummary['count'] }} returns</small>
                    </div>
                    <div style="width:45px; height:45px; border-radius:10px; background:rgba(243,156,18,0.15); display:flex; align-items:center; justify-content:center;"><i class="fas fa-undo" style="color:#f39c12;"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #e84393 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.5px;">Sales Returns</p>
                        <h4 class="mb-0" style="font-weight:700; color:#fd79a8;">{{ number_format($salesReturnSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $salesReturnSummary['count'] }} returns</small>
                    </div>
                    <div style="width:45px; height:45px; border-radius:10px; background:rgba(232,67,147,0.15); display:flex; align-items:center; justify-content:center;"><i class="fas fa-exchange-alt" style="color:#e84393;"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; border: 0 !important;">
    <div class="card-body d-flex flex-wrap" style="padding:10px; gap:8px;">
        @php $tabs = ['sales'=>['fas fa-file-invoice-dollar','Sales'],'purchases'=>['fas fa-shopping-cart','Purchases'],'purchase-returns'=>['fas fa-undo','Purchase Returns'],'sales-returns'=>['fas fa-exchange-alt','Sales Returns']]; @endphp
        @foreach($tabs as $tabKey => $tabInfo)
        <button type="button" class="btn txn-tab-btn" id="tab-btn-{{ $tabKey }}" data-tab="{{ $tabKey }}"
                style="{{ $loop->first ? 'background:#fff !important; color:#1e3c72 !important; font-weight:600;' : 'background:rgba(255,255,255,0.12) !important; color:rgba(255,255,255,0.85) !important; border:0;' }} padding:10px 18px; border-radius:8px; font-size:0.9rem;">
            <i class="{{ $tabInfo[0] }} mr-1"></i> {{ $tabInfo[1] }}
        </button>
        @endforeach
    </div>
</div>

{{-- ============================================================ --}}
{{-- SALES TAB                                                      --}}
{{-- ============================================================ --}}
<div id="tab-panel-sales" class="txn-tab-panel">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-file-invoice-dollar mr-2" style="color:#00b894;"></i>Sales Invoices</h5>
            <a href="{{ route('modules.transactions.sales.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> New Sale</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Invoice No.</th><th>Date</th><th>Customer</th><th>Mode</th><th class="text-right">Taxable</th><th class="text-right">GST</th><th class="text-right">Net Amount</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($sales as $i => $s)
                        <tr>
                            <td class="text-muted">{{ $sales->firstItem() + $i }}</td>
                            <td><strong>{{ $s->invoice_number }}</strong></td>
                            <td>{{ $s->invoice_date ? $s->invoice_date->format('d M Y') : '—' }}</td>
                            <td>@if($s->customer)<span style="font-weight:500;">{{ $s->customer->name }}</span>@elseif($s->party_name){{ $s->party_name }}@else <span class="text-muted">Walk-in</span>@endif</td>
                            <td><span class="badge badge-{{ ($s->payment_mode ?? 'CASH') === 'CASH' ? 'success' : 'info' }}">{{ $s->payment_mode ?? 'CASH' }}</span></td>
                            <td class="text-right">{{ number_format($s->taxable_amount, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($s->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#55efc4;">{{ number_format($s->net_amount, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('invoices.generate', $s) }}" class="btn btn-sm btn-outline-primary">Generate</a>
                                <a href="{{ route('invoices.print', $s) }}" class="btn btn-sm btn-outline-success" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-file-invoice-dollar fa-2x mb-2 d-block" style="opacity:0.3;"></i>No sales invoices yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages()) <div class="p-3 d-flex justify-content-center">{{ $sales->appends(request()->query())->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PURCHASES TAB                                                  --}}
{{-- ============================================================ --}}
<div id="tab-panel-purchases" class="txn-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-shopping-cart mr-2" style="color:#667eea;"></i>Purchases</h5>
            <a href="{{ route('modules.transactions.purchases.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus mr-1"></i> New Purchase</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Doc No.</th><th>Date</th><th>Vendor</th><th>Mode</th><th class="text-right">Subtotal</th><th class="text-right">GST</th><th class="text-right">Total</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($purchases as $i => $p)
                        <tr>
                            <td class="text-muted">{{ $purchases->firstItem() + $i }}</td>
                            <td><strong>{{ $p->doc_number }}</strong></td>
                            <td>{{ $p->purchase_date ? $p->purchase_date->format('d M Y') : '—' }}</td>
                            <td>@if($p->vendor)<span style="font-weight:500;">{{ $p->vendor->name }}</span>@else <span class="text-muted">—</span>@endif</td>
                            <td><span class="badge badge-{{ ($p->payment_mode ?? 'CASH') === 'CASH' ? 'success' : 'info' }}">{{ $p->payment_mode ?? 'CASH' }}</span></td>
                            <td class="text-right">{{ number_format($p->subtotal, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($p->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#a4b4f4;">{{ number_format($p->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('modules.transactions.purchases.generate', $p) }}" class="btn btn-sm btn-outline-primary">Generate</a>
                                <a href="{{ route('modules.transactions.purchases.print', $p) }}" class="btn btn-sm btn-outline-success" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-2x mb-2 d-block" style="opacity:0.3;"></i>No purchases yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages()) <div class="p-3 d-flex justify-content-center">{{ $purchases->appends(request()->query())->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PURCHASE RETURNS TAB                                           --}}
{{-- ============================================================ --}}
<div id="tab-panel-purchase-returns" class="txn-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-undo mr-2" style="color:#f39c12;"></i>Purchase Returns</h5>
            <a href="{{ route('modules.transactions.purchase-returns.create') }}" class="btn btn-sm" style="background:linear-gradient(135deg,#f39c12,#e67e22) !important; color:#fff !important; border:0; border-radius:8px;"><i class="fas fa-plus mr-1"></i> New Return</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Doc No.</th><th>Date</th><th>Vendor</th><th>Mode</th><th class="text-right">Total</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($purchaseReturns as $i => $pr)
                        <tr>
                            <td class="text-muted">{{ $purchaseReturns->firstItem() + $i }}</td>
                            <td><strong>{{ $pr->doc_number }}</strong></td>
                            <td>{{ $pr->return_date ? $pr->return_date->format('d M Y') : '—' }}</td>
                            <td>@if($pr->vendor)<span style="font-weight:500;">{{ $pr->vendor->name }}</span>@else —@endif</td>
                            <td><span class="badge badge-{{ ($pr->payment_mode ?? 'CASH') === 'CASH' ? 'success' : 'info' }}">{{ $pr->payment_mode ?? 'CASH' }}</span></td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($pr->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('modules.transactions.purchase-returns.generate', $pr) }}" class="btn btn-sm btn-outline-primary">Generate</a>
                                <a href="{{ route('modules.transactions.purchase-returns.print', $pr) }}" class="btn btn-sm btn-outline-success" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-undo fa-2x mb-2 d-block" style="opacity:0.3;"></i>No purchase returns yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchaseReturns->hasPages()) <div class="p-3 d-flex justify-content-center">{{ $purchaseReturns->appends(request()->query())->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- SALES RETURNS TAB                                              --}}
{{-- ============================================================ --}}
<div id="tab-panel-sales-returns" class="txn-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-exchange-alt mr-2" style="color:#e84393;"></i>Sales Returns</h5>
            <a href="{{ route('modules.transactions.sales-returns.create') }}" class="btn btn-sm" style="background:linear-gradient(135deg,#e84393,#fd79a8) !important; color:#fff !important; border:0; border-radius:8px;"><i class="fas fa-plus mr-1"></i> New Return</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>#</th><th>Doc No.</th><th>Date</th><th>Customer</th><th>Mode</th><th class="text-right">Total</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @forelse($salesReturns as $i => $sr)
                        <tr>
                            <td class="text-muted">{{ $salesReturns->firstItem() + $i }}</td>
                            <td><strong>{{ $sr->doc_number }}</strong></td>
                            <td>{{ $sr->return_date ? $sr->return_date->format('d M Y') : '—' }}</td>
                            <td>@if($sr->customer)<span style="font-weight:500;">{{ $sr->customer->name }}</span>@else —@endif</td>
                            <td><span class="badge badge-{{ ($sr->payment_mode ?? 'CASH') === 'CASH' ? 'success' : 'info' }}">{{ $sr->payment_mode ?? 'CASH' }}</span></td>
                            <td class="text-right" style="font-weight:700; color:#fd79a8;">{{ number_format($sr->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('modules.transactions.sales-returns.generate', $sr) }}" class="btn btn-sm btn-outline-primary">Generate</a>
                                <a href="{{ route('modules.transactions.sales-returns.print', $sr) }}" class="btn btn-sm btn-outline-success" target="_blank">Print</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4"><i class="fas fa-exchange-alt fa-2x mb-2 d-block" style="opacity:0.3;"></i>No sales returns yet</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($salesReturns->hasPages()) <div class="p-3 d-flex justify-content-center">{{ $salesReturns->appends(request()->query())->links() }}</div> @endif
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- TAB SWITCHING JAVASCRIPT                                       --}}
{{-- ============================================================ --}}
<script>
(function(){
    var tabs = ['sales','purchases','purchase-returns','sales-returns'];
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
            if(history.replaceState) history.replaceState(null,'','{{ route("modules.transactions") }}'+window.location.search+'#'+id);
        });
    });
    var h=(location.hash||'').replace('#','');
    if(tabs.indexOf(h)!==-1){var el=document.getElementById('tab-btn-'+h); if(el)el.click();}
})();
</script>
@endsection
