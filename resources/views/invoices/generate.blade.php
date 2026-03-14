@extends('layouts.app')

@section('title', 'Invoice ' . ($invoice->invoice_number ?? $invoice->id))
@section('header', 'Invoice Preview')

@push('styles')
    <style>
        .invoice-preview-frame {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0;
            color: #111;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .invoice-preview-content {
            padding: 30px;
            min-height: 400px;
        }

        .template-switch {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .tpl-switch-btn {
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px solid var(--border-color);
            background: var(--bg-input);
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .tpl-switch-btn:hover {
            border-color: #667eea;
            color: var(--text-primary);
            text-decoration: none;
        }

        .tpl-switch-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            border-color: #667eea;
        }

        .action-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .invoice-meta-card {
            border-left: 4px solid #667eea;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .meta-row:last-child {
            border-bottom: 0;
        }

        .meta-label {
            color: var(--text-muted);
            font-size: 0.78rem;
        }

        .meta-value {
            font-weight: 600;
            font-size: 0.85rem;
        }
    </style>
@endpush

@section('content')
    {{-- Toast --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close"
                data-dismiss="alert">&times;</button><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
    @endif

    <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-sm mb-3"><i class="fas fa-arrow-left mr-1"></i> Back
        to Invoices</a>

    <div class="row">
        {{-- Left: Invoice Preview --}}
        <div class="col-lg-8">
            {{-- Template Switcher --}}
            <div class="card mb-3">
                <div class="card-body" style="padding:12px 16px;">
                    <small class="text-muted d-block mb-2"
                        style="text-transform:uppercase;letter-spacing:0.5px;font-size:0.68rem;font-weight:600;">Switch
                        Template</small>
                    <div class="template-switch">
                        @foreach ($templates as $tpl)
                            <a href="{{ route('invoices.generate', ['invoice' => $invoice->id, 'template' => $tpl->id]) }}"
                                class="tpl-switch-btn {{ $template && $template->id === $tpl->id ? 'active' : '' }}">
                                {{ $tpl->name }}
                                @if ($tpl->is_default)
                                    <i class="fas fa-star ml-1" style="font-size:0.65rem;"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Action Bar --}}
            <div class="action-bar">
                @if ($template)
                    <a href="{{ route('invoices.print', ['invoice' => $invoice->id, 'template' => $template->id]) }}"
                        class="btn btn-primary" target="_blank">
                        <i class="fas fa-print mr-1"></i> Print
                    </a>
                    <button class="btn btn-success" onclick="downloadPdf()">
                        <i class="fas fa-file-pdf mr-1"></i> Save as PDF
                    </button>
                @endif
                <a href="{{ route('invoices.create') }}" class="btn btn-secondary">
                    <i class="fas fa-plus mr-1"></i> New Invoice
                </a>
            </div>

            {{-- Rendered Invoice --}}
            <div class="invoice-preview-frame">
                <div class="invoice-preview-content" id="invoiceContent">
                    @if ($renderedHtml)
                        {!! $renderedHtml !!}
                    @else
                        <div style="text-align:center;padding:60px 20px;color:#999;">
                            <i class="fas fa-palette" style="font-size:3rem;display:block;margin-bottom:16px;"></i>
                            <h5 style="color:#666;">No Template Selected</h5>
                            <p>Select a template above to preview your invoice.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Invoice Summary --}}
        <div class="col-lg-4">
            <div class="card invoice-meta-card mb-3">
                <div class="card-header" style="padding:12px 16px;">
                    <h6 class="mb-0" style="font-weight:700;"><i class="fas fa-info-circle mr-2"
                            style="color:#667eea;"></i>Invoice Details</h6>
                </div>
                <div class="card-body" style="padding:12px 16px;">
                    <div class="meta-row">
                        <span class="meta-label">Invoice No.</span>
                        <span class="meta-value">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Date</span>
                        <span class="meta-value">{{ $invoice->invoice_date?->format('d M Y') ?? '-' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Customer</span>
                        <span class="meta-value">{{ $invoice->party_name ?? 'Walk-in' }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Doc. Type</span>
                        <span class="meta-value">{{ $invoice->document_type }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Items</span>
                        <span class="meta-value">{{ $invoice->items->count() }}</span>
                    </div>
                    @if ($template)
                        <div class="meta-row">
                            <span class="meta-label">Template</span>
                            <span class="meta-value" style="color:#667eea;">{{ $template->name }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header" style="padding:12px 16px;">
                    <h6 class="mb-0" style="font-weight:700;"><i class="fas fa-calculator mr-2"
                            style="color:#00b894;"></i>Totals</h6>
                </div>
                <div class="card-body" style="padding:12px 16px;">
                    <div class="meta-row">
                        <span class="meta-label">Taxable Amount</span>
                        <span class="meta-value">{{ number_format($invoice->taxable_amount, 2) }}</span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">GST Amount</span>
                        <span class="meta-value">{{ number_format($invoice->gst_amount, 2) }}</span>
                    </div>
                    @if ($invoice->cgst_amount)
                        <div class="meta-row">
                            <span class="meta-label">CGST</span>
                            <span class="meta-value">{{ number_format($invoice->cgst_amount, 2) }}</span>
                        </div>
                    @endif
                    @if ($invoice->sgst_amount)
                        <div class="meta-row">
                            <span class="meta-label">SGST</span>
                            <span class="meta-value">{{ number_format($invoice->sgst_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="meta-row"
                        style="background:rgba(0,184,148,0.1);padding:10px;border-radius:6px;margin-top:8px;">
                        <span class="meta-label" style="font-weight:700;color:var(--text-primary);">Net Amount</span>
                        <span class="meta-value"
                            style="font-size:1.1rem;color:#00b894;">{{ number_format($invoice->net_amount, 2) }}</span>
                    </div>
                    @if ($invoice->advance_amount)
                        <div class="meta-row">
                            <span class="meta-label">Advance</span>
                            <span class="meta-value">{{ number_format($invoice->advance_amount, 2) }}</span>
                        </div>
                    @endif
                    @if ($invoice->balance_amount)
                        <div class="meta-row">
                            <span class="meta-label">Balance</span>
                            <span class="meta-value"
                                style="color:#ff7675;">{{ number_format($invoice->balance_amount, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header" style="padding:12px 16px;">
                    <h6 class="mb-0" style="font-weight:700;"><i class="fas fa-layer-group mr-2"
                            style="color:#667eea;"></i>Tax Slab Totals</h6>
                </div>
                <div class="card-body" style="padding:0;">
                    @php
                        $taxSlabs = [];
                        foreach ($invoice->items as $item) {
                            $qty = (float) $item->quantity;
                            $rate = (float) $item->rate;
                            $gstPct = (float) ($item->gst_percent ?? 0);
                            $taxable = round($qty * $rate, 2);
                            $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
                            $slabKey = rtrim(rtrim(number_format($gstPct, 2, '.', ''), '0'), '.');
                            if ($slabKey === '') {
                                $slabKey = '0';
                            }
                            if (!isset($taxSlabs[$slabKey])) {
                                $taxSlabs[$slabKey] = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0];
                            }
                            $half = round($gstAmt / 2, 2);
                            $taxSlabs[$slabKey]['taxable'] += $taxable;
                            $taxSlabs[$slabKey]['cgst'] += $half;
                            $taxSlabs[$slabKey]['sgst'] += $half;
                        }
                        foreach (['0', '5', '12', '18', '28'] as $stdSlab) {
                            if (!isset($taxSlabs[$stdSlab])) {
                                $taxSlabs[$stdSlab] = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0];
                            }
                        }
                        uksort($taxSlabs, fn($a, $b) => (float) $a <=> (float) $b);
                    @endphp
                    <div class="table-responsive">
                        <table class="table table-sm mb-0" style="font-size:0.78rem;">
                            <thead>
                                <tr>
                                    <th>GST%</th>
                                    <th class="text-right">Taxable</th>
                                    <th class="text-right">CGST</th>
                                    <th class="text-right">SGST</th>
                                    <th class="text-right">IGST</th>
                                    <th class="text-right">Tax Total</th>
                                    <th class="text-right">Gross</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $gTaxable = 0;
                                    $gCgst = 0;
                                    $gSgst = 0;
                                    $gIgst = 0;
                                    $gTax = 0;
                                    $gGross = 0;
                                @endphp
                                @foreach ($taxSlabs as $slab => $row)
                                    @php
                                        $taxTotal = $row['cgst'] + $row['sgst'] + $row['igst'];
                                        $gross = $row['taxable'] + $taxTotal;
                                        $gTaxable += $row['taxable'];
                                        $gCgst += $row['cgst'];
                                        $gSgst += $row['sgst'];
                                        $gIgst += $row['igst'];
                                        $gTax += $taxTotal;
                                        $gGross += $gross;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $slab }}%</strong></td>
                                        <td class="text-right">{{ number_format($row['taxable'], 2) }}</td>
                                        <td class="text-right">{{ number_format($row['cgst'], 2) }}</td>
                                        <td class="text-right">{{ number_format($row['sgst'], 2) }}</td>
                                        <td class="text-right">{{ number_format($row['igst'], 2) }}</td>
                                        <td class="text-right" style="font-weight:600;">{{ number_format($taxTotal, 2) }}
                                        </td>
                                        <td class="text-right" style="font-weight:600;">{{ number_format($gross, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background:rgba(102,126,234,0.1);font-weight:700;">
                                    <td>Grand</td>
                                    <td class="text-right">{{ number_format($gTaxable, 2) }}</td>
                                    <td class="text-right">{{ number_format($gCgst, 2) }}</td>
                                    <td class="text-right">{{ number_format($gSgst, 2) }}</td>
                                    <td class="text-right">{{ number_format($gIgst, 2) }}</td>
                                    <td class="text-right">{{ number_format($gTax, 2) }}</td>
                                    <td class="text-right">{{ number_format($gGross, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="card">
                <div class="card-header" style="padding:12px 16px;">
                    <h6 class="mb-0" style="font-weight:700;"><i class="fas fa-list mr-2"
                            style="color:#a29bfe;"></i>Line Items (Qty × Rate = Item Total)</h6>
                </div>
                <div class="card-body" style="padding:0;">
                    <table class="table table-hover mb-0" style="font-size:0.8rem;">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th class="text-right">Rate</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Taxable</th>
                                <th class="text-right">GST</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $overallTotal = 0; @endphp
                            @foreach ($invoice->items as $i => $item)
                                @php
                                    $taxable = round($item->quantity * $item->rate, 2);
                                    $gstAmt = $item->gst_percent ? round($taxable * ($item->gst_percent / 100), 2) : 0;
                                    $itemTotal = $taxable + $gstAmt;
                                    $overallTotal += $itemTotal;
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $item->product_name ?? '-' }}</td>
                                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity, 3) }}</td>
                                    <td class="text-right" style="color:#667eea;font-weight:600;">
                                        {{ number_format($taxable, 2) }}</td>
                                    <td class="text-right">{{ $gstAmt > 0 ? number_format($gstAmt, 2) : '-' }}</td>
                                    <td class="text-right" style="font-weight:700;">{{ number_format($itemTotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(0,184,148,0.08);font-weight:700;">
                                <td colspan="6" class="text-right" style="font-weight:700;">Overall Total:</td>
                                <td class="text-right" style="font-weight:700;color:#00b894;font-size:0.95rem;">
                                    {{ number_format($overallTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function downloadPdf() {
            // Open print view in new window for Save as PDF
            @if ($template)
                window.open('{{ route('invoices.print', ['invoice' => $invoice->id, 'template' => $template->id]) }}',
                    '_blank');
            @endif
        }
    </script>
@endpush
