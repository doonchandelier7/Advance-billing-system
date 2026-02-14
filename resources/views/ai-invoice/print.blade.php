<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print — Invoice {{ $invoice->doc_number ?? $invoice->id }}</title>
    <style>
        body { font-family: system-ui, sans-serif; font-size: 12px; color: #111; max-width: 800px; margin: 0 auto; padding: 24px; }
        h1 { font-size: 18px; margin-bottom: 24px; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #ddd; padding: 8px 12px; text-align: left; }
        th { background: #f5f5f5; }
        .header-table td { border: none; padding: 4px 12px 4px 0; }
        .text-right { text-align: right; }
        .totals { margin-top: 24px; }
        .totals table { width: 320px; margin-left: auto; }
        @media print { body { padding: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <p class="no-print"><a href="{{ route('ai-invoice.index') }}">← Back to AI Invoice</a> &nbsp; <button onclick="window.print()">Print / Save as PDF</button></p>
    <h1>{{ config('app.name') }} — Invoice</h1>
    <table class="header-table">
        <tr><td><strong>Document Type</strong></td><td>{{ $invoice->document_type ?? '—' }}</td></tr>
        <tr><td><strong>Invoice / Doc No.</strong></td><td>{{ $invoice->doc_number ?? '—' }}</td></tr>
        <tr><td><strong>Date</strong></td><td>{{ $invoice->invoice_date?->format('d-m-Y') ?? '—' }}</td></tr>
        <tr><td><strong>Party / Customer</strong></td><td>{{ $invoice->party_name ?? '—' }}</td></tr>
        <tr><td><strong>City</strong></td><td>{{ $invoice->city ?? '—' }}</td></tr>
        <tr><td><strong>State</strong></td><td>{{ $invoice->state ?? '—' }}</td></tr>
        <tr><td><strong>GSTIN</strong></td><td>{{ $invoice->gstin ?? '—' }}</td></tr>
        <tr><td><strong>Place of Supply</strong></td><td>{{ $invoice->place_of_supply ?? '—' }}</td></tr>
        <tr><td><strong>Transport</strong></td><td>{{ $invoice->transport_name ?? '—' }}</td></tr>
        <tr><td><strong>Vehicle No.</strong></td><td>{{ $invoice->vehicle_number ?? '—' }}</td></tr>
        <tr><td><strong>Driver</strong></td><td>{{ $invoice->driver_name ?? '—' }}</td></tr>
        <tr><td><strong>E‑Way Bill No.</strong></td><td>{{ $invoice->eway_bill_no ?? '—' }}</td></tr>
        <tr><td><strong>Distance (KM)</strong></td><td>{{ $invoice->distance_km ?? '—' }}</td></tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product / Description</th>
                <th>HSN</th>
                <th class="text-right">Qty</th>
                <th>Unit</th>
                <th class="text-right">Rate</th>
                <th class="text-right">GST %</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_name ?? '—' }}</td>
                <td>{{ $item->hsn_code ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->quantity, 3) }}</td>
                <td>{{ $item->unit ?? '—' }}</td>
                <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                <td class="text-right">{{ $item->gst_percent ? number_format($item->gst_percent, 1).'%' : '—' }}</td>
                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="totals">
        <table>
            <tr><td>Taxable Amount</td><td class="text-right">{{ number_format($invoice->taxable_amount, 2) }}</td></tr>
            <tr><td>GST Amount</td><td class="text-right">{{ number_format($invoice->gst_amount, 2) }}</td></tr>
            @if($invoice->cgst_amount)<tr><td>CGST</td><td class="text-right">{{ number_format($invoice->cgst_amount, 2) }}</td></tr>@endif
            @if($invoice->sgst_amount)<tr><td>SGST</td><td class="text-right">{{ number_format($invoice->sgst_amount, 2) }}</td></tr>@endif
            @if($invoice->igst_amount)<tr><td>IGST</td><td class="text-right">{{ number_format($invoice->igst_amount, 2) }}</td></tr>@endif
            <tr><td><strong>Net Amount</strong></td><td class="text-right"><strong>{{ number_format($invoice->net_amount, 2) }}</strong></td></tr>
            @if($invoice->advance_amount !== null)<tr><td>Advance</td><td class="text-right">{{ number_format($invoice->advance_amount, 2) }}</td></tr>@endif
            @if($invoice->balance_amount !== null)<tr><td>Balance</td><td class="text-right">{{ number_format($invoice->balance_amount, 2) }}</td></tr>@endif
        </table>
    </div>
</body>
</html>
