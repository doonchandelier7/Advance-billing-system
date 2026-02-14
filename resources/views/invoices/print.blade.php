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
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Qty</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Rate</th>
                        <th style="border:1px solid #ddd;padding:8px;text-align:right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $i => $item)
                    <tr>
                        <td style="border:1px solid #ddd;padding:8px;">{{ $i+1 }}</td>
                        <td style="border:1px solid #ddd;padding:8px;">{{ $item->product_name ?? '-' }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($item->quantity,3) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($item->rate,2) }}</td>
                        <td style="border:1px solid #ddd;padding:8px;text-align:right;">{{ number_format($item->amount,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
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
