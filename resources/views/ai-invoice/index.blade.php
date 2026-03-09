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
        <div id="uploadDropZone" class="text-center py-4 position-relative" style="border:2px dashed rgba(255,255,255,0.15); border-radius:12px; background:rgba(255,255,255,0.02);">
            <div id="uploadLoadingOverlay" class="d-none position-absolute" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);border-radius:12px;display:flex;align-items:center;justify-content:center;z-index:10;flex-direction:column;">
                <i class="fas fa-spinner fa-spin fa-3x mb-3" style="color:#a29bfe;"></i>
                <span id="uploadLoadingText" style="font-weight:600;font-size:1.1rem;">Uploading file…</span>
            </div>
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
        
        <div id="previewArea" class="d-none mt-4 position-relative">
            <div id="processLoadingOverlay" class="d-none position-absolute" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.75);border-radius:12px;display:flex;align-items:center;justify-content:center;z-index:10;flex-direction:column;min-height:200px;">
                <i class="fas fa-spinner fa-spin fa-3x mb-3" style="color:#00b894;"></i>
                <span style="font-weight:600;font-size:1.1rem;">Processing file…</span>
                <span class="text-muted small mt-1">Extracting invoice data with AI / OCR</span>
            </div>
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

{{-- Party not found banner (shown when form is visible and party not in DB) --}}
<div id="partyNotFoundBanner" class="card mb-4 border-warning d-none" style="border-width:2px !important;">
    <div class="card-body d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
        <div>
            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
            <strong>Party "<span id="partyNotFoundName"></span>" not found in database.</strong>
            <span class="text-muted ml-1">Add as Customer or Vendor to link this invoice.</span>
        </div>
        <div class="d-flex" style="gap:8px;">
            <button type="button" class="btn btn-sm btn-primary" id="addAsCustomerBtn"><i class="fas fa-user-plus mr-1"></i> Add as Customer</button>
            <button type="button" class="btn btn-sm btn-warning" id="addAsVendorBtn"><i class="fas fa-building mr-1"></i> Add as Vendor</button>
        </div>
    </div>
</div>

{{-- Add Customer Modal --}}
<div id="addCustomerModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#e84393,#fd79a8) !important; border:0;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-user-plus mr-2"></i> Add Customer</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addCustomerForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Name *</label><input type="text" name="name" id="ac-name" class="form-control" required></div>
                        <div class="col-md-6 form-group"><label>Contact Person</label><input type="text" name="contact_person" id="ac-contact" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Phone</label><input type="text" name="phone" id="ac-phone" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Email</label><input type="email" name="email" id="ac-email" class="form-control"></div>
                        <div class="col-12 form-group"><label>Address</label><input type="text" name="address" id="ac-address" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>City</label><input type="text" name="city" id="ac-city" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>State</label><input type="text" name="state" id="ac-state" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>GSTIN</label><input type="text" name="gstin" id="ac-gstin" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>PAN</label><input type="text" name="pan" id="ac-pan" class="form-control"></div>
                        <div class="col-12 mt-2"><hr style="border-color:rgba(232,67,147,0.2);"><small class="text-muted"><i class="fas fa-university mr-1"></i>Bank Details</small></div>
                        <div class="col-md-6 form-group"><label>Bank Name</label><input type="text" name="bank_name" id="ac-bank-name" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Account No.</label><input type="text" name="bank_account_no" id="ac-bank-account" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Branch</label><input type="text" name="bank_branch" id="ac-bank-branch" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>IFSC Code</label><input type="text" name="bank_ifsc" id="ac-bank-ifsc" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Customer</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Vendor Modal --}}
<div id="addVendorModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#f39c12,#e67e22) !important; border:0;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-building mr-2"></i> Add Vendor</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addVendorForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group"><label>Name *</label><input type="text" name="name" id="av-name" class="form-control" required></div>
                        <div class="col-md-6 form-group"><label>Contact Person</label><input type="text" name="contact_person" id="av-contact" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Phone</label><input type="text" name="phone" id="av-phone" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Email</label><input type="email" name="email" id="av-email" class="form-control"></div>
                        <div class="col-12 form-group"><label>Address</label><input type="text" name="address" id="av-address" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>City</label><input type="text" name="city" id="av-city" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>State</label><input type="text" name="state" id="av-state" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>GSTIN</label><input type="text" name="gstin" id="av-gstin" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>PAN</label><input type="text" name="pan" id="av-pan" class="form-control"></div>
                        <div class="col-12 mt-2"><hr style="border-color:rgba(243,156,18,0.2);"><small class="text-muted"><i class="fas fa-university mr-1"></i>Bank Details</small></div>
                        <div class="col-md-6 form-group"><label>Bank Name</label><input type="text" name="bank_name" id="av-bank-name" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Account No.</label><input type="text" name="bank_account_no" id="av-bank-account" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>Branch</label><input type="text" name="bank_branch" id="av-bank-branch" class="form-control"></div>
                        <div class="col-md-6 form-group"><label>IFSC Code</label><input type="text" name="bank_ifsc" id="av-bank-ifsc" class="form-control"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Save Vendor</button></div>
            </form>
        </div>
    </div>
</div>

{{-- Add Product Modal --}}
<div id="addProductModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#00b894,#00cec9) !important; border:0;">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-cube mr-2"></i> Add Product</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="addProductForm">
                <input type="hidden" id="ap-item-index" value="">
                <div class="modal-body">
                    <div class="form-group"><label>Product Name *</label><input type="text" name="name" id="ap-name" class="form-control" required></div>
                    <div class="form-group"><label>HSN Code</label><input type="text" name="hsn_code" id="ap-hsn" class="form-control" placeholder="e.g. 4412"></div>
                    <div class="form-group"><label>Unit (UOM)</label><input type="text" name="unit" id="ap-unit" class="form-control" placeholder="e.g. PCS, KG, LTR" list="ap-unit-list" autocomplete="off"><datalist id="ap-unit-list"><option value="PCS"><option value="NOS"><option value="KG"><option value="KGS"><option value="LTR"><option value="LTRS"><option value="MTR"><option value="PSC"><option value="BAG"><option value="BOX"><option value="SET"><option value="ROLL"><option value="DZN"><option value="PAIR"></datalist></div>
                    <div class="form-group"><label>Sale Rate (&#8377;)</label><input type="number" name="sale_rate" id="ap-rate" class="form-control" step="0.01" min="0" placeholder="0.00"></div>
                    <div class="form-group"><label>GST % (CGST + SGST combined)</label><input type="number" name="gst_percent" id="ap-gst" class="form-control" step="0.01" min="0" max="100" placeholder="e.g. 18"><small class="form-text text-muted">For intrastate: total rate e.g. 18% = 9% CGST + 9% SGST</small></div>
                    <div class="form-group"><label>Initial Stock</label><input type="number" name="stock" id="ap-stock" class="form-control" step="0.001" min="0" placeholder="0"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Save Product</button></div>
            </form>
        </div>
    </div>
</div>

{{-- 2–4. Extracted form --}}
<form id="invoiceForm" class="d-none">
    <input type="hidden" name="customer_id" id="h_customer_id" value="">
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
            <input type="hidden" id="h_vendor_bank_name" class="invoice-header-field" data-field="vendor_bank_name">
            <input type="hidden" id="h_vendor_bank_account_no" class="invoice-header-field" data-field="vendor_bank_account_no">
            <input type="hidden" id="h_vendor_bank_branch" class="invoice-header-field" data-field="vendor_bank_branch">
            <input type="hidden" id="h_vendor_bank_ifsc" class="invoice-header-field" data-field="vendor_bank_ifsc">
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
                        <th>UOM</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Taxable (Qty×Rate)</th>
                        <th class="text-right">GST %</th>
                        <th class="text-right">CGST</th>
                        <th class="text-right">SGST</th>
                        <th class="text-right">Total</th>
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
        const uploadZone = document.getElementById('uploadDropZone');
        const uploadOverlay = document.getElementById('uploadLoadingOverlay');
        const uploadText = document.getElementById('uploadLoadingText');
        if (uploadZone) { uploadZone.style.position = 'relative'; }
        if (uploadOverlay) { uploadOverlay.classList.remove('d-none'); uploadOverlay.style.display = 'flex'; }
        if (uploadText) uploadText.textContent = 'Uploading file…';
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
        if (uploadOverlay) { uploadOverlay.classList.add('d-none'); }
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
            const uploadOverlay = document.getElementById('uploadLoadingOverlay');
            const uploadText = document.getElementById('uploadLoadingText');
            if (uploadOverlay) { uploadOverlay.classList.remove('d-none'); uploadOverlay.style.display = 'flex'; }
            if (uploadText) uploadText.textContent = 'Uploading file…';
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
            if (uploadOverlay) { uploadOverlay.classList.add('d-none'); }
        }, 'image/jpeg', 0.9);
    });

    clearPreviewBtn.addEventListener('click', clearPreview);

    processBtn.addEventListener('click', async function() {
        processError.classList.add('d-none');
        qualityWarning.classList.add('d-none');
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Processing…';
        const processOverlay = document.getElementById('processLoadingOverlay');
        if (processOverlay) { processOverlay.classList.remove('d-none'); processOverlay.style.display = 'flex'; }
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
            await runCheckLookup();
            invoiceForm.classList.remove('d-none');
            invoiceForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } catch (e) {
            if (processErrorText) processErrorText.textContent = e.message || 'Processing failed';
            processError.classList.remove('d-none');
        }
        if (processOverlay) processOverlay.classList.add('d-none');
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
        document.getElementById('h_customer_id').value = '';
        itemsBody.innerHTML = '';
        (data.items || []).forEach((row, i) => addItemRow(row, i));
        if (!(data.items && data.items.length)) addItemRow({}, 0);
        recalcTotals();
    }

    async function runCheckLookup() {
        const partyName = document.getElementById('h_party_name').value.trim();
        const items = [];
        itemsBody.querySelectorAll('tr').forEach((tr, i) => {
            const nameInp = tr.querySelector('input[name="items[' + i + '][product_name]"]');
            const name = (nameInp && nameInp.value) ? nameInp.value.trim() : '';
            items.push({ product_name: name || '' });
        });
        try {
            const r = await fetch('{{ route("ai-invoice.check-lookup") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ party_name: partyName, items: items })
            });
            const data = await r.json();
            const partyBanner = document.getElementById('partyNotFoundBanner');
            if (data.party_found) {
                partyBanner.classList.add('d-none');
                if (data.party_found.type === 'customer') {
                    document.getElementById('h_customer_id').value = data.party_found.id;
                }
            } else if (partyName) {
                document.getElementById('partyNotFoundName').textContent = partyName;
                partyBanner.classList.remove('d-none');
            } else {
                partyBanner.classList.add('d-none');
            }
            (data.product_matches || []).forEach(function(pm) {
                const tr = itemsBody.children[pm.index];
                if (!tr) return;
                const productTd = tr.querySelector('td:first-child');
                if (!productTd) return;
                if (pm.found) {
                    const hid = productTd.querySelector('input[name*="[product_id]"]');
                    if (hid) hid.value = pm.found.id;
                    const addLink = productTd.querySelector('.add-product-link');
                    if (addLink) addLink.remove();
                } else if (productTd.querySelector('input[name^="items"][name$="[product_name]"]').value.trim()) {
                    const addLink = document.createElement('small');
                    addLink.className = 'add-product-link text-success ml-1';
                    addLink.style.cursor = 'pointer';
                    addLink.innerHTML = '<i class="fas fa-plus-circle"></i> Add to products';
                    addLink.dataset.index = pm.index;
                    addLink.addEventListener('click', function() { openAddProductModal(parseInt(this.dataset.index, 10)); });
                    productTd.appendChild(addLink);
                }
            });
        } catch (e) { console.warn('Check lookup failed', e); }
    }

    function parseMangledProductName(str) {
        if (!str || str.length < 15) return { name: str, hsn: '', unit: '', gst: '' };
        var result = { name: str, hsn: '', unit: '', gst: '' };
        var m;
        if ((m = str.match(/\s(\d{4})\s/)) && parseInt(m[1], 10) >= 1000) {
            result.hsn = m[1];
            result.name = result.name.replace(new RegExp('\\s*' + m[1] + '\\s*', 'g'), ' ');
        }
        if ((m = str.match(/\d+\.?\d*(PCS|NOS|KG|KGS|LTR|LTRS|MTR|PSC|BAG|BOX|SET|PAIR|DZN|ROLL|UNIT|UNITS|FT|SQFT|SQM|RMT|CFT|CUM)/i))) {
            result.unit = m[1].toUpperCase();
            result.name = result.name.replace(new RegExp('\\d*\\.?\\d*' + m[1] + '\\s*', 'gi'), ' ');
        }
        if ((m = str.match(/\b(5|12|18|28)\.?0*\b/))) {
            result.gst = m[1];
        }
        result.name = result.name.replace(/\s+\d+\.?\d*\s+\d+\.?\d*(?:\s+\d+\.?\d*)*\s*$/, '').replace(/\s+/g, ' ').trim();
        return result;
    }

    function openAddProductModal(itemIndex) {
        const tr = itemsBody.children[itemIndex];
        if (!tr) return;
        var nameVal = (tr.querySelector('input[name="items[' + itemIndex + '][product_name]"]') || {}).value || '';
        var hsnVal = (tr.querySelector('input[name="items[' + itemIndex + '][hsn_code]"]') || {}).value || '';
        var unitVal = (tr.querySelector('input[name="items[' + itemIndex + '][unit]"]') || {}).value || '';
        var gstVal = (tr.querySelector('input[name="items[' + itemIndex + '][gst_percent]"]') || {}).value || '';
        var rateVal = (tr.querySelector('input[name="items[' + itemIndex + '][rate]"]') || {}).value || '';
        var qtyVal = (tr.querySelector('input[name="items[' + itemIndex + '][quantity]"]') || {}).value || '';
        if (nameVal && nameVal.length > 20 && (!hsnVal || !unitVal || !gstVal)) {
            var parsed = parseMangledProductName(nameVal);
            nameVal = parsed.name;
            if (parsed.hsn && !hsnVal) hsnVal = parsed.hsn;
            if (parsed.unit && !unitVal) unitVal = parsed.unit;
            if (parsed.gst && !gstVal) gstVal = parsed.gst;
        }
        document.getElementById('ap-item-index').value = itemIndex;
        document.getElementById('ap-name').value = nameVal;
        document.getElementById('ap-hsn').value = hsnVal;
        document.getElementById('ap-unit').value = unitVal;
        document.getElementById('ap-rate').value = rateVal;
        document.getElementById('ap-gst').value = gstVal;
        document.getElementById('ap-stock').value = qtyVal;
        $('#addProductModal').modal('show');
    }

    function addItemRow(row = {}, index) {
        const tr = document.createElement('tr');
        const low = (row.confidence !== undefined && row.confidence < 0.7);
        const qty = parseFloat(row.quantity) || 0;
        const rate = parseFloat(row.rate) || 0;
        const gstPct = parseFloat(row.gst_percent) || 0;
        const taxable = Math.round(qty * rate * 100) / 100;
        const gstAmt = gstPct ? Math.round(taxable * (gstPct / 100) * 100) / 100 : 0;
        const cgstAmt = Math.round((gstAmt / 2) * 100) / 100;
        const sgstAmt = Math.round((gstAmt - cgstAmt) * 100) / 100;
        const total = taxable + gstAmt;
        tr.innerHTML = `
            <td><input type="hidden" name="items[${index}][product_id]" value="">
                <input type="text" name="items[${index}][product_name]" value="${escapeAttr(row.product_name || '')}" class="form-control ${low ? 'border-warning' : ''}" placeholder="Product name"></td>
            <td><input type="text" name="items[${index}][hsn_code]" value="${escapeAttr(row.hsn_code || '')}" class="form-control" placeholder="HSN"></td>
            <td><input type="number" name="items[${index}][quantity]" value="${row.quantity ?? ''}" step="0.001" min="0" class="form-control item-qty text-right" placeholder="0"></td>
            <td><input type="text" name="items[${index}][unit]" value="${escapeAttr(row.unit || '')}" class="form-control" placeholder="UOM"></td>
            <td><input type="number" name="items[${index}][rate]" value="${row.rate ?? ''}" step="0.0001" min="0" class="form-control item-rate text-right" placeholder="0.00"></td>
            <td class="text-right item-taxable" style="color:#667eea;font-weight:600;vertical-align:middle;">${taxable ? taxable.toFixed(2) : '-'}</td>
            <td><input type="number" name="items[${index}][gst_percent]" value="${row.gst_percent ?? ''}" step="0.01" min="0" max="100" class="form-control item-gst-pct text-right" placeholder="18"></td>
            <td class="text-right item-cgst-amt" style="vertical-align:middle;">${cgstAmt ? cgstAmt.toFixed(2) : '-'}</td>
            <td class="text-right item-sgst-amt" style="vertical-align:middle;">${sgstAmt ? sgstAmt.toFixed(2) : '-'}</td>
            <td class="text-right item-total" style="font-weight:700;vertical-align:middle;">${total ? total.toFixed(2) : '-'}
                <input type="hidden" name="items[${index}][amount]" value="${row.amount ?? total ?? ''}" class="item-amount-hidden">
            </td>
            <td class="text-center p-2"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="fas fa-trash"></i></button></td>
        `;
        tr.querySelector('.remove-row').addEventListener('click', function() { tr.remove(); recalcTotals(); });
        function recalcRow() {
            const q = parseFloat(tr.querySelector('.item-qty').value) || 0;
            const r = parseFloat(tr.querySelector('.item-rate').value) || 0;
            const g = parseFloat(tr.querySelector('.item-gst-pct').value) || 0;
            const t = Math.round(q * r * 100) / 100;
            const ga = g ? Math.round(t * (g / 100) * 100) / 100 : 0;
            const cgst = Math.round((ga / 2) * 100) / 100;
            const sgst = Math.round((ga - cgst) * 100) / 100;
            const tot = t + ga;
            tr.querySelector('.item-taxable').textContent = t ? t.toFixed(2) : '-';
            tr.querySelector('.item-cgst-amt').textContent = cgst ? cgst.toFixed(2) : '-';
            tr.querySelector('.item-sgst-amt').textContent = sgst ? sgst.toFixed(2) : '-';
            tr.querySelector('.item-total').innerHTML = (tot ? tot.toFixed(2) : '-') + '<input type="hidden" name="items[' + index + '][amount]" value="' + tot + '" class="item-amount-hidden">';
        }
        tr.querySelector('.item-qty').addEventListener('input', function() { recalcRow(); recalcTotals(); });
        tr.querySelector('.item-rate').addEventListener('input', function() { recalcRow(); recalcTotals(); });
        tr.querySelector('.item-gst-pct').addEventListener('input', function() { recalcRow(); recalcTotals(); });
        itemsBody.appendChild(tr);
    }

    function recalcTotals() {
        let taxable = 0; let gstAmt = 0; let net = 0;
        itemsBody.querySelectorAll('tr').forEach(function(tr) {
            const q = parseFloat(tr.querySelector('.item-qty')?.value) || 0;
            const r = parseFloat(tr.querySelector('.item-rate')?.value) || 0;
            const g = parseFloat(tr.querySelector('.item-gst-pct')?.value) || 0;
            const t = Math.round(q * r * 100) / 100;
            const ga = g ? Math.round(t * (g / 100) * 100) / 100 : 0;
            taxable += t; gstAmt += ga; net += t + ga;
        });
        const tTax = document.getElementById('t_taxable_amount');
        const tGst = document.getElementById('t_gst_amount');
        const tCgst = document.getElementById('t_cgst_amount');
        const tSgst = document.getElementById('t_sgst_amount');
        const tNet = document.getElementById('t_net_amount');
        if (tTax) tTax.value = taxable.toFixed(2);
        if (tGst) tGst.value = gstAmt.toFixed(2);
        if (tCgst) tCgst.value = (gstAmt / 2).toFixed(2);
        if (tSgst) tSgst.value = (gstAmt / 2).toFixed(2);
        if (tNet) tNet.value = net.toFixed(2);
    }

    function escapeAttr(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    addItemBtn.addEventListener('click', function() {
        addItemRow({}, itemsBody.children.length);
        recalcTotals();
    });

    document.getElementById('h_party_name').addEventListener('blur', function() {
        if (invoiceForm.classList.contains('d-none')) return;
        runCheckLookup();
    });

    document.getElementById('addAsCustomerBtn').addEventListener('click', function() {
        document.getElementById('ac-name').value = document.getElementById('h_party_name').value || '';
        document.getElementById('ac-city').value = document.getElementById('h_city').value || '';
        document.getElementById('ac-state').value = document.getElementById('h_state').value || '';
        document.getElementById('ac-gstin').value = document.getElementById('h_gstin').value || '';
        $('#addCustomerModal').modal('show');
    });
    document.getElementById('addAsVendorBtn').addEventListener('click', function() {
        document.getElementById('av-name').value = document.getElementById('h_party_name').value || '';
        document.getElementById('av-city').value = document.getElementById('h_city').value || '';
        document.getElementById('av-state').value = document.getElementById('h_state').value || '';
        document.getElementById('av-gstin').value = document.getElementById('h_gstin').value || '';
        document.getElementById('av-bank-name').value = (document.getElementById('h_vendor_bank_name') && document.getElementById('h_vendor_bank_name').value) || '';
        document.getElementById('av-bank-account').value = (document.getElementById('h_vendor_bank_account_no') && document.getElementById('h_vendor_bank_account_no').value) || '';
        document.getElementById('av-bank-branch').value = (document.getElementById('h_vendor_bank_branch') && document.getElementById('h_vendor_bank_branch').value) || '';
        document.getElementById('av-bank-ifsc').value = (document.getElementById('h_vendor_bank_ifsc') && document.getElementById('h_vendor_bank_ifsc').value) || '';
        $('#addVendorModal').modal('show');
    });

    document.getElementById('addCustomerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = { _token: csrf };
        fd.forEach((v, k) => { if (k !== '_token') payload[k] = v; });
        try {
            const r = await fetch('{{ route("ai-invoice.add-customer") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Failed');
            document.getElementById('h_customer_id').value = data.id;
            document.getElementById('partyNotFoundBanner').classList.add('d-none');
            $('#addCustomerModal').modal('hide');
        } catch (err) { alert(err.message || 'Failed to add customer'); }
    });
    document.getElementById('addVendorForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const payload = { _token: csrf };
        fd.forEach((v, k) => { if (k !== '_token') payload[k] = v; });
        try {
            const r = await fetch('{{ route("ai-invoice.add-vendor") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Failed');
            document.getElementById('partyNotFoundBanner').classList.add('d-none');
            $('#addVendorModal').modal('hide');
        } catch (err) { alert(err.message || 'Failed to add vendor'); }
    });
    document.getElementById('addProductForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const payload = {
            _token: csrf,
            name: document.getElementById('ap-name').value,
            hsn_code: document.getElementById('ap-hsn').value || null,
            unit: document.getElementById('ap-unit').value || null,
            sale_rate: document.getElementById('ap-rate').value || 0,
            gst_percent: document.getElementById('ap-gst').value || 0,
            stock: document.getElementById('ap-stock').value || 0
        };
        const itemIndex = parseInt(document.getElementById('ap-item-index').value, 10);
        try {
            const r = await fetch('{{ route("ai-invoice.add-product") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await r.json();
            if (!r.ok) throw new Error(data.message || 'Failed');
            const tr = itemsBody.children[itemIndex];
            if (tr) {
                const hid = tr.querySelector('input[name*="[product_id]"]');
                if (hid) hid.value = data.id;
                const addLink = tr.querySelector('.add-product-link');
                if (addLink) addLink.remove();
            }
            $('#addProductModal').modal('hide');
        } catch (err) { alert(err.message || 'Failed to add product'); }
    });

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
