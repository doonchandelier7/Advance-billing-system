@extends('layouts.app')

@section('title', 'AI Invoice Scan')
@section('header', 'AI Invoice Scan')

@section('content')
{{-- Page Header --}}
<div class="card mb-4" style="background:linear-gradient(135deg,#a29bfe,#6c5ce7) !important; border:0 !important;">
    <div class="card-body d-flex align-items-center" style="padding:24px;">
        <div style="width:52px; height:52px; background:rgba(255,255,255,0.2); border-radius:12px; display:flex; align-items:center; justify-content:center; margin-right:16px; flex-shrink:0;">
            <i class="fas fa-magic" style="font-size:1.3rem; color:#fff;"></i>
        </div>
        <div>
            <h4 style="font-weight:700; margin-bottom:4px; color:#fff;">AI Invoice Scanner</h4>
            <p style="color:rgba(255,255,255,0.8); margin:0; font-size:0.9rem;">Upload an invoice image or PDF and let AI extract the data automatically (no API key needed)</p>
        </div>
    </div>
</div>

{{-- 1. Image Upload --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center">
        <span class="badge badge-info mr-2" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; border-radius:8px;">1</span>
        <h5 class="mb-0" style="font-weight:600;">Upload Invoice Image</h5>
    </div>
    <div class="card-body">
        <div class="text-center py-4" style="border:2px dashed rgba(255,255,255,0.15); border-radius:12px; background:rgba(255,255,255,0.02);">
            <div style="width:70px; height:70px; background:linear-gradient(135deg,#a29bfe,#6c5ce7); border-radius:16px; display:inline-flex; align-items:center; justify-content:center; margin-bottom:16px;">
                <i class="fas fa-cloud-upload-alt fa-2x" style="color:#fff;"></i>
            </div>
            <h5 style="font-weight:600; margin-bottom:6px;">Upload or Capture Invoice</h5>
            <p class="text-muted mb-3">Select an image file or use your camera</p>
            <label class="btn btn-primary mb-0 mr-2" style="cursor:pointer;">
                <input type="file" id="fileInput" accept="image/jpeg,image/jpg,image/png,application/pdf,.pdf" class="d-none">
                <i class="fas fa-folder-open mr-1"></i> Choose File
            </label>
            <button type="button" id="captureBtn" class="btn btn-secondary">
                <i class="fas fa-camera mr-1"></i> Use Camera
            </button>
            <p class="text-muted small mt-3 mb-0">JPG, PNG, JPEG, PDF (Max 10 MB)</p>
        </div>
        
        <div id="previewArea" class="d-none mt-4">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i> <strong id="uploadSuccessText">File uploaded!</strong> Click <strong>Process with AI / OCR</strong> to extract data, or <button type="button" class="btn btn-link btn-sm p-0" id="enterManuallyFromUpload">enter manually</button>.
            </div>
            {{-- Image preview --}}
            <div id="imagePreviewContainer" class="text-center mb-3">
                <img id="previewImg" src="" alt="Preview" class="img-fluid rounded" style="max-height:280px; object-fit:contain; background:rgba(0,0,0,0.2); border-radius:10px;">
            </div>
            {{-- PDF preview (icon + filename) --}}
            <div id="pdfPreviewContainer" class="text-center mb-3 d-none">
                <div style="padding:30px; background:rgba(255,255,255,0.05); border-radius:12px; border:1px solid rgba(255,255,255,0.1);">
                    <i class="fas fa-file-pdf fa-4x" style="color:#e74c3c; margin-bottom:12px;"></i>
                    <p class="mb-1" style="font-weight:600;" id="pdfFileName">document.pdf</p>
                    <p class="text-muted small mb-0">PDF file ready for text extraction</p>
                </div>
            </div>
            <div class="d-flex flex-wrap" style="gap:10px;">
                <button type="button" id="processBtn" class="btn btn-success"><i class="fas fa-magic mr-1"></i> Process with AI / OCR</button>
                <button type="button" id="clearPreviewBtn" class="btn btn-secondary"><i class="fas fa-times mr-1"></i> Clear</button>
            </div>
            <div id="qualityWarning" class="alert alert-warning mt-3 d-none small"></div>
            <div id="processError" class="alert alert-danger mt-3 d-none">
                <span id="processErrorText"></span>
                <div class="mt-2"><button type="button" class="btn btn-sm btn-secondary" id="enterManuallyAfterError"><i class="fas fa-edit mr-1"></i> Enter Manually</button></div>
            </div>
        </div>
    </div>
</div>

{{-- Camera modal --}}
<div id="cameraModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#a29bfe,#6c5ce7) !important; border:0;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-camera mr-2"></i> Capture from Camera</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-0">
                <video id="cameraVideo" autoplay playsinline class="w-100" style="background: #000;"></video>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeCameraBtn" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" id="capturePhotoBtn" class="btn btn-primary">
                    <i class="fas fa-camera mr-1"></i> Capture Photo
                </button>
            </div>
        </div>
    </div>
</div>
<canvas id="captureCanvas" class="d-none"></canvas>

{{-- 2–4. Extracted form --}}
<form id="invoiceForm" class="d-none">
    {{-- Invoice Header --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <span class="badge badge-info mr-2" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; border-radius:8px;">2</span>
            <h5 class="mb-0" style="font-weight:600;">Invoice Details</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(['document_type'=>'Document Type','doc_number'=>'Invoice / Doc No.','invoice_date'=>'Invoice Date','party_name'=>'Party / Customer','city'=>'City','state'=>'State','gstin'=>'GSTIN','transport_name'=>'Transport','vehicle_number'=>'Vehicle No.','driver_name'=>'Driver','place_of_supply'=>'Place of Supply','eway_bill_no'=>'E‑Way Bill No.','distance_km'=>'Distance (KM)'] as $key => $label)
                <div class="col-md-4 col-lg-3 form-group">
                    <label for="h_{{ $key }}" class="small">{{ $label }}</label>
                    <input type="{{ in_array($key, ['invoice_date']) ? 'date' : (in_array($key, ['distance_km']) ? 'number' : 'text') }}" name="{{ $key }}" id="h_{{ $key }}" step="any" class="form-control invoice-header-field" data-field="{{ $key }}">
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge badge-info mr-2" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; border-radius:8px;">3</span>
                <h5 class="mb-0" style="font-weight:600;">Line Items</h5>
            </div>
            <button type="button" id="addItemBtn" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Add Item</button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product / Description</th>
                        <th>HSN</th>
                        <th class="text-right">Qty</th>
                        <th>Unit</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">GST %</th>
                        <th class="text-right">Amount</th>
                        <th style="width: 90px;"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody"></tbody>
            </table>
        </div>
    </div>

    {{-- Totals --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center">
            <span class="badge badge-info mr-2" style="width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; font-size:0.85rem; border-radius:8px;">4</span>
            <h5 class="mb-0" style="font-weight:600;">Totals & Summary</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach(['taxable_amount'=>'Taxable Amount','gst_amount'=>'GST Amount','cgst_amount'=>'CGST','sgst_amount'=>'SGST','igst_amount'=>'IGST','net_amount'=>'Net Amount','advance_amount'=>'Advance','balance_amount'=>'Balance'] as $key => $label)
                <div class="col-md-3 col-lg-2 form-group">
                    <label for="t_{{ $key }}" class="small">{{ $label }}</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">₹</span>
                        </div>
                        <input type="number" name="{{ $key }}" id="t_{{ $key }}" step="0.01" min="0" class="form-control invoice-total-field" data-field="{{ $key }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <input type="hidden" name="source_image_path" id="source_image_path">
    <input type="hidden" name="upload_id" id="upload_id">
    <input type="hidden" name="extraction_confidence" id="extraction_confidence">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">

    <div class="mb-4 text-center">
        <button type="submit" id="saveBtn" class="btn btn-success btn-lg" style="padding:14px 40px; font-weight:600;">
            <i class="fas fa-save mr-2"></i> Save Invoice & Generate PDF
        </button>
    </div>
    <div id="saveError" class="alert alert-danger d-none"></div>
    <div id="saveSuccess" class="alert alert-success d-none"></div>
</form>

<script>
(function() {
    function initAiInvoice() {
        if (typeof window.jQuery === 'undefined') {
            window.setTimeout(initAiInvoice, 50);
            return;
        }
        var $ = window.jQuery;
    const fileInput = document.getElementById('fileInput');
    const captureBtn = document.getElementById('captureBtn');
    const previewArea = document.getElementById('previewArea');
    const previewImg = document.getElementById('previewImg');
    const processBtn = document.getElementById('processBtn');
    const clearPreviewBtn = document.getElementById('clearPreviewBtn');
    const qualityWarning = document.getElementById('qualityWarning');
    const processError = document.getElementById('processError');
    const processErrorText = document.getElementById('processErrorText');
    const cameraModal = document.getElementById('cameraModal');
    const cameraVideo = document.getElementById('cameraVideo');
    const capturePhotoBtn = document.getElementById('capturePhotoBtn');
    const closeCameraBtn = document.getElementById('closeCameraBtn');
    const captureCanvas = document.getElementById('captureCanvas');
    const invoiceForm = document.getElementById('invoiceForm');
    const itemsBody = document.getElementById('itemsBody');
    const addItemBtn = document.getElementById('addItemBtn');
    const saveBtn = document.getElementById('saveBtn');
    const saveError = document.getElementById('saveError');
    const saveSuccess = document.getElementById('saveSuccess');

    let currentPath = null;
    let currentUploadId = null;
    const csrf = document.querySelector('input[name="_token"]').value;

    let isCurrentPdf = false;

    function showPreview(src, isPdf, fileName) {
        previewArea.classList.remove('d-none');
        processError.classList.add('d-none');
        qualityWarning.classList.add('d-none');

        const imageContainer = document.getElementById('imagePreviewContainer');
        const pdfContainer = document.getElementById('pdfPreviewContainer');
        const uploadText = document.getElementById('uploadSuccessText');

        if (isPdf) {
            isCurrentPdf = true;
            imageContainer.classList.add('d-none');
            pdfContainer.classList.remove('d-none');
            document.getElementById('pdfFileName').textContent = fileName || 'document.pdf';
            if (uploadText) uploadText.textContent = 'PDF uploaded!';
        } else {
            isCurrentPdf = false;
            pdfContainer.classList.add('d-none');
            imageContainer.classList.remove('d-none');
            previewImg.src = src || '';
            if (uploadText) uploadText.textContent = 'Image uploaded!';
        }
    }

    function showFormManually() {
        fillForm({
            header: {},
            items: [],
            totals: {},
            overall_confidence: 0,
            low_confidence_fields: [],
            quality_warning: null
        });
        invoiceForm.classList.remove('d-none');
        processError.classList.add('d-none');
        var saveErr = document.getElementById('saveError');
        if (saveErr) saveErr.classList.add('d-none');
        invoiceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function clearPreview() {
        previewImg.src = '';
        previewArea.classList.add('d-none');
        currentPath = null;
        currentUploadId = null;
        isCurrentPdf = false;
        document.getElementById('source_image_path').value = '';
        document.getElementById('upload_id').value = '';
        document.getElementById('imagePreviewContainer').classList.remove('d-none');
        document.getElementById('pdfPreviewContainer').classList.add('d-none');
    }

    fileInput.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        const fd = new FormData();
        fd.append('image', file);
        fd.append('_token', csrf);
        try {
            const r = await fetch('{{ route("ai-invoice.upload") }}', { method: 'POST', body: fd });
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Upload failed');
            currentPath = data.path;
            currentUploadId = data.upload_id;
            document.getElementById('source_image_path').value = data.path;
            document.getElementById('upload_id').value = data.upload_id;
            showPreview(data.preview_url, data.is_pdf || false, data.original_name || file.name);
        } catch (e) {
            if (processErrorText) processErrorText.textContent = e.message || 'Upload failed';
            processError.classList.remove('d-none');
        }
    });

    document.getElementById('enterManuallyFromUpload').addEventListener('click', showFormManually);
    document.getElementById('enterManuallyAfterError').addEventListener('click', showFormManually);

    captureBtn.addEventListener('click', function() {
        $('#cameraModal').modal('show');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } }).then(stream => {
            cameraVideo.srcObject = stream;
        }).catch(() => {
            navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
                cameraVideo.srcObject = stream;
            }).catch(() => { alert('Camera access denied'); $('#cameraModal').modal('hide'); });
        });
    });

    function stopCameraStream() {
        if (cameraVideo.srcObject) {
            cameraVideo.srcObject.getTracks().forEach(t => t.stop());
            cameraVideo.srcObject = null;
        }
    }

    $(cameraModal).on('hidden.bs.modal', stopCameraStream);
    closeCameraBtn.addEventListener('click', function() {
        $('#cameraModal').modal('hide');
    });

    capturePhotoBtn.addEventListener('click', function() {
        const ctx = captureCanvas.getContext('2d');
        captureCanvas.width = cameraVideo.videoWidth;
        captureCanvas.height = cameraVideo.videoHeight;
        ctx.drawImage(cameraVideo, 0, 0);
        captureCanvas.toBlob(async function(blob) {
            $('#cameraModal').modal('hide');
            stopCameraStream();
            const file = new File([blob], 'capture.jpg', { type: 'image/jpeg' });
            const fd = new FormData();
            fd.append('image', file);
            fd.append('_token', csrf);
            try {
                const r = await fetch('{{ route("ai-invoice.upload") }}', { method: 'POST', body: fd });
                const data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Upload failed');
                currentPath = data.path;
                currentUploadId = data.upload_id;
                document.getElementById('source_image_path').value = data.path;
                document.getElementById('upload_id').value = data.upload_id;
                showPreview(data.preview_url, false, 'capture.jpg');
            } catch (e) {
                if (processErrorText) processErrorText.textContent = e.message || 'Upload failed';
                processError.classList.remove('d-none');
            }
        }, 'image/jpeg', 0.9);
    });

    clearPreviewBtn.addEventListener('click', clearPreview);

    processBtn.addEventListener('click', async function() {
        processError.classList.add('d-none');
        qualityWarning.classList.add('d-none');
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing…';
        try {
            const r = await fetch('{{ route("ai-invoice.process") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ upload_id: currentUploadId, path: currentPath })
            });
            const data = await r.json();
            var errMsg = data.message || data.error || 'Processing failed';
            if (!r.ok) throw new Error(errMsg);
            if (data.quality_warning) {
                qualityWarning.textContent = data.quality_warning;
                qualityWarning.classList.remove('d-none');
            }
            fillForm(data);
            invoiceForm.classList.remove('d-none');
            invoiceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {
            if (processErrorText) processErrorText.textContent = e.message || 'Processing failed';
            processError.classList.remove('d-none');
        }
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-magic mr-1"></i> Process with AI / OCR';
    });

    function fillForm(data) {
        const low = (data.low_confidence_fields || []).map(f => f.toLowerCase().replace(/_/g, ''));
        document.querySelectorAll('.invoice-header-field').forEach(el => {
            const field = el.dataset.field;
            const v = (data.header || {})[field];
            if (v !== undefined && v !== null) el.value = v;
            const isLow = low.some(l => field.toLowerCase().replace(/_/g, '').includes(l) || l.includes(field.toLowerCase().replace(/_/g, '')));
            el.classList.toggle('border-warning', isLow);
            el.classList.toggle('bg-light', isLow);
        });
        document.querySelectorAll('.invoice-total-field').forEach(el => {
            const field = el.dataset.field;
            const v = (data.totals || {})[field];
            if (v !== undefined && v !== null) el.value = v;
            el.classList.toggle('border-warning', low.includes(field));
            el.classList.toggle('bg-warning', low.includes(field));
        });
        document.getElementById('extraction_confidence').value = data.overall_confidence ?? '';
        itemsBody.innerHTML = '';
        (data.items || []).forEach((row, i) => addItemRow(row, i));
        if (!(data.items && data.items.length)) addItemRow({}, 0);
    }

    function addItemRow(row = {}, index) {
        const tr = document.createElement('tr');
        const low = (row.confidence !== undefined && row.confidence < 0.7);
        tr.innerHTML = `
            <td><input type="text" name="items[${index}][product_name]" value="${escapeAttr(row.product_name || '')}" class="form-control ${low ? 'border-warning' : ''}" placeholder="Product name"></td>
            <td><input type="text" name="items[${index}][hsn_code]" value="${escapeAttr(row.hsn_code || '')}" class="form-control" placeholder="HSN"></td>
            <td><input type="number" name="items[${index}][quantity]" value="${row.quantity ?? ''}" step="0.001" min="0" class="form-control item-qty text-right" placeholder="0"></td>
            <td><input type="text" name="items[${index}][unit]" value="${escapeAttr(row.unit || '')}" class="form-control" placeholder="Unit"></td>
            <td><input type="number" name="items[${index}][rate]" value="${row.rate ?? ''}" step="0.0001" min="0" class="form-control item-rate text-right" placeholder="0.00"></td>
            <td><input type="number" name="items[${index}][gst_percent]" value="${row.gst_percent ?? ''}" step="0.01" min="0" max="100" class="form-control text-right" placeholder="0"></td>
            <td><input type="number" name="items[${index}][amount]" value="${row.amount ?? ''}" step="0.01" min="0" class="form-control item-amount text-right" placeholder="0.00"></td>
            <td class="text-center p-2"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
        `;
        tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
        itemsBody.appendChild(tr);
    }

    function escapeAttr(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    addItemBtn.addEventListener('click', () => addItemRow({}, itemsBody.children.length));

    invoiceForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        saveError.classList.add('d-none');
        saveSuccess.classList.add('d-none');
        saveBtn.disabled = true;
        const formData = new FormData(this);
        const payload = {};
        formData.forEach((v, k) => {
            if (k === '_token') return;
            if (k.startsWith('items[')) {
                const m = k.match(/items\[(\d+)\]\[(\w+)\]/);
                if (m) {
                    const i = parseInt(m[1], 10);
                    if (!payload.items) payload.items = [];
                    while (payload.items.length <= i) payload.items.push({});
                    payload.items[i][m[2]] = v === '' ? null : (isNaN(Number(v)) ? v : Number(v));
                }
            } else {
                payload[k] = v === '' ? null : (k === 'invoice_date' || k === 'source_image_path' || k === 'document_type' || k === 'doc_number' || k === 'party_name' || k === 'city' || k === 'state' || k === 'gstin' || k === 'transport_name' || k === 'vehicle_number' || k === 'driver_name' || k === 'place_of_supply' || k === 'eway_bill_no' ? v : (isNaN(Number(v)) ? v : Number(v)));
            }
        });
        payload.items = (payload.items || []).filter(i => i.quantity != null && i.quantity !== '');
        if (payload.upload_id !== undefined) payload.upload_id = parseInt(payload.upload_id, 10) || null;
        if (payload.items.length === 0) {
            saveError.textContent = 'Add at least one item.';
            saveError.classList.remove('d-none');
            saveBtn.disabled = false;
            return;
        }
        try {
            const r = await fetch('{{ route("ai-invoice.save") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || JSON.stringify(data.errors || data));
            saveSuccess.innerHTML = '<i class="fas fa-check-circle mr-2"></i><strong>Invoice saved successfully!</strong> <a href="' + data.pdf_url + '" target="_blank"><i class="fas fa-file-pdf mr-1"></i>Open PDF</a> | <a href="' + data.print_url + '" target="_blank"><i class="fas fa-print mr-1"></i>Print View</a>';
            saveSuccess.classList.remove('d-none');
        } catch (e) {
            saveError.textContent = e.message || 'Save failed';
            saveError.classList.remove('d-none');
        }
        saveBtn.disabled = false;
    });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAiInvoice);
    } else {
        initAiInvoice();
    }
})();
</script>
@endsection
