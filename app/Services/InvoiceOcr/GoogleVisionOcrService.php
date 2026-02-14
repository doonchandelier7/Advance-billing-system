<?php

namespace App\Services\InvoiceOcr;

use Illuminate\Support\Facades\Log;

/**
 * Google Cloud Vision OCR driver.
 * Requires: composer require google/cloud-vision
 * Set INVOICE_OCR_PROVIDER=google_vision and GOOGLE_VISION_CREDENTIALS path in .env.
 */
class GoogleVisionOcrService implements InvoiceOcrServiceInterface
{
    public function extract(string $imagePath): ExtractionResult
    {
        if (! class_exists(\Google\Cloud\Vision\V1\ImageAnnotatorClient::class)) {
            throw new \RuntimeException(
                'Google Cloud Vision is not installed. Run: composer require google/cloud-vision. '.
                'Then set GOOGLE_VISION_CREDENTIALS and INVOICE_OCR_PROVIDER=google_vision in .env.'
            );
        }

        $client = new \Google\Cloud\Vision\V1\ImageAnnotatorClient([
            'credentials' => config('invoice-ocr.google_vision.credentials'),
        ]);
        $image = file_get_contents($imagePath);
        $visionImage = new \Google\Cloud\Vision\V1\Image(['content' => $image]);
        $response = $client->documentTextDetection($visionImage);
        $text = $response->getFullTextAnnotation()?->getText() ?? '';

        $client->close();

        if (strlen($text) < 10) {
            return new ExtractionResult(
                header: [],
                items: [],
                totals: [],
                overallConfidence: 0.2,
                lowConfidenceFields: [],
                qualityWarning: 'Image quality may be low or no text detected. Please try a clearer image or re-upload.',
            );
        }

        // Google Vision returns raw text; we use a simple parser or delegate to OpenAI for structuring.
        // For full field mapping, you can send $text to OpenAI as text with a "structure this" prompt.
        return $this->parseRawTextToResult($text);
    }

    protected function parseRawTextToResult(string $text): ExtractionResult
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $header = [];
        $items = [];
        $totals = ['taxable_amount' => 0, 'gst_amount' => 0, 'net_amount' => 0, 'advance_amount' => null, 'balance_amount' => null];

        foreach ($lines as $line) {
            if (preg_match('/invoice\s*#?\s*:?\s*(\S+)/i', $line, $m)) {
                $header['doc_number'] = $m[1];
            }
            if (preg_match('/date\s*:?\s*(\d{1,2}[-\/]\d{1,2}[-\/]\d{2,4})/i', $line, $m)) {
                $header['invoice_date'] = $this->normalizeDate($m[1]);
            }
            if (preg_match('/total|net\s*amount|grand\s*total/i', $line) && preg_match('/(\d+\.?\d*)/', $line, $m)) {
                $totals['net_amount'] = (float) $m[1];
            }
        }

        return new ExtractionResult(
            header: $header,
            items: $items,
            totals: $totals,
            overallConfidence: 0.5,
            lowConfidenceFields: array_keys($header),
            qualityWarning: 'Google Vision extracted raw text. Review and complete fields manually for best accuracy.',
        );
    }

    private function normalizeDate(string $d): string
    {
        $d = str_replace('/', '-', $d);
        $t = strtotime($d);
        return $t ? date('Y-m-d', $t) : $d;
    }
}
