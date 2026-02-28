<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Setting;

class InvoiceTemplateBindingService
{
    /**
     * Bind invoice data to template HTML. Replace placeholders like {{invoice_number}}, {{customer_name}}, etc.
     */
    public function bind(Invoice $invoice, InvoiceTemplate $template): string
    {
        if (!$invoice->relationLoaded('customer') || !$invoice->relationLoaded('items')) {
            $invoice->load(['customer', 'items.product']);
        }
        $data = $this->invoiceToPlaceholders($invoice);
        $html = ($template->header_html ?? '') . "\n" . ($template->body_html ?? '') . "\n" . ($template->footer_html ?? '');
        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', (string) $value, $html);
        }
        return $html;
    }

    /**
     * Get all available placeholders with descriptions.
     */
    public static function availablePlaceholders(): array
    {
        return [
            // Invoice
            'invoice_number' => 'Invoice number (e.g. INV-2026-0001)',
            'doc_number' => 'Document number',
            'invoice_date' => 'Invoice date (dd-mm-yyyy)',
            'document_type' => 'Document type (Tax Invoice, Proforma, etc.)',
            'payment_mode' => 'Payment mode (Cash, Credit, UPI, etc.)',

            // Buyer / Customer
            'party_name' => 'Party / Customer name',
            'customer_name' => 'Same as party_name',
            'customer_address' => 'Customer full address (city, state)',
            'city' => 'Buyer city',
            'district' => 'Buyer district',
            'state' => 'Buyer state',
            'gstin' => 'Buyer GSTIN number',
            'buyer_state_code' => 'Buyer state code (from GSTIN)',
            'place_of_supply' => 'Place of supply',

            // Transport
            'transport_name' => 'Transport name',
            'vehicle_number' => 'Vehicle number',
            'driver_name' => 'Driver name',
            'gr_number' => 'GR number',
            'gr_date' => 'GR date',
            'eway_bill_no' => 'E-Way Bill number',
            'distance_km' => 'Distance in KMs',

            // Amounts
            'taxable_amount' => 'Total taxable amount',
            'gst_amount' => 'Total GST amount',
            'cgst_amount' => 'CGST amount',
            'sgst_amount' => 'SGST amount',
            'igst_amount' => 'IGST amount',
            'net_amount' => 'Net payable amount',
            'advance_amount' => 'Advance paid',
            'balance_amount' => 'Balance due',
            'amount_in_words' => 'Net amount in words',
            'total_qty' => 'Total quantity of all items',
            'total_taxable' => 'Same as taxable_amount',
            'total_sgst' => 'Same as sgst_amount',
            'total_cgst' => 'Same as cgst_amount',
            'total_gross' => 'Same as net_amount',

            // Items
            'items_rows' => 'HTML table rows (#, Product, HSN, Qty, Unit, Rate, Taxable(Qty×Rate), Tax%, GST Amt, Total + Grand Total row)',
            'items_rows_gst_split' => 'HTML table rows (SR, Desc, HSN, Qty, UOM, Price, Taxable, GST%, SGST, CGST, G.Amount + Grand Total row)',
            'items_count' => 'Number of line items',
            'tax_slab_rows' => 'Tax slab breakdown rows (GST%, taxable, SGST, CGST, IGST, tax total, gross total + grand row)',

            // Seller / Company (from Settings)
            'seller_name' => 'Seller company name (from settings)',
            'seller_address' => 'Seller address line 1 (from settings)',
            'seller_address_2' => 'Seller address line 2 (from settings)',
            'seller_city' => 'Seller city (from settings)',
            'seller_state' => 'Seller state (from settings)',
            'seller_state_code' => 'Seller state code (from settings)',
            'seller_gstin' => 'Seller GSTIN (from settings)',
            'seller_contact' => 'Seller contact number (from settings)',
            'seller_email' => 'Seller email (from settings)',
            'seller_pan' => 'Seller PAN (from settings)',

            // Buyer Bank (from invoice)
            'buyer_bank_name' => 'Buyer bank name',
            'buyer_bank_account_no' => 'Buyer bank account number',
            'buyer_bank_branch' => 'Buyer bank branch name',
            'buyer_bank_ifsc' => 'Buyer bank IFSC code',

            // Seller Bank (from Settings)
            'bank_account_no' => 'Seller bank account number (from settings)',
            'bank_name' => 'Seller bank name (from settings)',
            'bank_branch' => 'Seller bank branch name (from settings)',
            'bank_ifsc' => 'Seller bank IFSC code (from settings)',

            // System
            'company_name' => 'Company name from config',
            'current_date' => 'Current date',
            'notes' => 'Invoice notes',
        ];
    }

    protected function invoiceToPlaceholders(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $customerAddress = implode(', ', array_filter([
            $invoice->city ?? $customer?->city,
            $invoice->district ?? $customer?->district,
            $invoice->state ?? $customer?->state,
        ]));

        $buyerGstin = $invoice->gstin ?? $customer?->gstin ?? '';
        $buyerStateCode = $buyerGstin && strlen($buyerGstin) >= 2 ? substr($buyerGstin, 0, 2) : '';

        // Seller / Bank from Settings
        $sellerName = $this->setting('seller_name', config('app.name'));
        $sellerAddress = $this->setting('seller_address', '');
        $sellerAddress2 = $this->setting('seller_address_2', '');
        $sellerCity = $this->setting('seller_city', '');
        $sellerState = $this->setting('seller_state', '');
        $sellerStateCode = $this->setting('seller_state_code', '');
        $sellerGstin = $this->setting('seller_gstin', '');
        $sellerContact = $this->setting('seller_contact', '');
        $sellerEmail = $this->setting('seller_email', '');
        $sellerPan = $this->setting('seller_pan', '');
        $bankAccountNo = $this->setting('bank_account_no', '');
        $bankName = $this->setting('bank_name', '');
        $bankBranch = $this->setting('bank_branch', '');
        $bankIfsc = $this->setting('bank_ifsc', '');

        return [
            // Invoice
            'invoice_number' => $invoice->invoice_number ?? $invoice->doc_number,
            'doc_number' => $invoice->doc_number,
            'invoice_date' => $invoice->invoice_date?->format('d/m/Y') ?? '',
            'document_type' => $invoice->document_type ?? '',
            'payment_mode' => $invoice->payment_mode ?? '',

            // Buyer
            'party_name' => $invoice->party_name ?? $customer?->name ?? '',
            'customer_name' => $invoice->party_name ?? $customer?->name ?? '',
            'customer_address' => $customerAddress,
            'city' => $invoice->city ?? $customer?->city ?? '',
            'district' => $invoice->district ?? $customer?->district ?? '',
            'state' => $invoice->state ?? $customer?->state ?? '',
            'gstin' => $buyerGstin,
            'buyer_state_code' => $buyerStateCode,
            'place_of_supply' => $invoice->place_of_supply ?? '',

            // Transport
            'transport_name' => $invoice->transport_name ?? '',
            'vehicle_number' => $invoice->vehicle_number ?? '',
            'driver_name' => $invoice->driver_name ?? '',
            'gr_number' => $invoice->gr_number ?? '',
            'gr_date' => $invoice->gr_date?->format('d/m/Y') ?? '',
            'eway_bill_no' => $invoice->eway_bill_no ?? '',
            'distance_km' => $invoice->distance_km ?? '',

            // Amounts
            'taxable_amount' => number_format((float) $invoice->taxable_amount, 2),
            'gst_amount' => number_format((float) $invoice->gst_amount, 2),
            'cgst_amount' => $invoice->cgst_amount ? number_format((float) $invoice->cgst_amount, 2) : '0.00',
            'sgst_amount' => $invoice->sgst_amount ? number_format((float) $invoice->sgst_amount, 2) : '0.00',
            'igst_amount' => $invoice->igst_amount ? number_format((float) $invoice->igst_amount, 2) : '0.00',
            'net_amount' => number_format((float) $invoice->net_amount, 2),
            'advance_amount' => $invoice->advance_amount ? number_format((float) $invoice->advance_amount, 2) : '0.00',
            'balance_amount' => $invoice->balance_amount ? number_format((float) $invoice->balance_amount, 2) : '0.00',
            'amount_in_words' => $this->amountInWords((float) $invoice->net_amount),
            'total_qty' => number_format($invoice->items->sum('quantity'), 4),
            'total_taxable' => number_format((float) $invoice->taxable_amount, 2),
            'total_sgst' => $invoice->sgst_amount ? number_format((float) $invoice->sgst_amount, 2) : '0.00',
            'total_cgst' => $invoice->cgst_amount ? number_format((float) $invoice->cgst_amount, 2) : '0.00',
            'total_gross' => number_format((float) $invoice->net_amount, 2),

            // Items
            'items_rows' => $this->itemsToRows($invoice),
            'items_rows_gst_split' => $this->itemsToRowsGstSplit($invoice),
            'items_count' => $invoice->items->count(),
            'tax_slab_rows' => $this->taxSlabRows($invoice),

            // Seller
            'seller_name' => $sellerName,
            'seller_address' => $sellerAddress,
            'seller_address_2' => $sellerAddress2,
            'seller_city' => $sellerCity,
            'seller_state' => $sellerState,
            'seller_state_code' => $sellerStateCode,
            'seller_gstin' => $sellerGstin,
            'seller_contact' => $sellerContact,
            'seller_email' => $sellerEmail,
            'seller_pan' => $sellerPan,

            // Buyer Bank
            'buyer_bank_name' => $invoice->buyer_bank_name ?? '',
            'buyer_bank_account_no' => $invoice->buyer_bank_account_no ?? '',
            'buyer_bank_branch' => $invoice->buyer_bank_branch ?? '',
            'buyer_bank_ifsc' => $invoice->buyer_bank_ifsc ?? '',

            // Seller Bank
            'bank_account_no' => $bankAccountNo,
            'bank_name' => $bankName,
            'bank_branch' => $bankBranch,
            'bank_ifsc' => $bankIfsc,

            // System
            'company_name' => config('app.name'),
            'current_date' => now()->format('d/m/Y'),
            'notes' => $invoice->notes ?? '',
        ];
    }

    /**
     * Basic items rows (10 columns).
     * #, Product, HSN, Qty, Unit, Rate, Taxable(Qty×Rate), Tax%, GST Amt, Total
     */
    protected function itemsToRows(Invoice $invoice): string
    {
        $rows = '';
        $overallTaxable = 0;
        $overallGst = 0;
        $overallTotal = 0;

        foreach ($invoice->items as $i => $item) {
            $qty = (float) $item->quantity;
            $rate = (float) $item->rate;
            $gstPct = (float) ($item->gst_percent ?? 0);
            $taxable = round($qty * $rate, 2);
            $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
            $itemTotal = $taxable + $gstAmt;

            $overallTaxable += $taxable;
            $overallGst += $gstAmt;
            $overallTotal += $itemTotal;

            $rows .= '<tr>';
            $rows .= '<td style="text-align:center;">' . ($i + 1) . '</td>';
            $rows .= '<td>' . e($item->product_name ?? $item->product?->name ?? '') . '</td>';
            $rows .= '<td style="text-align:center;">' . e($item->hsn_code ?? '') . '</td>';
            $rows .= '<td style="text-align:right;">' . number_format($qty, 3) . '</td>';
            $rows .= '<td style="text-align:center;">' . e($item->unit ?? '') . '</td>';
            $rows .= '<td style="text-align:right;">' . number_format($rate, 2) . '</td>';
            $rows .= '<td style="text-align:right;font-weight:600;">' . number_format($taxable, 2) . '</td>';
            $rows .= '<td style="text-align:right;">' . ($gstPct ? number_format($gstPct, 1) . '%' : '-') . '</td>';
            $rows .= '<td style="text-align:right;">' . ($gstAmt > 0 ? number_format($gstAmt, 2) : '-') . '</td>';
            $rows .= '<td style="text-align:right;font-weight:700;">' . number_format($itemTotal, 2) . '</td>';
            $rows .= '</tr>';
        }

        $rows .= '<tr style="border-top:2px solid #000;font-weight:700;background:#f8f9fa;">';
        $rows .= '<td colspan="6" style="text-align:right;font-weight:700;">Grand Total:</td>';
        $rows .= '<td style="text-align:right;font-weight:700;">' . number_format($overallTaxable, 2) . '</td>';
        $rows .= '<td></td>';
        $rows .= '<td style="text-align:right;font-weight:700;">' . number_format($overallGst, 2) . '</td>';
        $rows .= '<td style="text-align:right;font-weight:700;font-size:1.05em;">' . number_format($overallTotal, 2) . '</td>';
        $rows .= '</tr>';

        return $rows;
    }

    /**
     * Detailed items rows with SGST/CGST split (11 columns).
     * SR, DESCRIPTION, HSN, QTY, UOM, PRICE, AMOUNT, GST%, SGST, CGST, G.AMOUNT
     * Pads to minimum 10 rows with empty rows.
     */
    protected function itemsToRowsGstSplit(Invoice $invoice): string
    {
        $rows = '';
        $bd = 'border:1px solid #000;';
        $minRows = 10;

        foreach ($invoice->items as $i => $item) {
            $qty = (float) $item->quantity;
            $rate = (float) $item->rate;
            $gstPct = (float) ($item->gst_percent ?? 0);
            $taxable = round($qty * $rate, 2);
            $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
            $sgst = round($gstAmt / 2, 2);
            $cgst = round($gstAmt / 2, 2);
            $gross = $taxable + $sgst + $cgst;

            $rows .= '<tr>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:center;">' . ($i + 1) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;">' . e($item->product_name ?? $item->product?->name ?? '') . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:center;">' . e($item->hsn_code ?? '') . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($qty, 4) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:center;">' . e(strtoupper($item->unit ?? '')) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($rate, 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($taxable, 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . ($gstPct ? number_format($gstPct, 2) : '') . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . ($sgst ? number_format($sgst, 2) : '') . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . ($cgst ? number_format($cgst, 2) : '') . '</td>';
            $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;">' . number_format($gross, 2) . '</td>';
            $rows .= '</tr>';
        }

        // Pad empty rows
        $remaining = $minRows - $invoice->items->count();
        for ($j = 0; $j < $remaining; $j++) {
            $rows .= '<tr>';
            for ($c = 0; $c < 11; $c++) {
                $rows .= '<td style="' . $bd . 'padding:4px 6px;">&nbsp;</td>';
            }
            $rows .= '</tr>';
        }

        $grandTaxable = 0;
        $grandSgst = 0;
        $grandCgst = 0;
        $grandTotal = 0;
        foreach ($invoice->items as $item) {
            $qty = (float) $item->quantity;
            $rate = (float) $item->rate;
            $gstPct = (float) ($item->gst_percent ?? 0);
            $taxable = round($qty * $rate, 2);
            $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
            $grandTaxable += $taxable;
            $grandSgst += round($gstAmt / 2, 2);
            $grandCgst += round($gstAmt / 2, 2);
            $grandTotal += $taxable + round($gstAmt / 2, 2) + round($gstAmt / 2, 2);
        }

        $rows .= '<tr style="font-weight:700;background:#f8f9fa;">';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;" colspan="5"></td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;">Total</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;">' . number_format($grandTaxable, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;"></td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;">' . number_format($grandSgst, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;">' . number_format($grandCgst, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-weight:700;font-size:1.05em;">' . number_format($grandTotal, 2) . '</td>';
        $rows .= '</tr>';

        return $rows;
    }

    /**
     * Tax slab breakdown rows grouped by GST%.
     * Each row: GST%, Taxable, SGST, CGST, IGST, Tax Total, Gross Total.
     */
    protected function taxSlabRows(Invoice $invoice): string
    {
        $defaultSlabs = [0, 5, 12, 18, 28];
        $grouped = [];

        foreach ($invoice->items as $item) {
            $gstPct = (float) ($item->gst_percent ?? 0);
            $qty = (float) $item->quantity;
            $rate = (float) $item->rate;
            $taxable = round($qty * $rate, 2);
            $gstAmt = $gstPct ? round($taxable * ($gstPct / 100), 2) : 0;
            $igst = 0.0;
            $sgst = round($gstAmt / 2, 2);
            $cgst = round($gstAmt / 2, 2);

            $key = rtrim(rtrim(number_format($gstPct, 2, '.', ''), '0'), '.');
            if ($key === '') {
                $key = '0';
            }
            if (!isset($grouped[$key])) {
                $grouped[$key] = ['taxable' => 0, 'sgst' => 0, 'cgst' => 0, 'igst' => 0];
            }
            $grouped[$key]['taxable'] += $taxable;
            $grouped[$key]['sgst'] += $sgst;
            $grouped[$key]['cgst'] += $cgst;
            $grouped[$key]['igst'] += $igst;
        }

        foreach ($defaultSlabs as $slab) {
            $key = (string) $slab;
            if (!isset($grouped[$key])) {
                $grouped[$key] = ['taxable' => 0, 'sgst' => 0, 'cgst' => 0, 'igst' => 0];
            }
        }

        $bd = 'border:1px solid #000;';
        $rows = '';
        $slabKeys = array_keys($grouped);
        usort($slabKeys, fn ($a, $b) => (float) $a <=> (float) $b);

        $grandTaxable = 0;
        $grandSgst = 0;
        $grandCgst = 0;
        $grandIgst = 0;
        $grandTax = 0;
        $grandGross = 0;

        foreach ($slabKeys as $slab) {
            $d = $grouped[$slab];
            $taxTotal = $d['sgst'] + $d['cgst'] + $d['igst'];
            $gross = $d['taxable'] + $taxTotal;

            $grandTaxable += $d['taxable'];
            $grandSgst += $d['sgst'];
            $grandCgst += $d['cgst'];
            $grandIgst += $d['igst'];
            $grandTax += $taxTotal;
            $grandGross += $gross;

            $rows .= '<tr>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;font-weight:700;">' . $slab . '%</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;">' . number_format($d['taxable'], 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;">' . number_format($d['sgst'], 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;">' . number_format($d['cgst'], 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;">' . number_format($d['igst'], 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;font-weight:700;">' . number_format($taxTotal, 2) . '</td>';
            $rows .= '<td style="' . $bd . 'padding:3px 6px;text-align:right;font-weight:700;">' . number_format($gross, 2) . '</td>';
            $rows .= '</tr>';
        }

        $rows .= '<tr style="font-weight:700;background:#f8f9fa;">';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;">Grand Total</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($grandTaxable, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($grandSgst, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($grandCgst, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($grandIgst, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;">' . number_format($grandTax, 2) . '</td>';
        $rows .= '<td style="' . $bd . 'padding:4px 6px;text-align:right;font-size:1.05em;">' . number_format($grandGross, 2) . '</td>';
        $rows .= '</tr>';

        return $rows;
    }

    /**
     * Read a setting with fallback.
     */
    protected function setting(string $key, $default = ''): string
    {
        try {
            return (string) (Setting::get($key, $default) ?? $default);
        } catch (\Throwable) {
            return (string) $default;
        }
    }

    /**
     * Convert a number to words (Indian numbering style).
     */
    protected function amountInWords(float $amount): string
    {
        if ($amount == 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $integerPart = (int) floor($amount);
        $decimalPart = round(($amount - $integerPart) * 100);

        $words = $this->numberToWords($integerPart, $ones, $tens);

        if ($decimalPart > 0) {
            $words .= ' and ' . $this->numberToWords((int) $decimalPart, $ones, $tens) . ' Paise';
        }

        return trim($words) . ' Only.';
    }

    private function numberToWords(int $number, array $ones, array $tens): string
    {
        if ($number == 0) return 'Zero';
        if ($number < 0) return 'Minus ' . $this->numberToWords(abs($number), $ones, $tens);

        $words = '';

        if (intdiv($number, 10000000) > 0) {
            $words .= $this->numberToWords(intdiv($number, 10000000), $ones, $tens) . ' Crore ';
            $number %= 10000000;
        }
        if (intdiv($number, 100000) > 0) {
            $words .= $this->numberToWords(intdiv($number, 100000), $ones, $tens) . ' Lakh ';
            $number %= 100000;
        }
        if (intdiv($number, 1000) > 0) {
            $words .= $this->numberToWords(intdiv($number, 1000), $ones, $tens) . ' Thousand ';
            $number %= 1000;
        }
        if (intdiv($number, 100) > 0) {
            $words .= $ones[intdiv($number, 100)] . ' Hundred ';
            $number %= 100;
        }
        if ($number > 0) {
            if ($words !== '') {
                $words .= 'and ';
            }
            if ($number < 20) {
                $words .= $ones[$number];
            } else {
                $words .= $tens[intdiv($number, 10)];
                if ($number % 10) {
                    $words .= ' ' . $ones[$number % 10];
                }
            }
        }

        return trim($words);
    }
}
