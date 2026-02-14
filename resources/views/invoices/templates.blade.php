@extends('layouts.app')

@section('title', 'Invoice Templates')
@section('header', 'Invoice Templates')

@push('styles')
<style>
    .template-card { transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden; }
    .template-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(102,126,234,0.2) !important; }
    .template-card.active-tpl { border: 2px solid #667eea !important; }
    .template-badge { position: absolute; top: 12px; right: 12px; }
    .template-type-tag { font-size: 0.65rem; padding: 3px 8px; border-radius: 20px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .type-tax_invoice { background: rgba(0,184,148,0.15); color: #00b894; }
    .type-proforma { background: rgba(116,185,255,0.15); color: #74b9ff; }
    .type-advance { background: rgba(253,203,110,0.15); color: #fdcb6e; }
    .type-delivery_challan { background: rgba(162,155,254,0.15); color: #a29bfe; }
    .type-credit_note { background: rgba(255,118,117,0.15); color: #ff7675; }
    .type-debit_note { background: rgba(253,121,168,0.15); color: #fd79a8; }
    .preview-box { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; min-height: 300px; color: #111; overflow: auto; max-height: 500px; }
    .placeholder-list { max-height: 250px; overflow-y: auto; }
    .placeholder-list .badge { cursor: pointer; margin: 2px; padding: 5px 10px; font-size: 0.72rem; }
    .placeholder-list .badge:hover { opacity: 0.8; }
    .editor-section { border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; margin-bottom: 16px; background: var(--invoice-section-bg, rgba(255,255,255,0.02)); }
    .editor-section label { font-size: 0.75rem; color: var(--invoice-label-color, rgba(255,255,255,0.5)); text-transform: uppercase; letter-spacing: 0.5px; }
    .editor-section textarea { font-family: 'Courier New', monospace; font-size: 0.82rem; min-height: 120px; }
    .color-swatch { width: 32px; height: 32px; border-radius: 6px; border: 2px solid var(--border-color); cursor: pointer; }
    .tab-btn { padding: 8px 18px; border: 1px solid var(--border-color); background: var(--bg-input); color: var(--text-primary); border-radius: 8px 8px 0 0; cursor: pointer; font-size: 0.82rem; font-weight: 600; }
    .tab-btn.active { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; border-color: #667eea; }
    .empty-state { text-align: center; padding: 60px 20px; }
    .empty-state i { font-size: 3rem; color: var(--text-muted); margin-bottom: 16px; }
</style>
@endpush

@section('content')

{{-- Toast --}}
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert">&times;</button><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
@endif

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Design and manage multiple invoice templates for different document types.</p>
    </div>
    <button class="btn btn-primary" data-toggle="modal" data-target="#templateModal" onclick="resetForm()">
        <i class="fas fa-plus mr-1"></i> New Template
    </button>
</div>

{{-- Template Grid --}}
@if($templates->count() > 0)
<div class="row">
    @foreach($templates as $tpl)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card template-card {{ $tpl->is_default ? 'active-tpl' : '' }}">
            <div class="template-badge">
                <span class="template-type-tag type-{{ $tpl->type }}">{{ $types[$tpl->type] ?? $tpl->type }}</span>
            </div>
            <div class="card-body">
                <h5 style="font-weight:700; margin-bottom:8px;">{{ $tpl->name }}</h5>
                <div class="d-flex align-items-center mb-3" style="gap:8px;">
                    @if($tpl->is_default)
                    <span class="badge badge-success" style="font-size:0.68rem;"><i class="fas fa-star mr-1"></i>Default</span>
                    @endif
                    @if($tpl->is_active)
                    <span class="badge badge-info" style="font-size:0.68rem;">Active</span>
                    @else
                    <span class="badge badge-secondary" style="font-size:0.68rem;">Inactive</span>
                    @endif
                    <span class="text-muted" style="font-size:0.72rem;">v{{ $tpl->version }}</span>
                </div>
                {{-- Mini Preview --}}
                <div style="background:#fff; border-radius:6px; padding:10px; min-height:80px; max-height:100px; overflow:hidden; font-size:8px; color:#333; line-height:1.3; margin-bottom:12px; border:1px solid #eee;">
                    {!! Str::limit(strip_tags($tpl->header_html . ' ' . $tpl->body_html . ' ' . $tpl->footer_html), 200) !!}
                    @if(!$tpl->header_html && !$tpl->body_html)
                    <div style="text-align:center;color:#999;padding:20px 0;">No preview available</div>
                    @endif
                </div>
                {{-- Color swatches --}}
                @if($tpl->colors)
                <div class="d-flex mb-3" style="gap:6px;">
                    @foreach($tpl->colors as $label => $color)
                    <div class="color-swatch" style="background:{{ $color }};width:24px;height:24px;" title="{{ ucfirst($label) }}: {{ $color }}"></div>
                    @endforeach
                </div>
                @endif
                {{-- Actions --}}
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-primary" onclick="editTemplate({{ $tpl->id }}, {{ json_encode($tpl) }})">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <div>
                        <button class="btn btn-sm btn-secondary" onclick="previewTemplate({{ json_encode($tpl) }})">
                            <i class="fas fa-eye mr-1"></i> Preview
                        </button>
                        <form method="POST" action="{{ route('invoices.templates.destroy', $tpl) }}" class="d-inline" onsubmit="return confirm('Delete this template?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card">
    <div class="card-body empty-state">
        <i class="fas fa-palette d-block"></i>
        <h5>No Templates Yet</h5>
        <p class="text-muted">Create your first invoice template to start generating professional invoices.</p>
        <button class="btn btn-primary" data-toggle="modal" data-target="#templateModal" onclick="resetForm()">
            <i class="fas fa-plus mr-1"></i> Create Template
        </button>
    </div>
</div>
@endif

{{-- Template Create/Edit Modal --}}
<div class="modal fade" id="templateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <form method="POST" id="templateForm" action="{{ route('invoices.templates.store') }}">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2); border-radius:14px 14px 0 0;">
                    <h5 class="modal-title" style="color:#fff; font-weight:700;"><i class="fas fa-palette mr-2"></i><span id="modalTitle">New Template</span></h5>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;">&times;</button>
                </div>
                <div class="modal-body" style="max-height:75vh; overflow-y:auto;">
                    <div class="row">
                        {{-- Left: Editor --}}
                        <div class="col-lg-7">
                            <div class="editor-section">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Template Name</label>
                                        <input type="text" name="name" id="tplName" class="form-control form-control-sm" placeholder="e.g. Professional Blue" required>
                                    </div>
                                    <div class="col-md-6 form-group">
                                        <label>Document Type</label>
                                        <select name="type" id="tplType" class="form-control form-control-sm" required>
                                            @foreach($types as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 form-group">
                                        <label>Primary Color</label>
                                        <input type="color" name="colors[primary]" id="tplColorPrimary" class="form-control form-control-sm" value="#667eea" style="height:38px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Secondary Color</label>
                                        <input type="color" name="colors[secondary]" id="tplColorSecondary" class="form-control form-control-sm" value="#764ba2" style="height:38px;">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>Accent Color</label>
                                        <input type="color" name="colors[accent]" id="tplColorAccent" class="form-control form-control-sm" value="#2d3436" style="height:38px;">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="hidden" name="is_default" value="0">
                                            <input type="checkbox" name="is_default" value="1" class="custom-control-input" id="tplDefault">
                                            <label class="custom-control-label" for="tplDefault">Set as Default</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-switch mb-2">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" value="1" class="custom-control-input" id="tplActive" checked>
                                            <label class="custom-control-label" for="tplActive">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Editor Tabs --}}
                            <div class="d-flex mb-0">
                                <button type="button" class="tab-btn active" data-tab="header" onclick="switchTab('header',this)">Header</button>
                                <button type="button" class="tab-btn" data-tab="body" onclick="switchTab('body',this)">Body</button>
                                <button type="button" class="tab-btn" data-tab="footer" onclick="switchTab('footer',this)">Footer</button>
                            </div>
                            <div class="editor-section" style="border-radius:0 10px 10px 10px; margin-top:0;">
                                <div id="tab-header">
                                    <label>Header HTML</label>
                                    <textarea name="header_html" id="tplHeader" class="form-control" rows="8" placeholder="<div>...header...</div>"></textarea>
                                </div>
                                <div id="tab-body" style="display:none;">
                                    <label>Body HTML (Items Table)</label>
                                    <textarea name="body_html" id="tplBody" class="form-control" rows="8" placeholder="<table>...@{{items_rows}}...</table>"></textarea>
                                </div>
                                <div id="tab-footer" style="display:none;">
                                    <label>Footer HTML</label>
                                    <textarea name="footer_html" id="tplFooter" class="form-control" rows="8" placeholder="<div>...footer...</div>"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Preview + Placeholders --}}
                        <div class="col-lg-5">
                            <div class="editor-section">
                                <label class="mb-2">Available Placeholders <small class="text-muted">(click to copy)</small></label>
                                <div class="placeholder-list">
                                    @php $placeholders = \App\Services\InvoiceTemplateBindingService::availablePlaceholders(); @endphp
                                    @foreach($placeholders as $key => $desc)
                                    <span class="badge badge-secondary" onclick="copyPlaceholder('{{ $key }}')" title="{{ $desc }}">@{{ '{{' . $key . '}}' }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="editor-section">
                                <label class="mb-2">Live Preview</label>
                                <button type="button" class="btn btn-sm btn-secondary float-right" onclick="refreshPreview()" style="margin-top:-4px;"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
                                <div class="clearfix"></div>
                                <div class="preview-box mt-2" id="livePreview">
                                    <p style="color:#999; text-align:center; padding:40px 0;">Start editing to see preview...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> <span id="submitBtnText">Save Template</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye mr-2"></i>Template Preview</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="preview-box" id="fullPreview" style="max-height:600px;"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['header','body','footer'].forEach(t => {
        document.getElementById('tab-'+t).style.display = t === tab ? '' : 'none';
    });
}

function resetForm() {
    var form = document.getElementById('templateForm');
    form.action = '{{ route("invoices.templates.store") }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('modalTitle').textContent = 'New Template';
    document.getElementById('submitBtnText').textContent = 'Save Template';
    form.reset();
    document.getElementById('tplActive').checked = true;
    document.getElementById('tplColorPrimary').value = '#667eea';
    document.getElementById('tplColorSecondary').value = '#764ba2';
    document.getElementById('tplColorAccent').value = '#2d3436';
    document.getElementById('livePreview').innerHTML = '<p style="color:#999;text-align:center;padding:40px 0;">Start editing to see preview...</p>';
}

function editTemplate(id, tpl) {
    var form = document.getElementById('templateForm');
    form.action = '/invoices/templates/' + id;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('modalTitle').textContent = 'Edit Template';
    document.getElementById('submitBtnText').textContent = 'Update Template';
    document.getElementById('tplName').value = tpl.name || '';
    document.getElementById('tplType').value = tpl.type || 'tax_invoice';
    document.getElementById('tplHeader').value = tpl.header_html || '';
    document.getElementById('tplBody').value = tpl.body_html || '';
    document.getElementById('tplFooter').value = tpl.footer_html || '';
    document.getElementById('tplDefault').checked = !!tpl.is_default;
    document.getElementById('tplActive').checked = tpl.is_active !== false;
    if (tpl.colors) {
        document.getElementById('tplColorPrimary').value = tpl.colors.primary || '#667eea';
        document.getElementById('tplColorSecondary').value = tpl.colors.secondary || '#764ba2';
        document.getElementById('tplColorAccent').value = tpl.colors.accent || '#2d3436';
    }
    refreshPreview();
    $('#templateModal').modal('show');
}

function refreshPreview() {
    var header = document.getElementById('tplHeader').value;
    var body = document.getElementById('tplBody').value;
    var footer = document.getElementById('tplFooter').value;
    var combined = header + '\n' + body + '\n' + footer;

    // Replace placeholders with sample data
    var bd = 'border:1px solid #000;';
    var sampleData = {
        'invoice_number': 'INV-2026-0001',
        'doc_number': 'INV-2026-0001',
        'invoice_date': '09/02/2026',
        'document_type': 'TAX INVOICE',
        'party_name': 'Nanak Sai Builders',
        'customer_name': 'Nanak Sai Builders',
        'customer_address': 'Mumbai, Maharashtra',
        'city': 'Mumbai',
        'state': 'Maharashtra',
        'buyer_state_code': '27',
        'gstin': '27AABCU9603R1ZM',
        'place_of_supply': 'Maharashtra',
        'payment_mode': 'CREDIT',
        'transport_name': 'Fast Transport',
        'vehicle_number': 'MH-12-AB-1234',
        'driver_name': 'Rajesh Kumar',
        'gr_number': 'GR-001',
        'gr_date': '09/02/2026',
        'eway_bill_no': 'EWB-123456789',
        'distance_km': '150',
        'taxable_amount': '10,111.00',
        'gst_amount': '1,820.00',
        'cgst_amount': '910.00',
        'sgst_amount': '910.00',
        'igst_amount': '0.00',
        'net_amount': '11,931.00',
        'advance_amount': '0.00',
        'balance_amount': '11,931.00',
        'amount_in_words': 'Eleven Thousand Nine Hundred and Thirty One Rupees Only.',
        'items_count': '3',
        'total_qty': '1,120.1000',
        'total_quantity': '1,120.1000',
        'total_taxable': '10,111.00',
        'total_sgst': '910.00',
        'total_cgst': '910.00',
        'total_gross': '11,931.00',
        'company_name': '{{ config("app.name") }}',
        'current_date': '09/02/2026',
        'notes': '',
        'seller_name': '{{ config("app.name") }}',
        'seller_address': 'S.C.F. 2047-48-49, Aerodale Market',
        'seller_address_2': 'Airport Road, Sector 123, Mohali',
        'seller_city': 'Mohali',
        'seller_state': 'Punjab',
        'seller_state_code': '03',
        'seller_gstin': '03AUCPK2095F1ZZ',
        'seller_contact': '9815627357',
        'seller_email': 'info@example.com',
        'seller_pan': 'AUCPK2095F',
        'bank_account_no': '14841131000605',
        'bank_name': 'Punjab National Bank',
        'bank_branch': 'Balongi, Distt. Sas Nagar',
        'bank_ifsc': 'PUNB0148410',
        'items_rows': '<tr><td style="text-align:center;">1</td><td>Premium Widget A</td><td style="text-align:center;">8471</td><td style="text-align:right;">5.000</td><td style="text-align:center;">PCS</td><td style="text-align:right;">2,000.00</td><td style="text-align:right;">18.0%</td><td style="text-align:right;font-weight:600;">11,800.00</td></tr><tr><td style="text-align:center;">2</td><td>Standard Part B</td><td style="text-align:center;">8472</td><td style="text-align:right;">5.000</td><td style="text-align:center;">KG</td><td style="text-align:right;">1,500.00</td><td style="text-align:right;">18.0%</td><td style="text-align:right;font-weight:600;">8,850.00</td></tr><tr><td style="text-align:center;">3</td><td>Accessory Pack C</td><td style="text-align:center;">8473</td><td style="text-align:right;">5.000</td><td style="text-align:center;">PCS</td><td style="text-align:right;">1,500.00</td><td style="text-align:right;">18.0%</td><td style="text-align:right;font-weight:600;">8,850.00</td></tr>',
        'items_rows_gst_split': '<tr><td style="'+bd+'padding:4px 6px;text-align:center;">1</td><td style="'+bd+'padding:4px 6px;">Nailse ()</td><td style="'+bd+'padding:4px 6px;text-align:center;">7317</td><td style="'+bd+'padding:4px 6px;text-align:right;">32.1000</td><td style="'+bd+'padding:4px 6px;text-align:center;">KGS</td><td style="'+bd+'padding:4px 6px;text-align:right;">60.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">1,926.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">18.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">173.34</td><td style="'+bd+'padding:4px 6px;text-align:right;">173.34</td><td style="'+bd+'padding:4px 6px;text-align:right;font-weight:700;">2,272.68</td></tr><tr><td style="'+bd+'padding:4px 6px;text-align:center;">2</td><td style="'+bd+'padding:4px 6px;">Screw Gypsum ()</td><td style="'+bd+'padding:4px 6px;text-align:center;">7318</td><td style="'+bd+'padding:4px 6px;text-align:right;">1,078.0000</td><td style="'+bd+'padding:4px 6px;text-align:center;">PCS</td><td style="'+bd+'padding:4px 6px;text-align:right;">0.45</td><td style="'+bd+'padding:4px 6px;text-align:right;">485.10</td><td style="'+bd+'padding:4px 6px;text-align:right;">18.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">43.66</td><td style="'+bd+'padding:4px 6px;text-align:right;">43.66</td><td style="'+bd+'padding:4px 6px;text-align:right;font-weight:700;">572.42</td></tr><tr><td style="'+bd+'padding:4px 6px;text-align:center;">3</td><td style="'+bd+'padding:4px 6px;">Ply 18MM ()</td><td style="'+bd+'padding:4px 6px;text-align:center;">4412</td><td style="'+bd+'padding:4px 6px;text-align:right;">6.0000</td><td style="'+bd+'padding:4px 6px;text-align:center;">PCS</td><td style="'+bd+'padding:4px 6px;text-align:right;">550.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">3,300.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">18.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">297.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">297.00</td><td style="'+bd+'padding:4px 6px;text-align:right;font-weight:700;">3,894.00</td></tr><tr><td style="'+bd+'padding:4px 6px;text-align:center;">4</td><td style="'+bd+'padding:4px 6px;">MDF 11MM ()</td><td style="'+bd+'padding:4px 6px;text-align:center;">4411</td><td style="'+bd+'padding:4px 6px;text-align:right;">4.0000</td><td style="'+bd+'padding:4px 6px;text-align:center;">PCS</td><td style="'+bd+'padding:4px 6px;text-align:right;">1,100.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">4,400.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">18.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">396.00</td><td style="'+bd+'padding:4px 6px;text-align:right;">396.00</td><td style="'+bd+'padding:4px 6px;text-align:right;font-weight:700;">5,192.00</td></tr>' + Array(7).fill('<tr>' + Array(11).fill('<td style="'+bd+'padding:4px 6px;">&nbsp;</td>').join('') + '</tr>').join(''),
        'tax_slab_rows': '<tr><td style="'+bd+'padding:3px 6px;font-weight:700;">0%</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td></tr><tr><td style="'+bd+'padding:3px 6px;font-weight:700;">5%</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td></tr><tr><td style="'+bd+'padding:3px 6px;font-weight:700;">12%</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td></tr><tr><td style="'+bd+'padding:3px 6px;font-weight:700;">18%</td><td style="'+bd+'padding:3px 6px;text-align:right;">10,111.1</td><td style="'+bd+'padding:3px 6px;text-align:right;">910</td><td style="'+bd+'padding:3px 6px;text-align:right;">910</td></tr><tr><td style="'+bd+'padding:3px 6px;font-weight:700;">28%</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td><td style="'+bd+'padding:3px 6px;text-align:right;">0</td></tr>'
    };
    for (var key in sampleData) {
        combined = combined.split('{{' + key + '}}').join(sampleData[key]);
    }
    document.getElementById('livePreview').innerHTML = combined || '<p style="color:#999;text-align:center;padding:40px 0;">Empty template</p>';
}

function previewTemplate(tpl) {
    var combined = (tpl.header_html || '') + '\n' + (tpl.body_html || '') + '\n' + (tpl.footer_html || '');
    document.getElementById('fullPreview').innerHTML = combined || '<p style="color:#999;text-align:center;">No content</p>';
    $('#previewModal').modal('show');
}

function copyPlaceholder(key) {
    var text = '{{' + key + '}}';
    // Find the active textarea
    var visible = document.querySelector('#tab-header:not([style*="display: none"]) textarea, #tab-header:not([style*="display:none"]) textarea') ||
                  document.querySelector('#tab-body:not([style*="display: none"]) textarea, #tab-body:not([style*="display:none"]) textarea') ||
                  document.querySelector('#tab-footer:not([style*="display: none"]) textarea, #tab-footer:not([style*="display:none"]) textarea');
    if (visible) {
        var start = visible.selectionStart;
        var end = visible.selectionEnd;
        visible.value = visible.value.substring(0, start) + text + visible.value.substring(end);
        visible.selectionStart = visible.selectionEnd = start + text.length;
        visible.focus();
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(text);
    }
}

// Auto-refresh preview on typing
['tplHeader','tplBody','tplFooter'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('input', function() {
            clearTimeout(window._previewTimer);
            window._previewTimer = setTimeout(refreshPreview, 300);
        });
    }
});
</script>
@endpush
