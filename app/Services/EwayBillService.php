<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * E-Way Bill data generation (stub).
 * Integrate with GSTN E-Way Bill API when credentials are configured.
 */
class EwayBillService
{
    public function generateData(Invoice $invoice): array
    {
        $invoice->load(['customer', 'items']);
        return [
            'doc_number' => $invoice->doc_number ?? $invoice->invoice_number,
            'doc_date' => $invoice->invoice_date?->format('d/m/Y'),
            'supply_type' => 'O', // Outward
            'sub_supply_type' => 1,
            'doc_type' => 'INV',
            'from_gstin' => config('integrations.eway_bill.gstin'),
            'from_trade_name' => config('app.name'),
            'to_gstin' => $invoice->gstin ?: null,
            'to_trade_name' => $invoice->party_name,
            'total_value' => (float) $invoice->taxable_amount,
            'cgst_value' => (float) ($invoice->cgst_amount ?? 0),
            'sgst_value' => (float) ($invoice->sgst_amount ?? 0),
            'igst_value' => (float) ($invoice->igst_amount ?? 0),
            'total_invoice_value' => (float) $invoice->net_amount,
            'transport_distance' => (int) ($invoice->distance_km ?? 0),
            'vehicle_no' => $invoice->vehicle_number,
            'item_list' => $invoice->items->map(fn ($i) => [
                'product_name' => $i->product_name,
                'hsn_code' => (int) (preg_replace('/\D/', '', $i->hsn_code ?? '0') ?: 0),
                'quantity' => (float) $i->quantity,
                'taxable_value' => (float) ($i->quantity * $i->rate),
            ])->toArray(),
        ];
    }
}
