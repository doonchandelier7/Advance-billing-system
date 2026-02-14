<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\EwayBillService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;

class EwayBillController extends Controller
{
    public function __construct(
        protected EwayBillService $eway,
        protected QrCodeService $qr,
    ) {}

    /**
     * Get E-Way Bill related data for an invoice (for external API integration).
     */
    public function data(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        return response()->json($this->eway->generateData($invoice));
    }

    /**
     * Get QR code URL for invoice (use in PDF or print).
     */
    public function qrUrl(Invoice $invoice): JsonResponse
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
        return response()->json([
            'url' => $this->qr->invoiceQrUrl($invoice),
            'data' => $this->qr->invoiceQrData($invoice),
        ]);
    }
}
