@extends('layouts.app')

@section('title', $documentLabel . ' ' . ($document->doc_number ?? $record->id))
@section('header', $documentLabel . ' Preview')

@push('styles')
<style>
    .preview-frame { background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
    .preview-content { padding: 30px; min-height: 350px; color: #111; }
    .template-switch { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .tpl-btn { padding: 8px 14px; border-radius: 8px; border: 2px solid var(--border-color); background: var(--bg-input); color: var(--text-secondary); text-decoration: none; font-size: 0.82rem; font-weight: 600; }
    .tpl-btn:hover { border-color: #667eea; color: var(--text-primary); text-decoration: none; }
    .tpl-btn.active { background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; border-color: #667eea; }
    .action-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
</style>
@endpush

@section('content')
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
</div>
@endif

<a href="{{ route('modules.transactions') }}#{{ $backTab }}" class="btn btn-secondary btn-sm mb-3">
    <i class="fas fa-arrow-left mr-1"></i> Back to Transactions
</a>

<div class="card mb-3">
    <div class="card-body">
        <small class="text-muted d-block mb-2" style="text-transform:uppercase;letter-spacing:0.5px;font-size:0.68rem;font-weight:600;">
            Switch Template
        </small>
        <div class="template-switch">
            @foreach($templates as $tpl)
            <a href="{{ route($generateRouteName, [$record->id, 'template' => $tpl->id]) }}"
               class="tpl-btn {{ $template && $template->id === $tpl->id ? 'active' : '' }}">
                {{ $tpl->name }}
                @if($tpl->is_default)<i class="fas fa-star ml-1" style="font-size:0.65rem;"></i>@endif
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="action-bar">
    @if($template)
    <a href="{{ route($printRouteName, [$record->id, 'template' => $template->id]) }}" class="btn btn-primary" target="_blank">
        <i class="fas fa-print mr-1"></i> Print
    </a>
    <button type="button" class="btn btn-success" onclick="downloadPdf()">
        <i class="fas fa-file-pdf mr-1"></i> Save as PDF
    </button>
    @endif
    <a href="{{ route('modules.transactions') }}#{{ $backTab }}" class="btn btn-secondary">
        <i class="fas fa-list mr-1"></i> Back to List
    </a>
</div>

<div class="preview-frame">
    <div class="preview-content">
        @if($renderedHtml)
            {!! $renderedHtml !!}
        @else
            <div style="text-align:center;padding:60px 20px;color:#999;">
                <i class="fas fa-palette" style="font-size:3rem;display:block;margin-bottom:16px;"></i>
                <h5 style="color:#666;">No Template Selected</h5>
                <p>Select a template above to preview this {{ strtolower($documentLabel) }}.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function downloadPdf() {
    @if($template)
    window.open('{{ route($printRouteName, [$record->id, 'template' => $template->id]) }}', '_blank');
    @endif
}
</script>
@endpush
