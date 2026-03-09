<?php

namespace App\Services\InvoiceOcr;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiVisionOcrService implements InvoiceOcrServiceInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gpt-4o',
    ) {}

    public function extract(string $imagePath): ExtractionResult
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenAI API key is not set. Set OPENAI_API_KEY in .env and INVOICE_OCR_PROVIDER=openai_vision.');
        }
        $contents = file_get_contents($imagePath);
        if ($contents === false) {
            throw new \RuntimeException('Could not read image file: '.$imagePath);
        }
        $base64 = base64_encode($contents);
        $mime = mime_content_type($imagePath) ?: 'image/jpeg';
        $dataUri = 'data:'.$mime.';base64,'.$base64;

        $prompt = $this->getSystemPrompt();
        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $prompt,
                    ],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => 'Extract all invoice/billing data from this image. Return only valid JSON matching the schema.',
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => ['url' => $dataUri],
                            ],
                        ],
                    ],
                ],
                'max_tokens' => 4096,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            Log::warning('OpenAI Vision API error', ['body' => $response->body(), 'status' => $response->status()]);
            $message = 'OCR processing failed.';
            if (isset($body['error']) && is_array($body['error'])) {
                $err = $body['error'];
                $code = $err['code'] ?? $err['type'] ?? '';
                $detail = $err['message'] ?? '';
                if ($code === 'insufficient_quota' || str_contains((string) $detail, 'quota')) {
                    $message = 'OpenAI API quota exceeded. Check your plan and billing at platform.openai.com. You can still create a bill by entering details manually.';
                } elseif (! empty($detail)) {
                    $message = 'OCR failed: '.$detail;
                }
            }
            throw new \RuntimeException($message);
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? '';
        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            throw new \RuntimeException('Invalid OCR response format');
        }

        return $this->mapResponseToExtractionResult($decoded);
    }

    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
You are an invoice/bill OCR and data extraction assistant. Analyze the image and extract structured data.

Return a single JSON object with this exact structure (use null for missing values, 0 for numbers when not found):

{
  "header": {
    "document_type": "string or null (e.g. Tax Invoice, Sales)",
    "doc_number": "string or null",
    "invoice_date": "YYYY-MM-DD or null",
    "party_name": "string or null",
    "city": "string or null",
    "state": "string or null",
    "gstin": "string or null",
    "transport_name": "string or null",
    "vehicle_number": "string or null",
    "driver_name": "string or null",
    "place_of_supply": "string or null",
    "eway_bill_no": "string or null",
    "distance_km": number or null,
    "vendor_bank_name": "string or null",
    "vendor_bank_account_no": "string or null",
    "vendor_bank_branch": "string or null",
    "vendor_bank_ifsc": "string or null"
  },
  "items": [
    {
      "product_name": "string",
      "hsn_code": "string or null (HSN/SAC code e.g. 4412, 3824)",
      "quantity": number,
      "unit": "string or null (UOM: PCS, KG, LTR, NOS, etc.)",
      "rate": number,
      "gst_percent": number or null,
      "amount": number,
      "confidence": number between 0 and 1
    }
  ],
  "totals": {
    "taxable_amount": number,
    "gst_amount": number,
    "cgst_amount": number or null,
    "sgst_amount": number or null,
    "igst_amount": number or null,
    "net_amount": number,
    "advance_amount": number or null,
    "balance_amount": number or null
  },
  "overall_confidence": number between 0 and 1,
  "low_confidence_fields": ["array of field names that were uncertain"],
  "quality_warning": "string or null - brief message if image quality is low"
}

Extract every visible field. For amounts use numbers (no currency symbols). For dates use YYYY-MM-DD only.
Always extract HSN code for each line item (4-8 digits). Always extract UOM/unit (PCS, KG, LTR, NOS, etc.) for each item.
Extract bank details (BANK NAME, ACCOUNT NO, BRANCH, IFSC) when present for vendor/seller.
PROMPT;
    }

    protected function mapResponseToExtractionResult(array $decoded): ExtractionResult
    {
        $header = $decoded['header'] ?? [];
        $items = $decoded['items'] ?? [];
        $totals = $decoded['totals'] ?? [];
        $overallConfidence = (float) ($decoded['overall_confidence'] ?? 0.5);
        $lowConfidenceFields = $decoded['low_confidence_fields'] ?? [];
        $qualityWarning = $decoded['quality_warning'] ?? null;

        return new ExtractionResult(
            header: $header,
            items: $items,
            totals: $totals,
            overallConfidence: $overallConfidence,
            lowConfidenceFields: is_array($lowConfidenceFields) ? $lowConfidenceFields : [],
            qualityWarning: is_string($qualityWarning) ? $qualityWarning : null,
        );
    }
}
