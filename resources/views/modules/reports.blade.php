@extends('layouts.app')

@section('title', 'Reports & Final Accounts')
@section('header', 'Reports & Final Accounts')

@section('content')

{{-- Tabs --}}
<div class="card mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important; border: 0 !important;">
    <div class="card-body d-flex flex-wrap" style="padding:10px; gap:8px;">
        @php
            $tabs = [
                'day-book'      => ['fas fa-book', 'Day Book'],
                'ledger'        => ['fas fa-file-alt', 'Ledger'],
                'trial-balance' => ['fas fa-balance-scale', 'Trial Balance'],
                'profit-loss'   => ['fas fa-chart-line', 'P&L'],
                'balance-sheet' => ['fas fa-file-invoice-dollar', 'Balance Sheet'],
            ];
        @endphp
        @foreach($tabs as $tabKey => $tabInfo)
        <button type="button" class="btn report-tab-btn" id="tab-btn-{{ $tabKey }}" data-tab="{{ $tabKey }}"
                style="{{ $loop->first ? 'background:#fff !important; color:#1e3c72 !important; font-weight:600;' : 'background:rgba(255,255,255,0.12) !important; color:rgba(255,255,255,0.85) !important; border:0;' }} padding:10px 18px; border-radius:8px; font-size:0.9rem;">
            <i class="{{ $tabInfo[0] }} mr-1"></i> {{ $tabInfo[1] }}
        </button>
        @endforeach
    </div>
</div>

{{-- ============================================================ --}}
{{-- UPLOAD ZONE                                                    --}}
{{-- ============================================================ --}}
<div class="card mb-4" id="upload-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="font-weight:600;">
            <i class="fas fa-cloud-upload-alt mr-2" style="color:#74b9ff;"></i>Upload Invoices
        </h5>
        <span class="text-muted" style="font-size:0.8rem;">PDF, JPG, PNG &mdash; max 10 MB</span>
    </div>
    <div class="card-body">
        <div id="drop-zone" style="border:2px dashed rgba(255,255,255,0.15); border-radius:12px; padding:40px 20px; text-align:center; cursor:pointer; transition:all 0.2s ease;">
            <i class="fas fa-file-upload fa-3x mb-3" style="opacity:0.3;"></i>
            <p class="mb-1" style="font-weight:500;">Drag & drop invoices here</p>
            <p class="text-muted mb-0" style="font-size:0.85rem;">or click to browse files</p>
            <input type="file" id="file-input" accept=".pdf,.jpg,.jpeg,.png" multiple class="d-none">
        </div>
        {{-- Upload progress --}}
        <div id="upload-progress" class="mt-3" style="display:none;">
            <div class="progress" style="height:6px; border-radius:3px;">
                <div id="progress-bar" class="progress-bar" role="progressbar" style="width:0%; background:linear-gradient(90deg,#667eea,#764ba2);"></div>
            </div>
            <small id="upload-status" class="text-muted mt-1 d-block"></small>
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- UPLOADED INVOICES GALLERY                                      --}}
{{-- ============================================================ --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0" style="font-weight:600;">
            <i class="fas fa-images mr-2" style="color:#a29bfe;"></i>Uploaded Invoices
            <span id="upload-count" class="badge badge-secondary ml-2" style="font-size:0.75rem;">{{ $uploads->count() }}</span>
        </h5>
    </div>
    <div class="card-body" id="gallery-body">
        @if($uploads->isEmpty())
        <div id="empty-state" class="text-center py-5">
            <i class="fas fa-file-invoice fa-3x mb-3" style="opacity:0.15;"></i>
            <p class="text-muted">No invoices uploaded yet. Upload your first invoice above.</p>
        </div>
        @else
        <div id="empty-state" class="text-center py-5" style="display:none;">
            <i class="fas fa-file-invoice fa-3x mb-3" style="opacity:0.15;"></i>
            <p class="text-muted">No invoices uploaded yet. Upload your first invoice above.</p>
        </div>
        @endif

        <div id="gallery-grid" class="row" style="{{ $uploads->isEmpty() ? 'display:none;' : '' }}">
            @foreach($uploads as $up)
            <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3" id="card-{{ $up->id }}">
                <div class="card mb-0 h-100" style="cursor:pointer; overflow:hidden; transition:transform 0.15s ease, box-shadow 0.15s ease;"
                     onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.3)';"
                     onmouseout="this.style.transform=''; this.style.boxShadow='';">
                    {{-- Thumbnail / Icon --}}
                    <div style="height:180px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); overflow:hidden;"
                         onclick="openPreview('{{ route('modules.reports.preview', $up->id) }}', '{{ $up->mime_type }}', '{{ addslashes($up->original_name) }}')">
                        @if(str_contains($up->mime_type, 'pdf'))
                            <div class="text-center">
                                <i class="fas fa-file-pdf fa-4x" style="color:#e74c3c; opacity:0.8;"></i>
                                <p class="text-muted mt-2 mb-0" style="font-size:0.75rem;">PDF Document</p>
                            </div>
                        @else
                            <img src="{{ route('modules.reports.preview', $up->id) }}" alt="{{ $up->original_name }}"
                                 style="width:100%; height:100%; object-fit:cover;">
                        @endif
                    </div>
                    {{-- Info --}}
                    <div class="card-body py-2 px-3">
                        <p class="mb-1 text-truncate" style="font-size:0.82rem; font-weight:500;" title="{{ $up->original_name }}">{{ $up->original_name }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $up->created_at->format('d M Y') }}</small>
                            <button type="button" class="btn btn-sm p-0" style="color:#e74c3c; font-size:0.8rem;" title="Delete"
                                    onclick="event.stopPropagation(); deleteUpload({{ $up->id }}, '{{ route('modules.reports.destroy', $up->id) }}')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ============================================================ --}}
{{-- PREVIEW MODAL                                                  --}}
{{-- ============================================================ --}}
<div id="preview-modal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.85); backdrop-filter:blur(4px);">
    <div style="position:absolute; top:0; left:0; right:0; padding:16px 24px; display:flex; justify-content:space-between; align-items:center; z-index:10;">
        <span id="preview-title" style="color:#fff; font-weight:600; font-size:1rem;"></span>
        <button type="button" onclick="closePreview()" style="background:rgba(255,255,255,0.1); border:0; color:#fff; width:40px; height:40px; border-radius:50%; font-size:1.2rem; cursor:pointer; transition:background 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="preview-content" style="position:absolute; top:60px; bottom:20px; left:20px; right:20px; display:flex; align-items:center; justify-content:center;"></div>
</div>

{{-- ============================================================ --}}
{{-- REPORT TAB PANELS (placeholders)                               --}}
{{-- ============================================================ --}}
<div id="tab-panel-day-book" class="report-tab-panel" style="display:none;">
    <div class="card mb-0">
        <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-book mr-2" style="color:#74b9ff;"></i>Day Book</h5></div>
        <div class="card-body text-center py-5"><i class="fas fa-book fa-3x mb-3" style="opacity:0.2;"></i><p class="text-muted">Day Book report coming soon.</p></div>
    </div>
</div>
<div id="tab-panel-ledger" class="report-tab-panel" style="display:none;">
    <div class="card mb-0">
        <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-file-alt mr-2" style="color:#a29bfe;"></i>Ledger</h5></div>
        <div class="card-body text-center py-5"><i class="fas fa-file-alt fa-3x mb-3" style="opacity:0.2;"></i><p class="text-muted">Ledger report coming soon.</p></div>
    </div>
</div>
<div id="tab-panel-trial-balance" class="report-tab-panel" style="display:none;">
    <div class="card mb-0">
        <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-balance-scale mr-2" style="color:#00cec9;"></i>Trial Balance</h5></div>
        <div class="card-body text-center py-5"><i class="fas fa-balance-scale fa-3x mb-3" style="opacity:0.2;"></i><p class="text-muted">Trial Balance report coming soon.</p></div>
    </div>
</div>
<div id="tab-panel-profit-loss" class="report-tab-panel" style="display:none;">
    <div class="card mb-0">
        <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-chart-line mr-2" style="color:#fdcb6e;"></i>Profit &amp; Loss</h5></div>
        <div class="card-body text-center py-5"><i class="fas fa-chart-line fa-3x mb-3" style="opacity:0.2;"></i><p class="text-muted">Profit &amp; Loss report coming soon.</p></div>
    </div>
</div>
<div id="tab-panel-balance-sheet" class="report-tab-panel" style="display:none;">
    <div class="card mb-0">
        <div class="card-header"><h5 class="mb-0" style="font-weight:600;"><i class="fas fa-file-invoice-dollar mr-2" style="color:#55efc4;"></i>Balance Sheet</h5></div>
        <div class="card-body text-center py-5"><i class="fas fa-file-invoice-dollar fa-3x mb-3" style="opacity:0.2;"></i><p class="text-muted">Balance Sheet report coming soon.</p></div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){

    /* ======== Tab switching ======== */
    var tabBtns   = document.querySelectorAll('.report-tab-btn');
    var tabPanels = document.querySelectorAll('.report-tab-panel');
    var uploadCard = document.getElementById('upload-card');
    var galleryCard = uploadCard ? uploadCard.nextElementSibling : null; // the gallery card

    // Find the gallery card properly
    var allCards = document.querySelectorAll('.card.mb-4');
    var galleryCardEl = allCards[2]; // 3rd card = gallery

    tabBtns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var target = this.dataset.tab;

            // Reset buttons
            tabBtns.forEach(function(b){
                b.style.background  = 'rgba(255,255,255,0.12)';
                b.style.color       = 'rgba(255,255,255,0.85)';
                b.style.fontWeight  = '400';
            });
            this.style.background  = '#fff';
            this.style.color       = '#1e3c72';
            this.style.fontWeight  = '600';

            // Hide upload & gallery cards, show tab panels
            if(uploadCard)    uploadCard.style.display    = 'none';
            if(galleryCardEl) galleryCardEl.style.display = 'none';
            tabPanels.forEach(function(p){ p.style.display = 'none'; });
            document.getElementById('tab-panel-' + target).style.display = 'block';
        });
    });

    /* ======== File Upload ======== */
    var dropZone  = document.getElementById('drop-zone');
    var fileInput = document.getElementById('file-input');
    var progressWrap = document.getElementById('upload-progress');
    var progressBar  = document.getElementById('progress-bar');
    var uploadStatus = document.getElementById('upload-status');

    // Click to browse
    dropZone.addEventListener('click', function(){ fileInput.click(); });
    fileInput.addEventListener('change', function(){ handleFiles(this.files); this.value = ''; });

    // Drag & drop
    ['dragenter','dragover'].forEach(function(evt){
        dropZone.addEventListener(evt, function(e){
            e.preventDefault(); e.stopPropagation();
            dropZone.style.borderColor = '#667eea';
            dropZone.style.background  = 'rgba(102,126,234,0.06)';
        });
    });
    ['dragleave','drop'].forEach(function(evt){
        dropZone.addEventListener(evt, function(e){
            e.preventDefault(); e.stopPropagation();
            dropZone.style.borderColor = 'rgba(255,255,255,0.15)';
            dropZone.style.background  = '';
        });
    });
    dropZone.addEventListener('drop', function(e){
        handleFiles(e.dataTransfer.files);
    });

    function handleFiles(files){
        if(!files.length) return;
        var queue = Array.from(files);
        var total = queue.length, done = 0;
        progressWrap.style.display = 'block';
        uploadStatus.textContent = 'Uploading 0 / ' + total + '...';
        progressBar.style.width = '0%';

        function next(){
            if(!queue.length){
                uploadStatus.textContent = 'All ' + total + ' file(s) uploaded!';
                progressBar.style.width = '100%';
                setTimeout(function(){ progressWrap.style.display = 'none'; }, 2000);
                return;
            }
            var file = queue.shift();
            uploadFile(file, function(){
                done++;
                progressBar.style.width = Math.round((done/total)*100) + '%';
                uploadStatus.textContent = 'Uploading ' + done + ' / ' + total + '...';
                next();
            });
        }
        next();
    }

    function uploadFile(file, onDone){
        var fd = new FormData();
        fd.append('file', file);
        fd.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("modules.reports.upload") }}', { method:'POST', body:fd, headers:{ 'X-Requested-With':'XMLHttpRequest' } })
        .then(function(r){ return r.json(); })
        .then(function(data){
            appendCard(data);
            onDone();
        })
        .catch(function(err){
            console.error(err);
            uploadStatus.textContent = 'Error uploading ' + file.name;
            onDone();
        });
    }

    function appendCard(data){
        // Hide empty state, show grid
        var empty = document.getElementById('empty-state');
        if(empty) empty.style.display = 'none';
        var grid = document.getElementById('gallery-grid');
        grid.style.display = '';

        var isPdf = data.mime_type && data.mime_type.indexOf('pdf') !== -1;
        var thumbHtml = isPdf
            ? '<div class="text-center"><i class="fas fa-file-pdf fa-4x" style="color:#e74c3c; opacity:0.8;"></i><p class="text-muted mt-2 mb-0" style="font-size:0.75rem;">PDF Document</p></div>'
            : '<img src="' + data.preview_url + '" alt="' + data.original_name + '" style="width:100%; height:100%; object-fit:cover;">';

        var col = document.createElement('div');
        col.className = 'col-lg-3 col-md-4 col-sm-6 col-12 mb-3';
        col.id = 'card-' + data.id;
        col.innerHTML =
            '<div class="card mb-0 h-100" style="cursor:pointer; overflow:hidden; transition:transform 0.15s ease, box-shadow 0.15s ease;" ' +
            'onmouseover="this.style.transform=\'translateY(-2px)\'; this.style.boxShadow=\'0 6px 20px rgba(0,0,0,0.3)\';" ' +
            'onmouseout="this.style.transform=\'\'; this.style.boxShadow=\'\';">' +
                '<div style="height:180px; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.02); overflow:hidden;" ' +
                    'onclick="openPreview(\'' + data.preview_url + '\', \'' + data.mime_type + '\', \'' + data.original_name.replace(/'/g, "\\'") + '\')">' +
                    thumbHtml +
                '</div>' +
                '<div class="card-body py-2 px-3">' +
                    '<p class="mb-1 text-truncate" style="font-size:0.82rem; font-weight:500;" title="' + data.original_name + '">' + data.original_name + '</p>' +
                    '<div class="d-flex justify-content-between align-items-center">' +
                        '<small class="text-muted">' + data.created_at + '</small>' +
                        '<button type="button" class="btn btn-sm p-0" style="color:#e74c3c; font-size:0.8rem;" title="Delete" ' +
                            'onclick="event.stopPropagation(); deleteUpload(' + data.id + ', \'' + data.delete_url + '\')">' +
                            '<i class="fas fa-trash-alt"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';
        grid.prepend(col);

        // Update count badge
        var countBadge = document.getElementById('upload-count');
        countBadge.textContent = parseInt(countBadge.textContent || 0) + 1;
    }
});

/* ======== Preview Modal ======== */
function openPreview(url, mimeType, name){
    var modal   = document.getElementById('preview-modal');
    var content = document.getElementById('preview-content');
    var title   = document.getElementById('preview-title');

    title.textContent = name;
    content.innerHTML = '';

    if(mimeType && mimeType.indexOf('pdf') !== -1){
        content.innerHTML = '<iframe src="' + url + '" style="width:100%; height:100%; border:0; border-radius:8px; background:#fff;"></iframe>';
    } else {
        content.innerHTML = '<img src="' + url + '" style="max-width:100%; max-height:100%; object-fit:contain; border-radius:8px; box-shadow:0 4px 30px rgba(0,0,0,0.5);">';
    }

    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closePreview(){
    var modal = document.getElementById('preview-modal');
    modal.style.display = 'none';
    document.getElementById('preview-content').innerHTML = '';
    document.body.style.overflow = '';
}

// Close on Escape key
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closePreview();
});

/* ======== Delete ======== */
function deleteUpload(id, url){
    if(!confirm('Delete this invoice?')) return;

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(function(r){ return r.json(); })
    .then(function(){
        var card = document.getElementById('card-' + id);
        if(card) card.remove();

        // Update count
        var countBadge = document.getElementById('upload-count');
        var c = parseInt(countBadge.textContent || 0) - 1;
        countBadge.textContent = Math.max(c, 0);

        // Show empty state if no cards left
        var grid = document.getElementById('gallery-grid');
        if(!grid.children.length){
            grid.style.display = 'none';
            document.getElementById('empty-state').style.display = '';
        }
    })
    .catch(function(err){ console.error(err); alert('Failed to delete.'); });
}
</script>
@endpush
