@extends('layouts.app')

@section('title', $title ?? 'Module')
@section('header', $title ?? 'Module')

@section('content')
<div class="row justify-content-center" style="margin-top:40px;">
    <div class="col-lg-6 col-md-8 col-12">
        <div class="card text-center" style="padding:40px 20px;">
            <div class="card-body">
                <div style="width:90px; height:90px; border-radius:20px; background:linear-gradient(135deg,#667eea,#764ba2); display:inline-flex; align-items:center; justify-content:center; margin-bottom:24px;">
                    @if(str_contains(strtolower($title ?? ''), 'transaction'))
                        <i class="fas fa-receipt fa-2x" style="color:#fff;"></i>
                    @elseif(str_contains(strtolower($title ?? ''), 'book'))
                        <i class="fas fa-book-open fa-2x" style="color:#fff;"></i>
                    @elseif(str_contains(strtolower($title ?? ''), 'report'))
                        <i class="fas fa-chart-pie fa-2x" style="color:#fff;"></i>
                    @else
                        <i class="fas fa-rocket fa-2x" style="color:#fff;"></i>
                    @endif
                </div>
                <h3 style="font-weight:700; margin-bottom:10px;">{{ $title ?? 'Coming Soon' }}</h3>
                <p class="text-muted" style="max-width:400px; margin:0 auto 24px;">{{ $description ?? 'This module is under development. We\'re working to bring you powerful features soon.' }}</p>

                @if(str_contains(strtolower($title ?? ''), 'transaction'))
                <div class="mb-4">
                    @foreach(['Sales','Purchases','Receipts','Payments','Journal'] as $f)
                    <span class="badge badge-info mr-1 mb-1" style="padding:6px 12px; font-size:0.8rem;">{{ $f }}</span>
                    @endforeach
                </div>
                @elseif(str_contains(strtolower($title ?? ''), 'book'))
                <div class="mb-4">
                    @foreach(['Purchase Book','Sales Book','Returns'] as $f)
                    <span class="badge badge-info mr-1 mb-1" style="padding:6px 12px; font-size:0.8rem;">{{ $f }}</span>
                    @endforeach
                </div>
                @elseif(str_contains(strtolower($title ?? ''), 'report'))
                <div class="mb-4">
                    @foreach(['Day Book','Ledger','Trial Balance','P&L','Balance Sheet'] as $f)
                    <span class="badge badge-info mr-1 mb-1" style="padding:6px 12px; font-size:0.8rem;">{{ $f }}</span>
                    @endforeach
                </div>
                @endif

                <a href="{{ route('dashboard') }}" class="btn btn-secondary"><i class="fas fa-arrow-left mr-2"></i>Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
