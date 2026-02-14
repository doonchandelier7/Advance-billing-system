<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceUpload;
use App\Services\InvoiceNumberService;
use App\Services\InvoiceOcr\InvoiceOcrServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AiInvoiceController extends Controller
{
    public function __construct(
        protected InvoiceOcrServiceInterface $ocr,
        protected InvoiceNumberService $invoiceNumber,
    ) {}

    /**
     * Show the AI Invoice Scan page (upload/capture, form, save).
     */
    public function index(): View
    {
        return view('ai-invoice.index');
    }

    /**
     * Upload invoice image or PDF. Accepts multipart file (JPG, PNG, JPEG, PDF).
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $file = $request->file('image');
        $path = $file->store('invoice-uploads/'.auth()->id(), 'local');
        $upload = InvoiceUpload::create([
            'user_id' => auth()->id(),
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        $fullPath = Storage::path($path);
        $isPdf = strtolower($file->getClientOriginalExtension()) === 'pdf'
            || $file->getMimeType() === 'application/pdf';

        $previewUrl = $isPdf ? null : route('ai-invoice.preview', ['upload' => $upload->id]);

        return response()->json([
            'upload_id' => $upload->id,
            'path' => $path,
            'preview_url' => $previewUrl,
            'is_pdf' => $isPdf,
            'original_name' => $file->getClientOriginalName(),
            'message' => ($isPdf ? 'PDF' : 'Image') . ' uploaded. You can process it now.',
        ], 201);
    }

    /**
     * Preview uploaded image (authorized).
     */
    public function preview(Request $request, int $upload): BinaryFileResponse|RedirectResponse
    {
        $record = InvoiceUpload::where('id', $upload)->where('user_id', auth()->id())->firstOrFail();
        if (! Storage::exists($record->path)) {
            abort(404);
        }
        $fullPath = Storage::path($record->path);
        return response()->file($fullPath, [
            'Content-Type' => $record->mime_type ?? 'image/jpeg',
        ]);
    }

    /**
     * Process image or PDF with OCR/AI and return extracted data.
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required_without:upload_id|string|nullable',
            'upload_id' => 'required_without:path|exists:invoice_uploads,id',
        ]);

        $path = $request->input('path');
        $mimeType = null;
        if ($request->has('upload_id')) {
            $upload = InvoiceUpload::where('user_id', auth()->id())->findOrFail($request->input('upload_id'));
            $path = $upload->path;
            $mimeType = $upload->mime_type;
        }
        if (! $path || ! Storage::exists($path)) {
            throw ValidationException::withMessages(['path' => ['Invalid or missing file path.']]);
        }

        $fullPath = Storage::path($path);

        // Detect if file is a PDF
        $isPdf = $mimeType === 'application/pdf'
            || str_ends_with(strtolower($path), '.pdf')
            || (file_exists($fullPath) && mime_content_type($fullPath) === 'application/pdf');

        try {
            if ($isPdf) {
                // Extract text directly from PDF (more accurate than OCR)
                $result = $this->extractFromPdf($fullPath);
            } else {
                $result = $this->ocr->extract($fullPath);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Processing failed.',
                'message' => $e->getMessage(),
                'quality_warning' => $isPdf
                    ? 'PDF text extraction failed. The PDF may be image-based. Try uploading as an image instead.'
                    : 'If image quality is low, try re-uploading a clearer image.',
            ], 422);
        }

        if ($request->has('upload_id')) {
            InvoiceUpload::where('id', $request->input('upload_id'))->update(['processed_at' => now()]);
        }

        return response()->json($result->toArray());
    }

    /**
     * Extract invoice data from a PDF file using text parser.
     * This is more accurate than OCR for text-based PDFs.
     */
    protected function extractFromPdf(string $pdfPath): \App\Services\InvoiceOcr\ExtractionResult
    {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        if (empty(trim($text))) {
            // PDF might be image-based (scanned) with no embedded text
            throw new \RuntimeException(
                'This PDF contains no extractable text (it may be a scanned/image-based PDF). '
                . 'Please upload the invoice as a JPG or PNG image instead for OCR processing.'
            );
        }

        // Use the Tesseract service's text parser directly
        $ocrService = new \App\Services\InvoiceOcr\TesseractOcrService();
        return $ocrService->extractFromText($text);
    }

    /**
     * Save invoice from form data (after user review/edit).
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'document_type' => 'nullable|string|max:64',
            'doc_number' => 'nullable|string|max:64',
            'invoice_date' => 'nullable|date',
            'party_name' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'state' => 'nullable|string|max:191',
            'gstin' => 'nullable|string|max:64',
            'transport_name' => 'nullable|string|max:191',
            'vehicle_number' => 'nullable|string|max:64',
            'driver_name' => 'nullable|string|max:191',
            'place_of_supply' => 'nullable|string|max:191',
            'eway_bill_no' => 'nullable|string|max:64',
            'distance_km' => 'nullable|numeric|min:0',
            'taxable_amount' => 'nullable|numeric|min:0',
            'gst_amount' => 'nullable|numeric|min:0',
            'cgst_amount' => 'nullable|numeric|min:0',
            'sgst_amount' => 'nullable|numeric|min:0',
            'igst_amount' => 'nullable|numeric|min:0',
            'net_amount' => 'nullable|numeric|min:0',
            'advance_amount' => 'nullable|numeric|min:0',
            'balance_amount' => 'nullable|numeric|min:0',
            'source_image_path' => 'nullable|string|max:512',
            'extraction_confidence' => 'nullable|numeric|min:0|max:1',
            'upload_id' => 'nullable|integer|exists:invoice_uploads,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.product_name' => 'nullable|string|max:191',
            'items.*.hsn_code' => 'nullable|string|max:32',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'nullable|string|max:32',
            'items.*.rate' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.confidence' => 'nullable|numeric|min:0|max:1',
        ]);

        $uploadId = $validated['upload_id'] ?? null;
        $sourcePath = $validated['source_image_path'] ?? null;
        $autoDelete = config('invoice-ocr.auto_delete_image_after_extraction', true);

        $invoiceNumber = $validated['doc_number'] ?? $this->invoiceNumber->generate();

        $invoice = new Invoice;
        $invoice->user_id = auth()->id();
        $invoice->invoice_number = $invoiceNumber;
        $invoice->document_type = $validated['document_type'] ?? null;
        $invoice->doc_number = $invoiceNumber;
        $invoice->invoice_date = $validated['invoice_date'] ?? null;
        $invoice->party_name = $validated['party_name'] ?? null;
        $invoice->city = $validated['city'] ?? null;
        $invoice->state = $validated['state'] ?? null;
        $invoice->gstin = $validated['gstin'] ?? null;
        $invoice->transport_name = $validated['transport_name'] ?? null;
        $invoice->vehicle_number = $validated['vehicle_number'] ?? null;
        $invoice->driver_name = $validated['driver_name'] ?? null;
        $invoice->place_of_supply = $validated['place_of_supply'] ?? null;
        $invoice->eway_bill_no = $validated['eway_bill_no'] ?? null;
        $invoice->distance_km = $validated['distance_km'] ?? null;
        $invoice->taxable_amount = $validated['taxable_amount'] ?? 0;
        $invoice->gst_amount = $validated['gst_amount'] ?? 0;
        $invoice->cgst_amount = $validated['cgst_amount'] ?? null;
        $invoice->sgst_amount = $validated['sgst_amount'] ?? null;
        $invoice->igst_amount = $validated['igst_amount'] ?? null;
        $invoice->net_amount = $validated['net_amount'] ?? 0;
        $invoice->advance_amount = $validated['advance_amount'] ?? null;
        $invoice->balance_amount = $validated['balance_amount'] ?? null;
        $invoice->source_image_path = $sourcePath;
        $invoice->extraction_confidence = $validated['extraction_confidence'] ?? null;
        $invoice->save();

        foreach ($validated['items'] as $i => $row) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $row['product_id'] ?? null,
                'product_name' => $row['product_name'] ?? null,
                'hsn_code' => $row['hsn_code'] ?? null,
                'quantity' => $row['quantity'],
                'unit' => $row['unit'] ?? null,
                'rate' => $row['rate'],
                'gst_percent' => $row['gst_percent'] ?? null,
                'amount' => $row['amount'],
                'confidence' => $row['confidence'] ?? null,
                'sort_order' => $i,
            ]);
        }

        if ($autoDelete && ($sourcePath || $uploadId)) {
            if ($sourcePath && Storage::exists($sourcePath)) {
                Storage::delete($sourcePath);
            }
            if ($uploadId) {
                InvoiceUpload::where('id', $uploadId)->where('user_id', auth()->id())->delete();
            }
        }

        return response()->json([
            'message' => 'Invoice saved successfully.',
            'invoice_id' => $invoice->id,
            'pdf_url' => route('ai-invoice.pdf', $invoice),
            'print_url' => route('ai-invoice.print', $invoice),
        ], 201);
    }

    /**
     * Download invoice as HTML (open in browser then Print to PDF).
     */
    public function pdf(Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice);
        return view('ai-invoice.pdf', ['invoice' => $invoice->load('items')]);
    }

    /**
     * Print-ready view (opens in new tab for Print to PDF).
     */
    public function print(Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice);
        return view('ai-invoice.print', ['invoice' => $invoice->load('items')]);
    }

    protected function authorizeInvoice(Invoice $invoice): void
    {
        if ($invoice->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
