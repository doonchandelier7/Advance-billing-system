<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * QR code for invoices (URL or data).
 * Use a package like endroid/qr-code or simple URL to invoice/verification page.
 */
class QrCodeService
{
    public function invoiceQrUrl(?\App\Models\Invoice $invoice): string
    {
        if (! $invoice) {
            return '';
        }
        return url('/invoice/verify/' . Str::slug($invoice->invoice_number ?? $invoice->id));
    }

    /**
     * Data string for QR (e.g. for endroid/qr-code to encode).
     */
    public function invoiceQrData(?\App\Models\Invoice $invoice): string
    {
        return $this->invoiceQrUrl($invoice);
    }
}
