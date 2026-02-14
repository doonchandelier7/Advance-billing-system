<?php

namespace App\Services\InvoiceOcr;

/**
 * AWS Textract OCR driver.
 * Requires: composer require aws/aws-sdk-php
 * Set INVOICE_OCR_PROVIDER=aws_textract and AWS_TEXTRACT_* credentials in .env.
 */
class AwsTextractOcrService implements InvoiceOcrServiceInterface
{
    public function extract(string $imagePath): ExtractionResult
    {
        if (! class_exists(\Aws\Textract\TextractClient::class)) {
            throw new \RuntimeException(
                'AWS SDK is not installed. Run: composer require aws/aws-sdk-php. '.
                'Then set AWS_TEXTRACT_ACCESS_KEY_ID, AWS_TEXTRACT_SECRET_ACCESS_KEY, AWS_TEXTRACT_REGION and INVOICE_OCR_PROVIDER=aws_textract in .env.'
            );
        }

        $client = new \Aws\Textract\TextractClient([
            'version' => 'latest',
            'region'  => config('invoice-ocr.aws_textract.region'),
            'credentials' => [
                'key'    => config('invoice-ocr.aws_textract.key'),
                'secret' => config('invoice-ocr.aws_textract.secret'),
            ],
        ]);

        $imageBytes = file_get_contents($imagePath);
        $result = $client->detectDocumentText([
            'Document' => ['Bytes' => $imageBytes],
        ]);

        $text = '';
        foreach ($result->get('Blocks') ?? [] as $block) {
            if (($block['BlockType'] ?? '') === 'LINE') {
                $text .= ($block['Text'] ?? '')."\n";
            }
        }
        $text = trim($text);

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

        return $this->parseRawTextToResult($text);
    }

    protected function parseRawTextToResult(string $text): ExtractionResult
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $header = [];
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
            items: [],
            totals: $totals,
            overallConfidence: 0.5,
            lowConfidenceFields: array_keys($header),
            qualityWarning: 'AWS Textract extracted raw text. Review and complete fields manually for best accuracy.',
        );
    }

    private function normalizeDate(string $d): string
    {
        $d = str_replace('/', '-', $d);
        $t = strtotime($d);
        return $t ? date('Y-m-d', $t) : $d;
    }
}
