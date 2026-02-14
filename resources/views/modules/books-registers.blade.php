@extends('layouts.app')

@section('title', 'Books & Registers')
@section('header', 'Books & Registers')

@section('content')

{{-- Summary Cards --}}
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #667eea !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Purchase Book</p>
                        <h4 class="mb-0" style="font-weight:700; color:#a4b4f4;">{{ number_format($purchaseSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $purchaseSummary['count'] }} entries</small>
                    </div>
                    <div style="width:50px; height:50px; border-radius:12px; background:rgba(102,126,234,0.15); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-shopping-cart" style="color:#667eea; font-size:1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #00b894 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Sales Book</p>
                        <h4 class="mb-0" style="font-weight:700; color:#55efc4;">{{ number_format($salesSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $salesSummary['count'] }} invoices</small>
                    </div>
                    <div style="width:50px; height:50px; border-radius:12px; background:rgba(0,184,148,0.15); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-file-invoice" style="color:#00b894; font-size:1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #f39c12 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Purchase Returns</p>
                        <h4 class="mb-0" style="font-weight:700; color:#fdcb6e;">{{ number_format($purchaseReturnSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $purchaseReturnSummary['count'] }} returns</small>
                    </div>
                    <div style="width:50px; height:50px; border-radius:12px; background:rgba(243,156,18,0.15); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-undo" style="color:#f39c12; font-size:1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-12 mb-3">
        <div class="card mb-0" style="border-left: 4px solid #e84393 !important;">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1" style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.5px;">Sales Returns</p>
                        <h4 class="mb-0" style="font-weight:700; color:#fd79a8;">{{ number_format($salesReturnSummary['total'], 2) }}</h4>
                        <small class="text-muted">{{ $salesReturnSummary['count'] }} returns</small>
                    </div>
                    <div style="width:50px; height:50px; border-radius:12px; background:rgba(232,67,147,0.15); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-exchange-alt" style="color:#e84393; font-size:1.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Date Filter --}}
<div class="card mb-4" style="background: rgba(255,255,255,0.03) !important;">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('modules.books-registers') }}" class="d-flex flex-wrap align-items-end" style="gap:12px;">
            <div>
                <label class="text-muted mb-1" style="font-size:0.75rem;">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate }}" style="width:160px;">
            </div>
            <div>
                <label class="text-muted mb-1" style="font-size:0.75rem;">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate }}" style="width:160px;">
            </div>
            <div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter mr-1"></i> Filter</button>
            </div>
            @if($fromDate || $toDate)
            <div>
                <a href="{{ route('modules.books-registers') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times mr-1"></i> Clear</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabs --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; border: 0 !important;">
    <div class="card-body d-flex flex-wrap" style="padding:10px; gap:8px;">
        @php
            $tabs = [
                'purchase' => ['fas fa-shopping-cart', 'Purchase Book'],
                'sales' => ['fas fa-file-invoice', 'Sales Book'],
                'purchase-returns' => ['fas fa-undo', 'Purchase Returns'],
                'sales-returns' => ['fas fa-exchange-alt', 'Sales Returns'],
            ];
        @endphp
        @foreach($tabs as $tabKey => $tabInfo)
        <button type="button" class="btn book-tab-btn" id="tab-btn-{{ $tabKey }}" data-tab="{{ $tabKey }}"
                style="{{ $loop->first ? 'background:#fff !important; color:#1e3c72 !important; font-weight:600;' : 'background:rgba(255,255,255,0.12) !important; color:rgba(255,255,255,0.85) !important; border:0;' }} padding:10px 18px; border-radius:8px; font-size:0.9rem;">
            <i class="{{ $tabInfo[0] }} mr-1"></i> {{ $tabInfo[1] }}
        </button>
        @endforeach
    </div>
</div>

{{-- ===== PURCHASE BOOK ===== --}}
<div id="tab-panel-purchase" class="book-tab-panel">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-shopping-cart mr-2" style="color:#667eea;"></i>Purchase Book</h5>
            <span class="badge badge-info" style="font-size:0.85rem; padding:6px 14px;">{{ $purchases->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doc No.</th>
                            <th>Date</th>
                            <th>Vendor</th>
                            <th>Reference</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">GST</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($purchases as $i => $p)
                        <tr>
                            <td class="text-muted">{{ $purchases->firstItem() + $i }}</td>
                            <td><strong>{{ $p->doc_number }}</strong></td>
                            <td>{{ $p->purchase_date ? $p->purchase_date->format('d M Y') : '—' }}</td>
                            <td>
                                @if($p->vendor)
                                    <span style="font-weight:500;">{{ $p->vendor->name }}</span>
                                    @if($p->vendor->city)<br><small class="text-muted">{{ $p->vendor->city }}</small>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $p->reference ?: '—' }}</td>
                            <td class="text-right">{{ number_format($p->subtotal, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($p->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#a4b4f4;">{{ number_format($p->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-shopping-cart fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                                No purchases found
                                <br><small>Purchases will appear here once they are recorded</small>
                            </td>
                        </tr>
                    @endforelse
                    @if($purchases->count() > 0)
                        <tr style="background: rgba(102,126,234,0.08) !important;">
                            <td colspan="5" class="text-right" style="font-weight:700;">Page Total</td>
                            <td class="text-right" style="font-weight:700;">{{ number_format($purchases->sum('subtotal'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($purchases->sum('gst_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#a4b4f4;">{{ number_format($purchases->sum('total'), 2) }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
            <div class="p-3 d-flex justify-content-center">{{ $purchases->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ===== SALES BOOK ===== --}}
<div id="tab-panel-sales" class="book-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-file-invoice mr-2" style="color:#00b894;"></i>Sales Book</h5>
            <span class="badge badge-success" style="font-size:0.85rem; padding:6px 14px;">{{ $sales->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th class="text-right">Taxable Amt</th>
                            <th class="text-right">GST</th>
                            <th class="text-right">Net Amount</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($sales as $i => $s)
                        <tr>
                            <td class="text-muted">{{ $sales->firstItem() + $i }}</td>
                            <td><strong>{{ $s->invoice_number }}</strong></td>
                            <td>{{ $s->invoice_date ? $s->invoice_date->format('d M Y') : '—' }}</td>
                            <td>
                                @if($s->customer)
                                    <span style="font-weight:500;">{{ $s->customer->name }}</span>
                                    @if($s->customer->city)<br><small class="text-muted">{{ $s->customer->city }}</small>@endif
                                @elseif($s->party_name)
                                    <span style="font-weight:500;">{{ $s->party_name }}</span>
                                    @if($s->city)<br><small class="text-muted">{{ $s->city }}</small>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">{{ number_format($s->taxable_amount, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($s->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#55efc4;">{{ number_format($s->net_amount, 2) }}</td>
                            <td class="text-right">
                                @if($s->balance_amount > 0)
                                    <span style="color:#ff7675; font-weight:600;">{{ number_format($s->balance_amount, 2) }}</span>
                                @else
                                    <span class="badge badge-success" style="font-size:0.75rem;">Paid</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-file-invoice fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                                No sales found
                                <br><small>Sales invoices will appear here once they are created</small>
                            </td>
                        </tr>
                    @endforelse
                    @if($sales->count() > 0)
                        <tr style="background: rgba(0,184,148,0.08) !important;">
                            <td colspan="4" class="text-right" style="font-weight:700;">Page Total</td>
                            <td class="text-right" style="font-weight:700;">{{ number_format($sales->sum('taxable_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($sales->sum('gst_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#55efc4;">{{ number_format($sales->sum('net_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700;">{{ number_format($sales->sum('balance_amount'), 2) }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages())
            <div class="p-3 d-flex justify-content-center">{{ $sales->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ===== PURCHASE RETURNS ===== --}}
<div id="tab-panel-purchase-returns" class="book-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-undo mr-2" style="color:#f39c12;"></i>Purchase Returns</h5>
            <span class="badge badge-warning" style="font-size:0.85rem; padding:6px 14px;">{{ $purchaseReturns->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doc No.</th>
                            <th>Return Date</th>
                            <th>Vendor</th>
                            <th>Reference</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">GST</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($purchaseReturns as $i => $pr)
                        <tr>
                            <td class="text-muted">{{ $purchaseReturns->firstItem() + $i }}</td>
                            <td><strong>{{ $pr->doc_number }}</strong></td>
                            <td>{{ $pr->return_date ? $pr->return_date->format('d M Y') : '—' }}</td>
                            <td>
                                @if($pr->vendor)
                                    <span style="font-weight:500;">{{ $pr->vendor->name }}</span>
                                    @if($pr->vendor->city)<br><small class="text-muted">{{ $pr->vendor->city }}</small>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $pr->reference ?: '—' }}</td>
                            <td class="text-right">{{ number_format($pr->subtotal, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($pr->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($pr->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-undo fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                                No purchase returns found
                                <br><small>Purchase returns will appear here once they are recorded</small>
                            </td>
                        </tr>
                    @endforelse
                    @if($purchaseReturns->count() > 0)
                        <tr style="background: rgba(243,156,18,0.08) !important;">
                            <td colspan="5" class="text-right" style="font-weight:700;">Page Total</td>
                            <td class="text-right" style="font-weight:700;">{{ number_format($purchaseReturns->sum('subtotal'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($purchaseReturns->sum('gst_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($purchaseReturns->sum('total'), 2) }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if($purchaseReturns->hasPages())
            <div class="p-3 d-flex justify-content-center">{{ $purchaseReturns->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

{{-- ===== SALES RETURNS ===== --}}
<div id="tab-panel-sales-returns" class="book-tab-panel d-none">
    <div class="card mb-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0" style="font-weight:600;"><i class="fas fa-exchange-alt mr-2" style="color:#e84393;"></i>Sales Returns</h5>
            <span class="badge badge-danger" style="font-size:0.85rem; padding:6px 14px;">{{ $salesReturns->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doc No.</th>
                            <th>Return Date</th>
                            <th>Customer</th>
                            <th>Reference</th>
                            <th class="text-right">Subtotal</th>
                            <th class="text-right">GST</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($salesReturns as $i => $sr)
                        <tr>
                            <td class="text-muted">{{ $salesReturns->firstItem() + $i }}</td>
                            <td><strong>{{ $sr->doc_number }}</strong></td>
                            <td>{{ $sr->return_date ? $sr->return_date->format('d M Y') : '—' }}</td>
                            <td>
                                @if($sr->customer)
                                    <span style="font-weight:500;">{{ $sr->customer->name }}</span>
                                    @if($sr->customer->city)<br><small class="text-muted">{{ $sr->customer->city }}</small>@endif
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted">{{ $sr->reference ?: '—' }}</td>
                            <td class="text-right">{{ number_format($sr->subtotal, 2) }}</td>
                            <td class="text-right" style="color:#fdcb6e;">{{ number_format($sr->gst_amount, 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fd79a8;">{{ number_format($sr->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-exchange-alt fa-2x mb-3 d-block" style="opacity:0.3;"></i>
                                No sales returns found
                                <br><small>Sales returns will appear here once they are recorded</small>
                            </td>
                        </tr>
                    @endforelse
                    @if($salesReturns->count() > 0)
                        <tr style="background: rgba(232,67,147,0.08) !important;">
                            <td colspan="5" class="text-right" style="font-weight:700;">Page Total</td>
                            <td class="text-right" style="font-weight:700;">{{ number_format($salesReturns->sum('subtotal'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fdcb6e;">{{ number_format($salesReturns->sum('gst_amount'), 2) }}</td>
                            <td class="text-right" style="font-weight:700; color:#fd79a8;">{{ number_format($salesReturns->sum('total'), 2) }}</td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
            @if($salesReturns->hasPages())
            <div class="p-3 d-flex justify-content-center">{{ $salesReturns->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
// Tab switching
(function(){
    var tabs = ['purchase','sales','purchase-returns','sales-returns'];
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
            if(history.replaceState) history.replaceState(null,'','{{ route("modules.books-registers") }}'+window.location.search+'#'+id);
        });
    });
    // Activate tab from hash
    var h=(location.hash||'').replace('#','');
    if(tabs.indexOf(h)!==-1){var el=document.getElementById('tab-btn-'+h); if(el)el.click();}
})();
</script>
@endsection
