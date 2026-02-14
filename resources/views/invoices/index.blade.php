@extends('layouts.app')

@section('title', 'My Invoices')
@section('header', 'My Invoices')

@push('styles')
<style>
    .invoice-row { transition: all 0.2s ease; }
    .invoice-row:hover { background: var(--bg-card-hover) !important; }
    .status-badge { font-size: 0.68rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
    .generate-dropdown { position: relative; display: inline-block; }
    .generate-dropdown .dropdown-content { display: none; position: absolute; right: 0; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 8px; min-width: 200px; z-index: 100; box-shadow: 0 8px 25px rgba(0,0,0,0.15); padding: 8px 0; }
    .generate-dropdown:hover .dropdown-content { display: block; }
    .generate-dropdown .dropdown-content a { display: block; padding: 8px 16px; color: var(--text-secondary); font-size: 0.82rem; text-decoration: none; }
    .generate-dropdown .dropdown-content a:hover { background: var(--bg-hover); color: var(--text-primary); }
    .empty-invoices { text-align: center; padding: 60px 20px; }
    .empty-invoices i { font-size: 3rem; color: var(--text-muted); margin-bottom: 16px; }
</style>
@endpush

@section('content')

{{-- Toast --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">All your generated invoices. Click "Generate" to preview/print with any template.</p>
    <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create Invoice</a>
</div>

@if($invoices->count() > 0)
<div class="card">
    <div class="card-body" style="padding:0;">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Invoice No.</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th class="text-right">Items</th>
                    <th class="text-right">Net Amount</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $inv)
                <tr class="invoice-row">
                    <td><strong style="color:#667eea;">{{ $inv->invoice_number }}</strong></td>
                    <td>{{ $inv->invoice_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $inv->party_name ?? $inv->customer?->name ?? 'Walk-in' }}</td>
                    <td><span class="status-badge" style="background:rgba(102,126,234,0.15);color:#667eea;">{{ $inv->document_type ?? '-' }}</span></td>
                    <td class="text-right">{{ $inv->items->count() }}</td>
                    <td class="text-right" style="font-weight:700;">{{ number_format($inv->net_amount, 2) }}</td>
                    <td class="text-right">
                        <div class="generate-dropdown">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-file-alt mr-1"></i> Generate <i class="fas fa-caret-down ml-1"></i></button>
                            <div class="dropdown-content">
                                @foreach($templates as $tpl)
                                <a href="{{ route('invoices.generate', ['invoice' => $inv->id, 'template' => $tpl->id]) }}">
                                    <i class="fas fa-palette mr-2" style="color:#667eea;"></i>{{ $tpl->name }}
                                    @if($tpl->is_default)<span class="badge badge-success ml-1" style="font-size:0.58rem;">Default</span>@endif
                                </a>
                                @endforeach
                                @if($templates->count() === 0)
                                <a href="{{ route('invoices.templates') }}"><i class="fas fa-plus mr-2"></i>Create Template First</a>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($invoices->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@else
<div class="card">
    <div class="card-body empty-invoices">
        <i class="fas fa-file-alt d-block"></i>
        <h5>No Invoices Yet</h5>
        <p class="text-muted">Create your first invoice to get started.</p>
        <a href="{{ route('invoices.create') }}" class="btn btn-primary"><i class="fas fa-plus mr-1"></i> Create Invoice</a>
    </div>
</div>
@endif

@endsection
