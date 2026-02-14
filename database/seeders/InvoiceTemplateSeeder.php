<?php

namespace Database\Seeders;

use App\Models\InvoiceTemplate;
use Illuminate\Database\Seeder;

class InvoiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Remove old default
        InvoiceTemplate::where('type', 'tax_invoice')->update(['is_default' => false]);

        // ────────────────────────────────────────────────
        //  TRADITIONAL GST TAX INVOICE (Default)
        //  Matches the standard Indian bordered invoice format
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Traditional GST Invoice', 'type' => 'tax_invoice'],
            [
                'colors' => ['primary' => '#000000', 'secondary' => '#333333', 'accent' => '#000000'],
                'is_default' => true,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->traditionalHeader(),
                'body_html' => $this->traditionalBody(),
                'footer_html' => $this->traditionalFooter(),
            ]
        );

        // ────────────────────────────────────────────────
        //  Professional Blue
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Professional Blue', 'type' => 'tax_invoice'],
            [
                'colors' => ['primary' => '#1a5276', 'secondary' => '#2e86c1', 'accent' => '#2c3e50'],
                'is_default' => false,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->professionalBlueHeader(),
                'body_html' => $this->professionalBlueBody(),
                'footer_html' => $this->professionalBlueFooter(),
            ]
        );

        // ────────────────────────────────────────────────
        //  Modern Gradient
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Modern Gradient', 'type' => 'tax_invoice'],
            [
                'colors' => ['primary' => '#667eea', 'secondary' => '#764ba2', 'accent' => '#2d3436'],
                'is_default' => false,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->modernGradientHeader(),
                'body_html' => $this->modernGradientBody(),
                'footer_html' => $this->modernGradientFooter(),
            ]
        );

        // ────────────────────────────────────────────────
        //  Proforma
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Standard Proforma', 'type' => 'proforma'],
            [
                'colors' => ['primary' => '#0984e3', 'secondary' => '#74b9ff', 'accent' => '#2d3436'],
                'is_default' => true,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->proformaHeader(),
                'body_html' => $this->proformaBody(),
                'footer_html' => $this->proformaFooter(),
            ]
        );

        // ────────────────────────────────────────────────
        //  Delivery Challan
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Standard Delivery Challan', 'type' => 'delivery_challan'],
            [
                'colors' => ['primary' => '#00b894', 'secondary' => '#55efc4', 'accent' => '#2d3436'],
                'is_default' => true,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->challanHeader(),
                'body_html' => $this->challanBody(),
                'footer_html' => $this->challanFooter(),
            ]
        );

        // ────────────────────────────────────────────────
        //  Credit Note
        // ────────────────────────────────────────────────
        InvoiceTemplate::updateOrCreate(
            ['name' => 'Standard Credit Note', 'type' => 'credit_note'],
            [
                'colors' => ['primary' => '#e74c3c', 'secondary' => '#ff7675', 'accent' => '#2d3436'],
                'is_default' => true,
                'is_active' => true,
                'version' => 1,
                'header_html' => $this->creditNoteHeader(),
                'body_html' => $this->creditNoteBody(),
                'footer_html' => $this->creditNoteFooter(),
            ]
        );
    }

    /* ================================================================
     *  TRADITIONAL GST INVOICE (matching the reference image exactly)
     * ================================================================ */

    private function traditionalHeader(): string
    {
        return <<<'HTML'
<div style="font-family:Arial,Helvetica,sans-serif;max-width:800px;margin:0 auto;font-size:11px;color:#000;line-height:1.4;">
<table style="width:100%;border-collapse:collapse;border:2px solid #000;" cellpadding="0" cellspacing="0">

  <!-- Row 1: Invoice No / Tax Invoice / Copy Type -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:6px 10px;vertical-align:top;">
      <strong>INVOICE NO : {{invoice_number}}</strong><br>
      <strong>DATED : {{invoice_date}}</strong>
    </td>
    <td colspan="3" style="border:1px solid #000;padding:6px 10px;text-align:center;vertical-align:middle;">
      <strong style="font-size:13px;">{{document_type}}</strong>
    </td>
    <td colspan="3" style="border:1px solid #000;padding:6px 10px;text-align:right;vertical-align:top;">
      <strong>ORIGINAL FOR RECIPIENT</strong><br>
      <strong>BASIS OF PAYMENT : {{payment_mode}}</strong>
    </td>
  </tr>

  <!-- Row 2: Seller/Buyer Headers -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:3px 10px;text-align:center;background:#f5f5f5;">
      <strong style="font-size:9px;letter-spacing:1px;">SELLER'S DETAILS</strong>
    </td>
    <td colspan="6" style="border:1px solid #000;padding:3px 10px;text-align:center;background:#f5f5f5;">
      <strong style="font-size:9px;letter-spacing:1px;">BUYER'S DETAILS</strong>
    </td>
  </tr>

  <!-- Row 3: Seller/Buyer Details -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:8px 10px;vertical-align:top;line-height:1.6;">
      <strong style="font-size:13px;">{{seller_name}}</strong><br>
      {{seller_address}}<br>
      {{seller_address_2}}<br><br>
      <strong>CITY :</strong> {{seller_city}}<br>
      <strong>STATE :</strong> {{seller_state}} &nbsp; <strong>STATE CODE :</strong> {{seller_state_code}}<br>
      <strong>GSTIN :</strong> {{seller_gstin}}<br>
      <strong>Contact :</strong> {{seller_contact}}<br>
      <strong>Email :</strong> {{seller_email}}
    </td>
    <td colspan="6" style="border:1px solid #000;padding:8px 10px;vertical-align:top;line-height:1.6;">
      <strong style="font-size:13px;">{{customer_name}}</strong><br>
      {{customer_address}}<br><br>
      <strong>CITY :</strong> {{city}}<br>
      <strong>STATE :</strong> {{state}} &nbsp;&nbsp; <strong>STATECODE :</strong> {{buyer_state_code}}<br>
      <strong>GSTIN :</strong> {{gstin}}<br>
      <strong>PAN :</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>AADHAR :</strong><br>
      <strong>PLACE OF SUPPLY :</strong> {{place_of_supply}}
    </td>
  </tr>

  <!-- Row 4: GR / Transport -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:6px 10px;">
      <strong>GR NO. :</strong> {{gr_number}} &nbsp;&nbsp; <strong>DATED :</strong> {{gr_date}}
    </td>
    <td colspan="6" style="border:1px solid #000;padding:6px 10px;">
      <strong>TRANSPORT NAME :</strong> {{transport_name}}
    </td>
  </tr>

  <!-- Row 5: Vehicle / Driver -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:6px 10px;">
      <strong>VEHICLE NO. :</strong> {{vehicle_number}}
    </td>
    <td colspan="6" style="border:1px solid #000;padding:6px 10px;">
      <strong>DRIVER NAME :</strong> {{driver_name}}
    </td>
  </tr>

  <!-- Row 6: E-Way Bill -->
  <tr>
    <td colspan="5" style="border:1px solid #000;padding:6px 10px;">
      <strong>EWAY BILL NO. :</strong> {{eway_bill_no}}
    </td>
    <td colspan="6" style="border:1px solid #000;padding:6px 10px;text-align:right;">
      <strong>APP. DISTANCE IN KMS:</strong> {{distance_km}}
    </td>
  </tr>

</table>

<!-- Items Table -->
<table style="width:100%;border-collapse:collapse;border-left:2px solid #000;border-right:2px solid #000;" cellpadding="0" cellspacing="0">
  <!-- Items Header -->
  <tr style="background:#f5f5f5;">
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:3.5%;">SR.</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:20%;">DESCRIPTION</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:6%;">HSN</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:8%;">QTY</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:5%;">UOM</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:8%;">PRICE</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:10%;">AMOUNT</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:6%;">GST%</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:9%;">SGST</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:9%;">CGST</th>
    <th style="border:1px solid #000;padding:6px 4px;text-align:center;font-size:10px;font-weight:700;width:11%;">G. AMOUNT</th>
  </tr>
HTML;
    }

    private function traditionalBody(): string
    {
        return <<<'HTML'
  <!-- Item Rows (dynamically generated with GST split) -->
  {{items_rows_gst_split}}
HTML;
    }

    private function traditionalFooter(): string
    {
        return <<<'HTML'
  <!-- TOTALS Row -->
  <tr style="background:#f5f5f5;font-weight:700;">
    <td colspan="2" style="border:1px solid #000;padding:6px;text-align:center;font-size:11px;"><strong>TOTALS</strong></td>
    <td style="border:1px solid #000;padding:6px;"></td>
    <td style="border:1px solid #000;padding:6px;text-align:right;">{{total_qty}}</td>
    <td style="border:1px solid #000;padding:6px;"></td>
    <td style="border:1px solid #000;padding:6px;"></td>
    <td style="border:1px solid #000;padding:6px;text-align:right;">{{total_taxable}}</td>
    <td style="border:1px solid #000;padding:6px;"></td>
    <td style="border:1px solid #000;padding:6px;text-align:right;">{{total_sgst}}</td>
    <td style="border:1px solid #000;padding:6px;text-align:right;">{{total_cgst}}</td>
    <td style="border:1px solid #000;padding:6px;text-align:right;font-size:12px;">{{total_gross}}</td>
  </tr>
</table>

<!-- Tax Breakdown + Summary -->
<table style="width:100%;border-collapse:collapse;border-left:2px solid #000;border-right:2px solid #000;border-bottom:1px solid #000;" cellpadding="0" cellspacing="0">
  <tr>
    <!-- LEFT: Tax Slab Breakdown -->
    <td style="width:48%;vertical-align:top;border-right:1px solid #000;padding:0;">
      <table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0">
        <tr style="background:#f5f5f5;">
          <th style="border:1px solid #000;padding:4px 6px;font-size:9px;text-align:center;width:12%;"></th>
          <th style="border:1px solid #000;padding:4px 6px;font-size:9px;text-align:center;font-weight:700;"><strong>SALES</strong></th>
          <th style="border:1px solid #000;padding:4px 6px;font-size:9px;text-align:center;font-weight:700;"><strong>SGST</strong></th>
          <th style="border:1px solid #000;padding:4px 6px;font-size:9px;text-align:center;font-weight:700;"><strong>CGST</strong></th>
        </tr>
        {{tax_slab_rows}}
      </table>
    </td>
    <!-- RIGHT: Tax Summary -->
    <td style="width:52%;vertical-align:top;padding:0;">
      <table style="width:100%;border-collapse:collapse;" cellpadding="0" cellspacing="0">
        <tr>
          <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">TOTAL AMOUNT BEFORE TAX</td>
          <td style="border:1px solid #000;padding:5px 10px;text-align:right;font-weight:700;">{{taxable_amount}}</td>
        </tr>
        <tr>
          <td style="border:1px solid #000;padding:5px 10px;">ADD SGST</td>
          <td style="border:1px solid #000;padding:5px 10px;text-align:right;">{{sgst_amount}}</td>
        </tr>
        <tr>
          <td style="border:1px solid #000;padding:5px 10px;">ADD CGST</td>
          <td style="border:1px solid #000;padding:5px 10px;text-align:right;">{{cgst_amount}}</td>
        </tr>
        <tr>
          <td style="border:1px solid #000;padding:5px 10px;font-weight:700;">TOTAL GST</td>
          <td style="border:1px solid #000;padding:5px 10px;text-align:right;font-weight:700;">{{gst_amount}}</td>
        </tr>
        <tr style="background:#f5f5f5;">
          <td style="border:1px solid #000;padding:5px 10px;font-weight:700;font-size:12px;">TOTAL AMOUNT WITH GST</td>
          <td style="border:1px solid #000;padding:5px 10px;text-align:right;font-weight:700;font-size:12px;">{{net_amount}}</td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<!-- Amount in Words -->
<table style="width:100%;border-collapse:collapse;border-left:2px solid #000;border-right:2px solid #000;" cellpadding="0" cellspacing="0">
  <tr>
    <td style="border:1px solid #000;padding:6px 10px;background:#f5f5f5;">
      <strong>AMOUNT IN WORDS</strong> &nbsp;&nbsp;&nbsp; {{amount_in_words}}
    </td>
  </tr>
</table>

<!-- Bank Details -->
<table style="width:100%;border-collapse:collapse;border-left:2px solid #000;border-right:2px solid #000;" cellpadding="0" cellspacing="0">
  <tr>
    <td style="border:1px solid #000;padding:5px 10px;width:18%;font-weight:700;font-size:10px;">BANK ACCOUNT NO.</td>
    <td style="border:1px solid #000;padding:5px 10px;width:32%;">{{bank_account_no}}</td>
    <td style="border:1px solid #000;padding:5px 10px;width:12%;font-weight:700;font-size:10px;">BRANCH</td>
    <td style="border:1px solid #000;padding:5px 10px;width:38%;">{{bank_branch}}</td>
  </tr>
  <tr>
    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;font-size:10px;">BANK NAME</td>
    <td style="border:1px solid #000;padding:5px 10px;">{{bank_name}}</td>
    <td style="border:1px solid #000;padding:5px 10px;font-weight:700;font-size:10px;">IFSC</td>
    <td style="border:1px solid #000;padding:5px 10px;">{{bank_ifsc}}</td>
  </tr>
</table>

<!-- Terms -->
<table style="width:100%;border-collapse:collapse;border-left:2px solid #000;border-right:2px solid #000;" cellpadding="0" cellspacing="0">
  <tr>
    <td style="border:1px solid #000;padding:8px 10px;font-size:10px;line-height:1.6;">
      <strong>* E &amp; O.E.</strong> &nbsp; * Goods once sold will not be taken back or exchanged<br>
      * Not Responsible for breakages and loss as soon as the consignment leaves our Godown<br>
      * All Disputes Are Subject To local Jurisdiction Only
    </td>
  </tr>
</table>

<!-- Signatures -->
<table style="width:100%;border-collapse:collapse;border:2px solid #000;border-top:1px solid #000;" cellpadding="0" cellspacing="0">
  <tr>
    <td style="border:1px solid #000;padding:10px;width:50%;vertical-align:bottom;height:70px;">
      <br><br><br>
      <strong style="font-size:11px;">BUYER'S SIGNATURE</strong>
    </td>
    <td style="border:1px solid #000;padding:10px;width:50%;text-align:right;vertical-align:bottom;">
      <strong style="font-size:11px;">FOR {{seller_name}}</strong><br><br><br>
      <strong style="font-size:11px;">AUTHORIZED SIGNATORY</strong>
    </td>
  </tr>
</table>

</div>
HTML;
    }

    /* ================================================================
     *  PROFESSIONAL BLUE
     * ================================================================ */

    private function professionalBlueHeader(): string
    {
        return <<<'HTML'
<div style="font-family:'Segoe UI',system-ui,sans-serif;max-width:780px;margin:0 auto;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #1a5276;padding-bottom:20px;margin-bottom:20px;">
    <div>
      <h1 style="margin:0;font-size:28px;color:#1a5276;font-weight:800;">{{seller_name}}</h1>
      <p style="margin:4px 0 0;color:#666;font-size:12px;">{{seller_address}}</p>
      <p style="margin:2px 0;color:#666;font-size:12px;">GSTIN: {{seller_gstin}}</p>
    </div>
    <div style="text-align:right;">
      <h2 style="margin:0;font-size:22px;color:#1a5276;text-transform:uppercase;letter-spacing:2px;">{{document_type}}</h2>
      <p style="margin:6px 0 0;font-size:13px;color:#555;"><strong>Invoice #:</strong> {{invoice_number}}</p>
      <p style="margin:2px 0;font-size:13px;color:#555;"><strong>Date:</strong> {{invoice_date}}</p>
    </div>
  </div>
  <div style="display:flex;gap:20px;margin-bottom:24px;">
    <div style="flex:1;background:#f0f7fc;border-radius:8px;padding:16px;">
      <h4 style="margin:0 0 8px;font-size:11px;color:#1a5276;text-transform:uppercase;letter-spacing:1px;">Bill To</h4>
      <p style="margin:0;font-size:14px;font-weight:700;color:#2c3e50;">{{customer_name}}</p>
      <p style="margin:4px 0;font-size:12px;color:#555;">{{customer_address}}</p>
      <p style="margin:2px 0;font-size:12px;color:#555;">GSTIN: {{gstin}}</p>
      <p style="margin:2px 0;font-size:12px;color:#555;">Place of Supply: {{place_of_supply}}</p>
    </div>
    <div style="flex:1;background:#f0f7fc;border-radius:8px;padding:16px;">
      <h4 style="margin:0 0 8px;font-size:11px;color:#1a5276;text-transform:uppercase;letter-spacing:1px;">Transport Details</h4>
      <p style="margin:2px 0;font-size:12px;color:#555;"><strong>Transport:</strong> {{transport_name}}</p>
      <p style="margin:2px 0;font-size:12px;color:#555;"><strong>Vehicle:</strong> {{vehicle_number}}</p>
      <p style="margin:2px 0;font-size:12px;color:#555;"><strong>Driver:</strong> {{driver_name}}</p>
      <p style="margin:2px 0;font-size:12px;color:#555;"><strong>GR No:</strong> {{gr_number}} &nbsp; <strong>E-Way:</strong> {{eway_bill_no}}</p>
    </div>
  </div>
HTML;
    }

    private function professionalBlueBody(): string
    {
        return <<<'HTML'
  <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <thead>
      <tr style="background:#1a5276;">
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:center;border:1px solid #1a5276;">#</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:left;border:1px solid #1a5276;">Description</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:center;border:1px solid #1a5276;">HSN</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;border:1px solid #1a5276;">Qty</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:center;border:1px solid #1a5276;">Unit</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;border:1px solid #1a5276;">Rate</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;border:1px solid #1a5276;">GST</th>
        <th style="padding:10px 12px;color:#fff;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;text-align:right;border:1px solid #1a5276;">Amount</th>
      </tr>
    </thead>
    <tbody>
      {{items_rows}}
    </tbody>
  </table>
HTML;
    }

    private function professionalBlueFooter(): string
    {
        return <<<'HTML'
  <div style="display:flex;justify-content:flex-end;margin-bottom:24px;">
    <table style="width:320px;border-collapse:collapse;">
      <tr><td style="padding:6px 12px;font-size:12px;color:#555;">Taxable Amount</td><td style="padding:6px 12px;text-align:right;font-size:12px;font-weight:600;">{{taxable_amount}}</td></tr>
      <tr><td style="padding:6px 12px;font-size:12px;color:#555;">CGST</td><td style="padding:6px 12px;text-align:right;font-size:12px;">{{cgst_amount}}</td></tr>
      <tr><td style="padding:6px 12px;font-size:12px;color:#555;">SGST</td><td style="padding:6px 12px;text-align:right;font-size:12px;">{{sgst_amount}}</td></tr>
      <tr style="border-top:2px solid #1a5276;"><td style="padding:10px 12px;font-size:14px;font-weight:800;color:#1a5276;">Net Amount</td><td style="padding:10px 12px;text-align:right;font-size:16px;font-weight:800;color:#1a5276;">{{net_amount}}</td></tr>
      <tr><td style="padding:6px 12px;font-size:12px;color:#555;">Advance</td><td style="padding:6px 12px;text-align:right;font-size:12px;">{{advance_amount}}</td></tr>
      <tr><td style="padding:6px 12px;font-size:12px;color:#555;font-weight:600;">Balance Due</td><td style="padding:6px 12px;text-align:right;font-size:13px;font-weight:700;color:#c0392b;">{{balance_amount}}</td></tr>
    </table>
  </div>
  <div style="background:#f0f7fc;border-radius:6px;padding:12px 16px;margin-bottom:20px;">
    <p style="margin:0;font-size:11px;color:#1a5276;"><strong>Amount in Words:</strong> {{amount_in_words}}</p>
  </div>
  <div style="display:flex;justify-content:space-between;margin-top:40px;padding-top:20px;border-top:1px solid #ddd;">
    <div style="text-align:center;"><div style="border-top:1px solid #999;width:180px;margin-top:50px;padding-top:6px;font-size:11px;color:#666;">Customer Signature</div></div>
    <div style="text-align:center;"><div style="border-top:1px solid #999;width:180px;margin-top:50px;padding-top:6px;font-size:11px;color:#666;">Authorized Signatory</div></div>
  </div>
  <p style="text-align:center;font-size:10px;color:#999;margin-top:24px;">This is a computer-generated invoice.</p>
</div>
HTML;
    }

    /* ================================================================
     *  MODERN GRADIENT
     * ================================================================ */

    private function modernGradientHeader(): string
    {
        return <<<'HTML'
<div style="font-family:'Segoe UI',system-ui,sans-serif;max-width:780px;margin:0 auto;">
  <div style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px 12px 0 0;padding:24px 28px;color:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <h1 style="margin:0;font-size:26px;font-weight:800;">{{seller_name}}</h1>
        <p style="margin:4px 0 0;font-size:12px;opacity:0.8;">{{seller_address}}</p>
      </div>
      <div style="text-align:right;">
        <h2 style="margin:0;font-size:18px;text-transform:uppercase;letter-spacing:3px;font-weight:300;">{{document_type}}</h2>
        <p style="margin:8px 0 0;font-size:24px;font-weight:800;">{{invoice_number}}</p>
      </div>
    </div>
  </div>
  <div style="padding:0 28px;">
    <div style="display:flex;gap:20px;margin:20px 0;">
      <div style="flex:1;border:1px solid #e8e5f5;border-radius:8px;padding:16px;">
        <h4 style="margin:0 0 10px;font-size:10px;color:#667eea;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;">Bill To</h4>
        <p style="margin:0;font-size:15px;font-weight:700;color:#2d3436;">{{customer_name}}</p>
        <p style="margin:4px 0;font-size:12px;color:#666;">{{customer_address}}</p>
        <p style="margin:4px 0;font-size:12px;color:#666;">GSTIN: {{gstin}}</p>
      </div>
      <div style="flex:1;border:1px solid #e8e5f5;border-radius:8px;padding:16px;">
        <h4 style="margin:0 0 10px;font-size:10px;color:#667eea;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;">Invoice Details</h4>
        <p style="margin:4px 0;font-size:12px;color:#666;"><strong>Date:</strong> {{invoice_date}}</p>
        <p style="margin:4px 0;font-size:12px;color:#666;"><strong>Payment:</strong> {{payment_mode}}</p>
        <p style="margin:4px 0;font-size:12px;color:#666;"><strong>Place of Supply:</strong> {{place_of_supply}}</p>
        <p style="margin:4px 0;font-size:12px;color:#666;"><strong>E-Way Bill:</strong> {{eway_bill_no}}</p>
      </div>
    </div>
HTML;
    }

    private function modernGradientBody(): string
    {
        return <<<'HTML'
    <table style="width:100%;border-collapse:collapse;margin:20px 0;">
      <thead>
        <tr>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:center;">#</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:left;">Description</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:center;">HSN</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:right;">Qty</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:center;">Unit</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:right;">Rate</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:right;">GST</th>
          <th style="padding:12px;font-size:10px;text-transform:uppercase;letter-spacing:1px;color:#667eea;border-bottom:2px solid #667eea;text-align:right;">Amount</th>
        </tr>
      </thead>
      <tbody>
        {{items_rows}}
      </tbody>
    </table>
HTML;
    }

    private function modernGradientFooter(): string
    {
        return <<<'HTML'
    <div style="display:flex;justify-content:flex-end;margin:20px 0;">
      <div style="width:340px;">
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12px;color:#666;border-bottom:1px solid #eee;"><span>Taxable</span><span>{{taxable_amount}}</span></div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12px;color:#666;border-bottom:1px solid #eee;"><span>CGST</span><span>{{cgst_amount}}</span></div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;font-size:12px;color:#666;border-bottom:1px solid #eee;"><span>SGST</span><span>{{sgst_amount}}</span></div>
        <div style="display:flex;justify-content:space-between;padding:12px 0;margin-top:4px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:6px;padding:12px 16px;color:#fff;"><span style="font-weight:700;">NET TOTAL</span><span style="font-weight:800;font-size:16px;">{{net_amount}}</span></div>
      </div>
    </div>
    <div style="background:#f8f7ff;border-radius:6px;padding:10px 16px;margin:16px 0;">
      <p style="margin:0;font-size:11px;color:#667eea;"><strong>In Words:</strong> {{amount_in_words}}</p>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:50px;padding-top:20px;">
      <div style="text-align:center;"><div style="border-top:1px dashed #ccc;width:180px;margin-top:40px;padding-top:8px;font-size:10px;color:#999;">Customer Signature</div></div>
      <div style="text-align:center;"><div style="border-top:1px dashed #ccc;width:180px;margin-top:40px;padding-top:8px;font-size:10px;color:#999;">Authorized Signatory</div></div>
    </div>
    <p style="text-align:center;font-size:9px;color:#bbb;margin-top:20px;">Computer generated invoice | {{company_name}}</p>
  </div>
</div>
HTML;
    }

    /* ================================================================
     *  PROFORMA
     * ================================================================ */

    private function proformaHeader(): string
    {
        return <<<'HTML'
<div style="font-family:'Segoe UI',system-ui,sans-serif;max-width:780px;margin:0 auto;">
  <div style="background:#0984e3;padding:20px 24px;color:#fff;border-radius:8px 8px 0 0;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h1 style="margin:0;font-size:22px;font-weight:800;">{{seller_name}}</h1>
      <div style="text-align:right;">
        <h2 style="margin:0;font-size:16px;letter-spacing:3px;font-weight:300;text-transform:uppercase;">PROFORMA INVOICE</h2>
        <p style="margin:6px 0 0;font-size:14px;font-weight:700;">{{invoice_number}}</p>
      </div>
    </div>
  </div>
  <div style="padding:20px 24px;border:1px solid #ddd;border-top:0;">
    <div style="display:flex;gap:20px;margin-bottom:20px;">
      <div style="flex:1;background:#ebf5fb;border-radius:6px;padding:14px;">
        <p style="margin:0 0 6px;font-size:10px;color:#0984e3;text-transform:uppercase;font-weight:700;">Bill To</p>
        <p style="margin:0;font-weight:700;">{{customer_name}}</p>
        <p style="margin:2px 0;font-size:12px;color:#666;">{{customer_address}}</p>
        <p style="margin:2px 0;font-size:12px;color:#666;">GSTIN: {{gstin}}</p>
      </div>
      <div style="flex:1;background:#ebf5fb;border-radius:6px;padding:14px;">
        <p style="margin:0 0 6px;font-size:10px;color:#0984e3;text-transform:uppercase;font-weight:700;">Details</p>
        <p style="margin:2px 0;font-size:12px;"><strong>Date:</strong> {{invoice_date}}</p>
        <p style="margin:2px 0;font-size:12px;"><strong>Valid Until:</strong> 15 days from issue</p>
      </div>
    </div>
    <div style="background:#fff3cd;border-radius:6px;padding:10px 14px;margin-bottom:16px;border-left:4px solid #f39c12;">
      <p style="margin:0;font-size:11px;color:#856404;"><strong>Note:</strong> This is a Proforma Invoice. Prices and availability are subject to change.</p>
    </div>
HTML;
    }

    private function proformaBody(): string
    {
        return <<<'HTML'
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
      <thead>
        <tr style="background:#0984e3;">
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:center;">#</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:left;">Description</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:center;">HSN</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:right;">Qty</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:center;">Unit</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:right;">Rate</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:right;">GST</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #0984e3;text-align:right;">Amount</th>
        </tr>
      </thead>
      <tbody>
        {{items_rows}}
      </tbody>
    </table>
HTML;
    }

    private function proformaFooter(): string
    {
        return <<<'HTML'
    <div style="display:flex;justify-content:flex-end;">
      <table style="width:300px;border-collapse:collapse;">
        <tr><td style="padding:6px;font-size:12px;color:#666;">Taxable</td><td style="padding:6px;text-align:right;">{{taxable_amount}}</td></tr>
        <tr><td style="padding:6px;font-size:12px;color:#666;">GST</td><td style="padding:6px;text-align:right;">{{gst_amount}}</td></tr>
        <tr style="border-top:2px solid #0984e3;"><td style="padding:10px 6px;font-weight:700;color:#0984e3;font-size:14px;">Estimated Total</td><td style="padding:10px 6px;text-align:right;font-weight:800;color:#0984e3;font-size:16px;">{{net_amount}}</td></tr>
      </table>
    </div>
    <p style="margin-top:16px;font-size:11px;color:#666;"><strong>Amount in Words:</strong> {{amount_in_words}}</p>
    <div style="margin-top:50px;text-align:right;">
      <div style="border-top:1px solid #ccc;width:200px;display:inline-block;padding-top:8px;font-size:10px;color:#999;">Authorized Signatory</div>
    </div>
  </div>
</div>
HTML;
    }

    /* ================================================================
     *  DELIVERY CHALLAN
     * ================================================================ */

    private function challanHeader(): string
    {
        return <<<'HTML'
<div style="font-family:'Segoe UI',system-ui,sans-serif;max-width:780px;margin:0 auto;">
  <div style="display:flex;justify-content:space-between;border-bottom:3px solid #00b894;padding-bottom:16px;margin-bottom:16px;">
    <h1 style="margin:0;font-size:24px;color:#00b894;font-weight:800;">{{seller_name}}</h1>
    <div style="text-align:right;">
      <h2 style="margin:0;font-size:18px;color:#00b894;text-transform:uppercase;letter-spacing:2px;">DELIVERY CHALLAN</h2>
      <p style="margin:4px 0;font-size:12px;color:#666;"><strong>Challan #:</strong> {{invoice_number}} &nbsp; <strong>Date:</strong> {{invoice_date}}</p>
    </div>
  </div>
  <div style="display:flex;gap:16px;margin-bottom:20px;">
    <div style="flex:1;background:#e8f8f5;border-radius:6px;padding:14px;">
      <p style="margin:0 0 6px;font-size:10px;color:#00b894;text-transform:uppercase;font-weight:700;">Deliver To</p>
      <p style="margin:0;font-weight:700;">{{customer_name}}</p>
      <p style="margin:2px 0;font-size:12px;color:#666;">{{customer_address}}</p>
    </div>
    <div style="flex:1;background:#e8f8f5;border-radius:6px;padding:14px;">
      <p style="margin:0 0 6px;font-size:10px;color:#00b894;text-transform:uppercase;font-weight:700;">Transport</p>
      <p style="margin:2px 0;font-size:12px;"><strong>Transport:</strong> {{transport_name}}</p>
      <p style="margin:2px 0;font-size:12px;"><strong>Vehicle:</strong> {{vehicle_number}} &nbsp; <strong>GR:</strong> {{gr_number}}</p>
    </div>
  </div>
HTML;
    }

    private function challanBody(): string
    {
        return <<<'HTML'
  <table style="width:100%;border-collapse:collapse;margin:16px 0;">
    <thead>
      <tr style="background:#00b894;">
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:center;">#</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:left;">Description</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:center;">HSN</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:right;">Qty</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:center;">Unit</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:right;">Rate</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:right;">GST</th>
        <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #00b894;text-align:right;">Amount</th>
      </tr>
    </thead>
    <tbody>
      {{items_rows}}
    </tbody>
  </table>
HTML;
    }

    private function challanFooter(): string
    {
        return <<<'HTML'
  <div style="display:flex;justify-content:flex-end;">
    <table style="width:300px;border-collapse:collapse;">
      <tr style="border-top:2px solid #00b894;"><td style="padding:10px 6px;font-weight:700;color:#00b894;font-size:14px;">Total Value</td><td style="padding:10px 6px;text-align:right;font-weight:800;color:#00b894;font-size:16px;">{{net_amount}}</td></tr>
    </table>
  </div>
  <p style="margin:16px 0;font-size:11px;color:#666;"><strong>Note:</strong> Goods are sent on approval. Tax invoice will be issued separately.</p>
  <div style="display:flex;justify-content:space-between;margin-top:50px;">
    <div style="text-align:center;"><div style="border-top:1px solid #ccc;width:180px;margin-top:40px;padding-top:8px;font-size:10px;color:#999;">Received By</div></div>
    <div style="text-align:center;"><div style="border-top:1px solid #ccc;width:180px;margin-top:40px;padding-top:8px;font-size:10px;color:#999;">Dispatched By</div></div>
  </div>
</div>
HTML;
    }

    /* ================================================================
     *  CREDIT NOTE
     * ================================================================ */

    private function creditNoteHeader(): string
    {
        return <<<'HTML'
<div style="font-family:'Segoe UI',system-ui,sans-serif;max-width:780px;margin:0 auto;">
  <div style="background:#e74c3c;padding:18px 24px;color:#fff;border-radius:8px 8px 0 0;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h1 style="margin:0;font-size:22px;font-weight:800;">{{seller_name}}</h1>
      <div style="text-align:right;">
        <h2 style="margin:0;font-size:16px;letter-spacing:2px;text-transform:uppercase;">CREDIT NOTE</h2>
        <p style="margin:4px 0 0;font-size:13px;font-weight:700;">{{invoice_number}}</p>
      </div>
    </div>
  </div>
  <div style="padding:20px 24px;border:1px solid #ddd;border-top:0;">
    <div style="display:flex;gap:20px;margin-bottom:20px;">
      <div style="flex:1;background:#fdf2f2;border-radius:6px;padding:14px;border-left:4px solid #e74c3c;">
        <p style="margin:0 0 6px;font-size:10px;color:#e74c3c;text-transform:uppercase;font-weight:700;">Issued To</p>
        <p style="margin:0;font-weight:700;">{{customer_name}}</p>
        <p style="margin:2px 0;font-size:12px;color:#666;">{{customer_address}}</p>
        <p style="margin:2px 0;font-size:12px;color:#666;">GSTIN: {{gstin}}</p>
      </div>
      <div style="flex:1;">
        <p style="margin:4px 0;font-size:12px;"><strong>Date:</strong> {{invoice_date}}</p>
        <p style="margin:4px 0;font-size:12px;"><strong>Original Invoice:</strong> {{doc_number}}</p>
      </div>
    </div>
HTML;
    }

    private function creditNoteBody(): string
    {
        return <<<'HTML'
    <table style="width:100%;border-collapse:collapse;margin:16px 0;">
      <thead>
        <tr style="background:#e74c3c;">
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:center;">#</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:left;">Description</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:center;">HSN</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:right;">Qty</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:center;">Unit</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:right;">Rate</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:right;">GST</th>
          <th style="padding:10px;color:#fff;font-size:10px;text-transform:uppercase;border:1px solid #e74c3c;text-align:right;">Amount</th>
        </tr>
      </thead>
      <tbody>
        {{items_rows}}
      </tbody>
    </table>
HTML;
    }

    private function creditNoteFooter(): string
    {
        return <<<'HTML'
    <div style="display:flex;justify-content:flex-end;">
      <table style="width:300px;border-collapse:collapse;">
        <tr><td style="padding:6px;font-size:12px;color:#666;">Taxable</td><td style="padding:6px;text-align:right;">{{taxable_amount}}</td></tr>
        <tr><td style="padding:6px;font-size:12px;color:#666;">GST</td><td style="padding:6px;text-align:right;">{{gst_amount}}</td></tr>
        <tr style="border-top:2px solid #e74c3c;"><td style="padding:10px 6px;font-weight:700;color:#e74c3c;font-size:14px;">Credit Total</td><td style="padding:10px 6px;text-align:right;font-weight:800;color:#e74c3c;font-size:16px;">{{net_amount}}</td></tr>
      </table>
    </div>
    <p style="margin:16px 0;font-size:11px;color:#666;"><strong>In Words:</strong> {{amount_in_words}}</p>
    <div style="margin-top:50px;text-align:right;">
      <div style="border-top:1px solid #ccc;width:200px;display:inline-block;padding-top:8px;font-size:10px;color:#999;">Authorized Signatory</div>
    </div>
  </div>
</div>
HTML;
    }
}
