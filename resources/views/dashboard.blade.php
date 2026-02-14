@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
{{-- Welcome Banner --}}
<div class="row">
    <div class="col-12">
        <div class="card mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; border: 0 !important;">
            <div class="card-body" style="padding: 28px;">
                <h4 style="font-weight: 700; margin-bottom: 6px; color: #fff;">Welcome back, {{ auth()->user()->name }}!</h4>
                <p style="color: rgba(255,255,255,0.8); margin: 0; font-size: 0.95rem;">Manage your billing, accounting, and reports all in one place.</p>
            </div>
        </div>
    </div>
</div>

{{-- Stats Row --}}
<div class="row">
    @php
        $stats = [
            ['label' => 'Products',   'count' => \App\Models\Product::count(),  'icon' => 'fas fa-cube',     'color' => '#0984e3', 'bg' => 'rgba(9,132,227,0.15)'],
            ['label' => 'Customers',  'count' => \App\Models\Customer::count(), 'icon' => 'fas fa-users',    'color' => '#00b894', 'bg' => 'rgba(0,184,148,0.15)'],
            ['label' => 'Vendors',    'count' => \App\Models\Vendor::count(),   'icon' => 'fas fa-building', 'color' => '#f39c12', 'bg' => 'rgba(243,156,18,0.15)'],
            ['label' => 'Categories', 'count' => \App\Models\Category::count(), 'icon' => 'fas fa-tags',     'color' => '#e84393', 'bg' => 'rgba(232,67,147,0.15)'],
        ];
    @endphp
    @foreach($stats as $s)
    <div class="col-lg-3 col-6">
        <div class="card mb-4">
            <div class="card-body d-flex align-items-center" style="padding: 20px;">
                <div style="width:52px; height:52px; border-radius:12px; background:{{ $s['bg'] }}; display:flex; align-items:center; justify-content:center; margin-right:16px; flex-shrink:0;">
                    <i class="{{ $s['icon'] }}" style="font-size:1.3rem; color:{{ $s['color'] }};"></i>
                </div>
                <div>
                    <div class="stat-count" style="font-size:1.6rem; font-weight:700; color:var(--text-primary); line-height:1;">{{ $s['count'] }}</div>
                    <div class="stat-label" style="color:var(--text-muted); font-size:0.85rem; margin-top:4px;">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Modules + Quick Actions --}}
<div class="row">
    {{-- Modules --}}
    <div class="col-lg-9 col-12">
        <h5 style="color:var(--text-muted); font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px;">
            <i class="fas fa-th-large mr-2"></i>Modules
        </h5>
        <div class="row">
            @php
                $modules = [
                    ['route' => 'modules.master-setup',    'icon' => 'fas fa-database',  'title' => 'Master Setup',      'desc' => 'Categories, units, products, customers & vendors', 'gradient' => 'linear-gradient(135deg, #667eea, #764ba2)'],
                    ['route' => 'modules.transactions',    'icon' => 'fas fa-receipt',   'title' => 'Transactions',      'desc' => 'Sales, purchases, receipts & payments',            'gradient' => 'linear-gradient(135deg, #00b894, #00cec9)'],
                    ['route' => 'modules.books-registers', 'icon' => 'fas fa-book-open', 'title' => 'Books & Registers', 'desc' => 'Purchase book, sales book & returns',              'gradient' => 'linear-gradient(135deg, #fdcb6e, #f39c12)'],
                    ['route' => 'modules.accounting',      'icon' => 'fas fa-coins',     'title' => 'Accounting',        'desc' => 'Chart of accounts & financial management',         'gradient' => 'linear-gradient(135deg, #fd79a8, #e84393)'],
                    ['route' => 'modules.reports',         'icon' => 'fas fa-chart-pie', 'title' => 'Reports',           'desc' => 'Day book, ledger, trial balance, P&L',             'gradient' => 'linear-gradient(135deg, #74b9ff, #0984e3)'],
                    ['route' => 'ai-invoice.index',        'icon' => 'fas fa-magic',     'title' => 'AI Invoice Scan',   'desc' => 'Scan invoices with AI for auto-entry',             'gradient' => 'linear-gradient(135deg, #a29bfe, #6c5ce7)'],
                ];
            @endphp
            @foreach($modules as $m)
            <div class="col-lg-4 col-md-6 col-12">
                <a href="{{ route($m['route']) }}" style="text-decoration:none;">
                    <div class="card mb-4" style="cursor:pointer; transition: transform 0.2s, box-shadow 0.2s;"
                         onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,0.3)';"
                         onmouseout="this.style.transform='none';this.style.boxShadow='none';">
                        <div class="card-body" style="padding:24px;">
                            <div style="width:46px; height:46px; border-radius:12px; background:{{ $m['gradient'] }}; display:flex; align-items:center; justify-content:center; margin-bottom:16px;">
                                <i class="{{ $m['icon'] }}" style="color:#fff; font-size:1.2rem;"></i>
                            </div>
                            <h5 style="font-weight:600; font-size:1rem; margin-bottom:6px; color:var(--text-primary);">{{ $m['title'] }}</h5>
                            <p style="color:var(--text-muted); font-size:0.82rem; margin:0; line-height:1.5;">{{ $m['desc'] }}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center" style="padding:12px 24px;">
                            <span style="color:#667eea; font-weight:500; font-size:0.8rem;">Open module</span>
                            <i class="fas fa-arrow-right" style="color:#667eea; font-size:0.8rem;"></i>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-lg-3 col-12">
        <h5 style="color:var(--text-muted); font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:16px;">
            <i class="fas fa-bolt mr-2" style="color:#f39c12;"></i>Quick Actions
        </h5>
        <div class="card mb-4">
            <div class="card-body" style="padding:16px;">
                @php
                    $actions = [
                        ['href' => route('modules.master-setup').'#products',  'icon' => 'fas fa-plus',          'label' => 'Add Product',   'color' => '#667eea'],
                        ['href' => route('modules.master-setup').'#customers', 'icon' => 'fas fa-user-plus',     'label' => 'Add Customer',  'color' => '#00b894'],
                        ['href' => route('modules.transactions'),              'icon' => 'fas fa-shopping-cart',  'label' => 'New Sale',      'color' => '#f39c12'],
                        ['href' => route('ai-invoice.index'),                  'icon' => 'fas fa-camera',         'label' => 'Scan Invoice',  'color' => '#6c5ce7'],
                    ];
                @endphp
                @foreach($actions as $a)
                <a href="{{ $a['href'] }}" class="d-flex align-items-center mb-2 quick-action-link" style="padding:12px; background:var(--bg-card-hover); border-radius:10px; text-decoration:none; color:var(--text-secondary); transition: background 0.2s;"
                   onmouseover="this.style.background='rgba(102,126,234,0.15)'" onmouseout="this.style.background=''">
                    <div style="width:34px; height:34px; border-radius:8px; background:{{ $a['color'] }}; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0;">
                        <i class="{{ $a['icon'] }}" style="color:#fff; font-size:0.8rem;"></i>
                    </div>
                    <span style="font-weight:500; font-size:0.9rem;">{{ $a['label'] }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
