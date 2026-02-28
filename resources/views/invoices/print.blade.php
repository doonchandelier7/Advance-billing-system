<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number ?? $invoice->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; font-size: 12px; color: #111; max-width: 800px; margin: 0 auto; padding: 0; }
        @media screen {
            body { padding: 20px; }
            .no-print-bar { display: flex; justify-content: center; gap: 12px; padding: 16px; margin-bottom: 20px; background: #f5f5f5; border-radius: 8px; }
            .no-print-bar button { padding: 10px 24px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; }
            .no-print-bar .btn-print { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
            .no-print-bar .btn-back { background: #e0e0e0; color: #333; }
        }
        @media print {
            body { padding: 0; margin: 0; }
            .no-print-bar { display: none !important; }
        }
        .invoice-rendered { line-height: 1.5; }
        .invoice-rendered table { border-collapse: collapse; }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <button class="btn-back" onclick="window.history.back()"><i class="fas fa-arrow-left"></i> Back</button>
        <button class="btn-print" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="invoice-rendered">
        @if($renderedHtml)
            {!! $renderedHtml !!}
        @else
            {{-- Fallback: basic invoice if no template --}}
            <h1 style="font-size:18px;margin-bottom:24px;">{{ config('app.name') }} - Invoice</h1>
            <table style="width:100%;margin-bottom:16px;">
                <tr><td><strong>Invoice No.</strong></td><td>{{ $invoice->invoice_number }}</td></tr>
                <tr><td><strong>Date</strong></td><td>{{ $invoice->invoice_date?->format('d-m-Y') ?? '-' }}</td></tr>
                <tr><td><strong>Customer</strong></td><td>{{ $invoice->party_name ?? '-' }}</td></tr>
            </table>
            <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="border:1px solid #ddd;padding:8px;">#</th>
                        <th style="border:1px solid #ddd;padding:8px;">Product</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Rate</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Qty</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Taxable (Qty×Rate)</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">GST%</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">GST Amt</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $overallTotal = 0; @endphp
                    @foreach($invoice->items as $i => $item)
                    @php
                        $taxable = round($item->quantity * $item->rate, 2);
                        $gstAmt = $item->gst_percent ? round($taxable * ($item->gst_percent / 100), 2) : 0;
                        $itemTotal = $taxable + $gstAmt;
                        $overallTotal += $itemTotal;
                    @endphp
                    <tr>
                        <td style="border:1px solid #ddd;padding:8px;">{{ $i+1 }}</td>
                        <td style="border:1px solid #ddd;padding:8px;">{{ $item->product_name ?? '-' }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($item->rate,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($item->quantity,3) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;font-weight:600;">{{ number_format($taxable,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ $item->gst_percent ? number_format($item->gst_percent,1).'%' : '-' }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ $gstAmt > 0 ? number_format($gstAmt,2) : '-' }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;font-weight:700;">{{ number_format($itemTotal,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f5f5f5;font-weight:700;">
                        <td colspan="7" style="border:1px solid #ddd;padding:8px;text-align:right;">Overall Total:</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;font-size:1.1em;">{{ number_format($overallTotal,2) }}</td>
                    </tr>
                </tfoot>
            </table>
            @php
                $taxSlabs = [];
                foreach ($invoice->items as $item) {
                    $qty = (float) $item->quantity;
                    $rate = (float) $item->rate;
                    $gstPct = (float) ($item->gst_percent ?? 0);
                    $taxable = round($qty * $rate, 2);
                    $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
                    $slabKey = rtrim(rtrim(number_format($gstPct, 2, '.', ''), '0'), '.');
                    if ($slabKey === '') { $slabKey = '0'; }
                    if (!isset($taxSlabs[$slabKey])) {
                        $taxSlabs[$slabKey] = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0];
                    }
                    $half = round($gstAmt / 2, 2);
                    $taxSlabs[$slabKey]['taxable'] += $taxable;
                    $taxSlabs[$slabKey]['cgst'] += $half;
                    $taxSlabs[$slabKey]['sgst'] += $half;
                }
                foreach (['0','5','12','18','28'] as $stdSlab) {
                    if (!isset($taxSlabs[$stdSlab])) {
                        $taxSlabs[$stdSlab] = ['taxable' => 0, 'cgst' => 0, 'sgst' => 0, 'igst' => 0];
                    }
                }
                uksort($taxSlabs, fn($a, $b) => (float)$a <=> (float)$b);
            @endphp
            <table style="width:100%;border-collapse:collapse;margin:16px 0;">
                <thead>
                    <tr style="background:#f5f5f5;">
                        <th style="border:1px solid #ddd;padding:8px;">GST%</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Taxable</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">CGST</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">SGST</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">IGST</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Tax Total</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Gross</th>
                    </tr>
                </thead>
                <tbody>
                    @php $gTaxable=0; $gCgst=0; $gSgst=0; $gIgst=0; $gTax=0; $gGross=0; @endphp
                    @foreach($taxSlabs as $slab => $row)
                        @php
                            $taxTotal = $row['cgst'] + $row['sgst'] + $row['igst'];
                            $gross = $row['taxable'] + $taxTotal;
                            $gTaxable += $row['taxable']; $gCgst += $row['cgst']; $gSgst += $row['sgst']; $gIgst += $row['igst']; $gTax += $taxTotal; $gGross += $gross;
                        @endphp
                        <tr>
                            <td style="border:1px solid #ddd;padding:8px;font-weight:700;">{{ $slab }}%</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($row['taxable'],2) }}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($row['cgst'],2) }}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($row['sgst'],2) }}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($row['igst'],2) }}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;font-weight:600;">{{ number_format($taxTotal,2) }}</td>
                            <td style="border:1px solid #ddd;padding:8px;text-align:right;font-weight:600;">{{ number_format($gross,2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background:#f5f5f5;font-weight:700;">
                        <td style="border:1px solid #ddd;padding:8px;">Grand Total</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($gTaxable,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($gCgst,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($gSgst,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($gIgst,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($gTax,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;font-size:1.1em;">{{ number_format($gGross,2) }}</td>
                    </tr>
                </tfoot>
            </table>
            <p><strong>Net Amount:</strong> {{ number_format($invoice->net_amount, 2) }}</p>
        @endif
    </div>

    <script>
        @if($renderedHtml)
        window.onload = function() { window.print(); };
        @endif
    </script>
</body>
</html>
