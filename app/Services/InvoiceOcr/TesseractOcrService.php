<?php

namespace App\Services\InvoiceOcr;

use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * Free, local OCR service using Tesseract OCR.
 * No paid API keys needed – runs entirely on your machine.
 *
 * Requires Tesseract OCR installed on the system:
 *   Windows: https://github.com/UB-Mannheim/tesseract/wiki
 *   Linux:   sudo apt install tesseract-ocr
 *   Mac:     brew install tesseract
 */
class TesseractOcrService implements InvoiceOcrServiceInterface
{
    public function __construct(
        protected string $tesseractPath = '',
        protected string $lang = 'eng',
    ) {}

    public function extract(string $imagePath): ExtractionResult
    {
        if (! file_exists($imagePath)) {
            throw new \RuntimeException('Could not read image file: ' . $imagePath);
        }

        // Pre-process image for better OCR results
        $processedPath = $this->preprocessImage($imagePath);
        $pathToOcr = $processedPath ?: $imagePath;

        try {
            $rawText = $this->runOcr($pathToOcr);
        } finally {
            // Clean up preprocessed temp file
            if ($processedPath && $processedPath !== $imagePath && file_exists($processedPath)) {
                @unlink($processedPath);
            }
        }

        if (empty(trim($rawText))) {
            return new ExtractionResult(
                header: [],
                items: [],
                totals: [],
                overallConfidence: 0.1,
                lowConfidenceFields: [],
                qualityWarning: 'OCR could not extract any text from the image. Please try a clearer image or enter data manually.',
            );
        }

        Log::debug('Tesseract OCR raw text', ['text' => $rawText]);

        return $this->parseInvoiceText($rawText);
    }

    /**
     * Extract invoice data from already-extracted text (e.g., from PDF).
     * Public method so the controller can call it directly for PDF text.
     */
    public function extractFromText(string $text): ExtractionResult
    {
        if (empty(trim($text))) {
            return new ExtractionResult(
                header: [],
                items: [],
                totals: [],
                overallConfidence: 0.1,
                lowConfidenceFields: [],
                qualityWarning: 'No text could be extracted from the document.',
            );
        }

        Log::debug('PDF text extraction raw text', ['text' => $text]);

        return $this->parseInvoiceText($text);
    }

    /**
     * Pre-process image using GD library for better OCR accuracy.
     * Converts to grayscale, increases contrast, sharpens, and upscales small images.
     */
    protected function preprocessImage(string $imagePath): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }

        $mime = mime_content_type($imagePath);
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($imagePath),
            'image/png' => @imagecreatefrompng($imagePath),
            'image/webp' => @imagecreatefromwebp($imagePath),
            'image/bmp' => @imagecreatefrombmp($imagePath),
            default => null,
        };

        if (! $image) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Upscale small images (< 1500px wide) for better OCR recognition
        if ($width < 1500) {
            $scale = 1500 / $width;
            $newW = (int) ($width * $scale);
            $newH = (int) ($height * $scale);
            $upscaled = imagecreatetruecolor($newW, $newH);
            imagecopyresampled($upscaled, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);
            imagedestroy($image);
            $image = $upscaled;
        }

        // Convert to grayscale for better OCR
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        // Increase contrast (negative = more contrast)
        imagefilter($image, IMG_FILTER_CONTRAST, -20);

        // Increase brightness slightly to clear up dark scans
        imagefilter($image, IMG_FILTER_BRIGHTNESS, 10);

        // Sharpen the image to make text edges crisp
        $sharpen = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];
        $divisor = array_sum(array_map('array_sum', $sharpen));
        if ($divisor > 0) {
            imageconvolution($image, $sharpen, $divisor, 0);
        }

        $tempPath = $imagePath . '_ocr_preprocessed.png';
        imagepng($image, $tempPath);
        imagedestroy($image);

        return $tempPath;
    }

    /**
     * Run Tesseract OCR on the image.
     * Uses multiple PSM modes for best results on structured invoices.
     */
    protected function runOcr(string $imagePath): string
    {
        // Strategy: try PSM 3 (fully automatic) first – best for structured invoices
        // with tables, headers, and mixed layouts. Then fall back to PSM 4 (column)
        // and PSM 6 (uniform block) if needed.
        $psmModes = [3, 4, 6];

        foreach ($psmModes as $idx => $psm) {
            $ocr = new TesseractOCR($imagePath);
            $ocr->lang($this->lang);
            $ocr->psm($psm);

            // DO NOT use configFile('digits') – it restricts recognition to
            // only digit characters, destroying product names, party names, etc.

            if (! empty($this->tesseractPath)) {
                $ocr->executable($this->tesseractPath);
            }

            try {
                $result = $ocr->run();

                // If we got meaningful text (letters + digits), accept it
                if (! empty(trim($result)) && preg_match('/[A-Za-z]/', $result) && preg_match('/\d/', $result)) {
                    Log::debug("Tesseract OCR succeeded with PSM {$psm}", ['length' => strlen($result)]);
                    return $result;
                }

                // If this is the last mode, return whatever we got
                if ($idx === count($psmModes) - 1) {
                    return $result;
                }
            } catch (\Exception $e) {
                Log::warning("Tesseract OCR failed with PSM {$psm}", ['error' => $e->getMessage()]);
                if ($idx === count($psmModes) - 1) {
                    throw $e;
                }
            }
        }

        return '';
    }

    /**
     * Parse raw OCR text into structured invoice data.
     */
    protected function parseInvoiceText(string $text): ExtractionResult
    {
        $lines = preg_split('/\r?\n/', $text);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines, fn($l) => $l !== '');
        $lines = array_values($lines);

        $fullText = implode("\n", $lines);
        $lowConfidenceFields = [];

        // Extract header fields
        $header = $this->extractHeader($fullText, $lines, $lowConfidenceFields);

        // Extract line items
        $items = $this->extractLineItems($fullText, $lines, $lowConfidenceFields);

        // Extract totals
        $totals = $this->extractTotals($fullText, $lines, $lowConfidenceFields);

        // Auto-calculate totals from items if not found in text
        if ($totals['net_amount'] == 0 && ! empty($items)) {
            $calcTotal = 0;
            $calcTaxable = 0;
            foreach ($items as $item) {
                $amount = $item['amount'] ?? 0;
                $calcTotal += $amount;
                $gstPct = $item['gst_percent'] ?? 0;
                if ($gstPct > 0) {
                    $calcTaxable += round($amount / (1 + $gstPct / 100), 2);
                } else {
                    $calcTaxable += $amount;
                }
            }
            if ($calcTotal > 0) {
                $totals['net_amount'] = round($calcTotal, 2);
            }
            if ($calcTaxable > 0 && $totals['taxable_amount'] == 0) {
                $totals['taxable_amount'] = round($calcTaxable, 2);
            }
            if ($totals['gst_amount'] == 0 && $totals['net_amount'] > 0 && $totals['taxable_amount'] > 0) {
                $totals['gst_amount'] = round($totals['net_amount'] - $totals['taxable_amount'], 2);
            }
        }

        // Calculate overall confidence based on critical fields found
        $headerFilled = count(array_filter($header, fn($v) => $v !== null && $v !== ''));
        $totalsFilled = count(array_filter($totals, fn($v) => $v !== null && $v > 0));
        $itemCount = count($items);

        // Weight critical fields more heavily
        $score = 0;
        $maxScore = 0;

        // Critical header fields (high weight)
        $criticalHeaders = ['doc_number', 'invoice_date', 'party_name'];
        foreach ($criticalHeaders as $field) {
            $maxScore += 3;
            if (! empty($header[$field])) $score += 3;
        }
        // Important header fields (medium weight)
        $importantHeaders = ['gstin', 'state', 'city', 'document_type'];
        foreach ($importantHeaders as $field) {
            $maxScore += 2;
            if (! empty($header[$field])) $score += 2;
        }
        // Optional header fields (low weight)
        $optionalHeaders = ['transport_name', 'vehicle_number', 'driver_name', 'place_of_supply', 'eway_bill_no', 'distance_km'];
        foreach ($optionalHeaders as $field) {
            $maxScore += 1;
            if (! empty($header[$field])) $score += 1;
        }
        // Items (very important)
        $maxScore += 5;
        if ($itemCount > 0) $score += 5;
        // Totals
        $maxScore += 3;
        if ($totals['net_amount'] > 0) $score += 2;
        if ($totals['taxable_amount'] > 0) $score += 1;

        $overallConfidence = $maxScore > 0 ? min(0.95, max(0.15, $score / $maxScore)) : 0.15;

        $qualityWarning = null;
        if ($overallConfidence < 0.35) {
            $qualityWarning = 'Low extraction confidence. The image quality may be poor or the invoice format is unusual. Please review all fields carefully.';
        } elseif (count($lowConfidenceFields) > 5) {
            $qualityWarning = 'Several fields have low confidence. Please review highlighted fields.';
        } elseif ($itemCount === 0) {
            $qualityWarning = 'No line items were detected. Please add items manually.';
        }

        return new ExtractionResult(
            header: $header,
            items: $items,
            totals: $totals,
            overallConfidence: round($overallConfidence, 2),
            lowConfidenceFields: $lowConfidenceFields,
            qualityWarning: $qualityWarning,
        );
    }

    /**
     * Extract invoice header fields from OCR text.
     */
    protected function extractHeader(string $text, array $lines, array &$lowConfidence): array
    {
        $header = [
            'document_type' => null,
            'doc_number' => null,
            'invoice_date' => null,
            'party_name' => null,
            'city' => null,
            'state' => null,
            'gstin' => null,
            'transport_name' => null,
            'vehicle_number' => null,
            'driver_name' => null,
            'place_of_supply' => null,
            'eway_bill_no' => null,
            'distance_km' => null,
            'vendor_bank_name' => null,
            'vendor_bank_account_no' => null,
            'vendor_bank_branch' => null,
            'vendor_bank_ifsc' => null,
        ];

        // --- Document Type ---
        $docTypes = [
            'tax invoice', 'sales invoice', 'proforma invoice', 'credit note',
            'debit note', 'delivery challan', 'purchase order', 'quotation',
            'estimate', 'bill of supply', 'invoice', 'bill', 'receipt',
        ];
        foreach ($docTypes as $type) {
            if (stripos($text, $type) !== false) {
                $header['document_type'] = ucwords($type);
                break;
            }
        }
        if (! $header['document_type']) {
            $lowConfidence[] = 'document_type';
        }

        // --- Invoice / Doc Number ---
        $invoicePatterns = [
            '/(?:invoice\s*(?:no|number|#|num)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/(?:bill\s*(?:no|number|#|num)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/(?:doc\s*(?:no|number|#|num)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/(?:receipt\s*(?:no|number|#)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/(?:voucher\s*(?:no|number|#)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/(?:inv\.?\s*(?:no|#)\.?\s*[:=\-]?\s*)([A-Z0-9\-\/\.\s]+)/i',
            '/\b(INV[\-\/]?\d{2,}[A-Z0-9\-\/]*)\b/i',
            '/\b(GST[\-\/]\d{4}[\-\/]\d+)\b/i',
        ];
        foreach ($invoicePatterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $val = trim($m[1]);
                // Stop at next label or long whitespace
                $val = preg_replace('/\s{3,}.*$/', '', $val);
                $val = preg_replace('/\s*(dated?|date|buyer|seller|duplicate|original|copy|page).*$/i', '', $val);
                $val = trim($val, " \t\n\r\0\x0B:,-.");
                if (strlen($val) > 0) {
                    $header['doc_number'] = $val;
                    break;
                }
            }
        }
        if (! $header['doc_number']) {
            $lowConfidence[] = 'doc_number';
        }

        // --- Invoice Date ---
        $datePatterns = [
            // Labeled date patterns
            '/(?:invoice\s*date|bill\s*date|date\s*of\s*invoice|dated?)\s*[:=\-]?\s*(\d{1,2}[\s]*[\/-][\s]*\d{1,2}[\s]*[\/-][\s]*\d{2,4})/i',
            '/(?:invoice\s*date|bill\s*date|date\s*of\s*invoice|dated?)\s*[:=\-]?\s*(\d{4}[\s]*[\/-][\s]*\d{1,2}[\s]*[\/-][\s]*\d{1,2})/i',
            '/(?:invoice\s*date|bill\s*date|date\s*of\s*invoice|dated?)\s*[:=\-]?\s*(\d{1,2}\s+(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+\d{2,4})/i',
            // Unlabeled but common date formats
            '/\b(\d{2}[\/-]\d{2}[\/-]\d{4})\b/',
            '/\b(\d{4}[\/-]\d{2}[\/-]\d{2})\b/',
        ];
        foreach ($datePatterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $header['invoice_date'] = $this->normalizeDate(trim($m[1]));
                break;
            }
        }
        if (! $header['invoice_date']) {
            $lowConfidence[] = 'invoice_date';
        }

        // --- GSTIN (15-character Indian GST Number) ---
        // Try to find buyer's GSTIN specifically (appears after buyer/bill-to section)
        $allGstins = [];
        if (preg_match_all('/\b(\d{2}[A-Z]{5}\d{4}[A-Z]\d[A-Z0-9][A-Z0-9])\b/', $text, $gstMatches)) {
            $allGstins = array_unique($gstMatches[1]);
        }
        // Also try labeled GSTIN patterns
        if (preg_match_all('/(?:GSTIN|GST\s*(?:No|Number|IN)|GSTIN\/UIN)\s*[:=\-]?\s*([A-Z0-9]{15})/i', $text, $gstLabeled)) {
            foreach ($gstLabeled[1] as $g) {
                $g = strtoupper($g);
                if (preg_match('/^\d{2}[A-Z]{5}\d{4}[A-Z]\d[A-Z0-9][A-Z0-9]$/', $g)) {
                    $allGstins[] = $g;
                }
            }
            $allGstins = array_unique($allGstins);
        }

        if (count($allGstins) === 1) {
            // Only one GSTIN found – use it (likely seller's, but still useful)
            $header['gstin'] = $allGstins[0];
        } elseif (count($allGstins) >= 2) {
            // Multiple GSTINs – try to pick the buyer's one
            // Look for GSTIN that appears after "buyer" or "bill to" section
            $buyerSectionStart = false;
            foreach ($lines as $line) {
                if (preg_match('/buyer|bill\s*to|sold\s*to|ship\s*to|consignee/i', $line)) {
                    $buyerSectionStart = true;
                }
                if ($buyerSectionStart) {
                    foreach ($allGstins as $gstin) {
                        if (stripos($line, $gstin) !== false) {
                            $header['gstin'] = $gstin;
                            break 2;
                        }
                    }
                }
            }
            // Fallback: use the second GSTIN (first is usually seller's)
            if (! $header['gstin']) {
                $header['gstin'] = $allGstins[1] ?? $allGstins[0];
            }
        }
        if (! $header['gstin']) {
            $lowConfidence[] = 'gstin';
        }

        // --- Party / Customer Name ---
        // Strategy 1: Look for labeled patterns on the same line
        $partyPatterns = [
            '/(?:party\s*name|customer\s*name|bill\s*to|sold\s*to|ship\s*to|consignee|m\/?s\.?)\s*[:=\-]?\s*(.+)/i',
            '/(?:name\s*of\s*(?:party|customer|buyer))\s*[:=\-]?\s*(.+)/i',
        ];
        foreach ($partyPatterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $val = trim($m[1]);
                $val = preg_replace('/\s*(address|city|state|gstin|gst|phone|mobile|email|pin|zip).*$/i', '', $val);
                $val = trim($val, " \t\n\r\0\x0B:,-.");
                if (strlen($val) > 2 && strlen($val) < 150) {
                    $header['party_name'] = $val;
                    break;
                }
            }
        }

        // Strategy 2: Look for "BUYER'S DETAILS" section header – name is on the next non-empty line
        if (! $header['party_name']) {
            foreach ($lines as $idx => $line) {
                if (preg_match('/buyer.?\s*details?/i', $line)) {
                    for ($j = $idx + 1; $j < min($idx + 4, count($lines)); $j++) {
                        $candidate = trim($lines[$j]);
                        // Skip empty lines, labels, and addresses
                        if (empty($candidate) || preg_match('/^(address|city|state|gstin|gst|phone|#|sector|sec\.)/i', $candidate)) {
                            continue;
                        }
                        // A name should be mostly letters, not a number or code
                        if (preg_match('/^[A-Z][A-Za-z\s\.]{2,}/', $candidate)) {
                            $candidate = preg_replace('/\s*(address|city|state|gstin|gst|phone|mobile|email|pin|zip|#\d).*$/i', '', $candidate);
                            $candidate = trim($candidate, " \t\n\r\0\x0B:,-.");
                            if (strlen($candidate) > 2 && strlen($candidate) < 150) {
                                $header['party_name'] = $candidate;
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }

        // Strategy 3: Look for "buyer" keyword with name after it
        if (! $header['party_name']) {
            if (preg_match('/buyer\s*[:=\-]?\s*(.+)/i', $text, $m)) {
                $val = trim($m[1]);
                $val = preg_replace('/\s*(details?|address|city|state|gstin|gst|phone|mobile|email|pin|zip).*$/i', '', $val);
                $val = trim($val, " \t\n\r\0\x0B:,-.");
                if (strlen($val) > 2 && strlen($val) < 150 && preg_match('/[A-Za-z]/', $val)) {
                    $header['party_name'] = $val;
                }
            }
        }

        // Strategy 4: Look for "SELLER'S DETAILS" section – party_name fallback from seller
        if (! $header['party_name']) {
            foreach ($lines as $idx => $line) {
                if (preg_match('/seller.?\s*details?/i', $line)) {
                    for ($j = $idx + 1; $j < min($idx + 4, count($lines)); $j++) {
                        $candidate = trim($lines[$j]);
                        if (empty($candidate)) continue;
                        if (preg_match('/^[A-Z][A-Za-z\s\.]{2,}/', $candidate)) {
                            $candidate = preg_replace('/\s*(address|city|state|gstin).*$/i', '', $candidate);
                            $candidate = trim($candidate, " \t\n\r\0\x0B:,-.");
                            if (strlen($candidate) > 2 && strlen($candidate) < 150) {
                                // We found the seller name; for buyer, skip – but note it
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }

        if (! $header['party_name']) {
            $lowConfidence[] = 'party_name';
        }

        // --- State ---
        $indianStates = [
            'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh',
            'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka',
            'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
            'Nagaland', 'Odisha', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu',
            'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal',
            'Delhi', 'Jammu and Kashmir', 'Ladakh', 'Chandigarh', 'Puducherry',
            'Andaman and Nicobar', 'Dadra and Nagar Haveli', 'Daman and Diu', 'Lakshadweep',
        ];
        // Try labeled state first
        if (preg_match('/(?:state|place\s*of\s*supply)\s*[:=\-]?\s*(.+)/i', $text, $m)) {
            $stateText = trim($m[1]);
            foreach ($indianStates as $state) {
                if (stripos($stateText, $state) === 0) {
                    $header['state'] = $state;
                    break;
                }
            }
            if (! $header['state']) {
                $stateText = preg_replace('/\s*(code|city|gstin|pin|zip).*$/i', '', $stateText);
                $stateText = trim($stateText, " \t\n\r\0\x0B:,-.()0123456789");
                if (strlen($stateText) > 2 && strlen($stateText) < 40) {
                    $header['state'] = $stateText;
                }
            }
        }
        // Try finding state name anywhere
        if (! $header['state']) {
            foreach ($indianStates as $state) {
                if (stripos($text, $state) !== false) {
                    $header['state'] = $state;
                    break;
                }
            }
        }

        // --- City ---
        if (preg_match('/(?:city|place|town)\s*[:=\-]?\s*([A-Za-z\s]+)/i', $text, $m)) {
            $city = trim($m[1]);
            $city = preg_replace('/\s*(state|pin|zip|code|gstin).*$/i', '', $city);
            $city = trim($city, " \t\n\r\0\x0B:,-.");
            if (strlen($city) > 1 && strlen($city) < 60) {
                $header['city'] = $city;
            }
        }

        // --- Place of Supply ---
        if (preg_match('/(?:place\s*of\s*supply)\s*[:=\-]?\s*(.+)/i', $text, $m)) {
            $pos = trim($m[1]);
            $pos = preg_replace('/\s*(state|code|gstin).*$/i', '', $pos);
            $pos = trim($pos, " \t\n\r\0\x0B:,-.");
            if (strlen($pos) > 1 && strlen($pos) < 80) {
                $header['place_of_supply'] = $pos;
            }
        }

        // --- Transport Name ---
        if (preg_match('/(?:transport(?:er)?|carrier)\s*(?:name)?\s*[:=\-]?\s*(.+)/i', $text, $m)) {
            $val = trim($m[1]);
            $val = preg_replace('/\s*(vehicle|driver|mode|gr\s*no).*$/i', '', $val);
            $val = trim($val, " \t\n\r\0\x0B:,-.");
            if (strlen($val) > 1 && strlen($val) < 100) {
                $header['transport_name'] = $val;
            }
        }

        // --- Vehicle Number ---
        if (preg_match('/(?:vehicle\s*(?:no|number)\.?)\s*[:=\-]?\s*([A-Z]{2}\s*\d{1,2}\s*[A-Z]{0,3}\s*\d{1,4})/i', $text, $m)) {
            $header['vehicle_number'] = strtoupper(preg_replace('/\s+/', '', $m[1]));
        } elseif (preg_match('/\b([A-Z]{2}\d{2}[A-Z]{1,3}\d{4})\b/', $text, $m)) {
            $header['vehicle_number'] = $m[1];
        }

        // --- Driver Name ---
        if (preg_match('/(?:driver\s*(?:name)?)\s*[:=\-]?\s*([A-Za-z\s\.]+)/i', $text, $m)) {
            $val = trim($m[1]);
            $val = trim($val, " \t\n\r\0\x0B:,-.");
            if (strlen($val) > 1 && strlen($val) < 80) {
                $header['driver_name'] = $val;
            }
        }

        // --- E-Way Bill Number ---
        if (preg_match('/(?:e[\-\s]*way\s*bill\s*(?:no|number)?\.?)\s*[:=\-]?\s*(\d[\d\s]{8,})/i', $text, $m)) {
            $header['eway_bill_no'] = preg_replace('/\s+/', '', $m[1]);
        }

        // --- Distance ---
        if (preg_match('/(?:distance)\s*[:=\-]?\s*(\d+)\s*(?:km|k\.m\.?)?/i', $text, $m)) {
            $header['distance_km'] = (int) $m[1];
        }

        // --- Vendor/Seller Bank Details (for Add as Vendor) ---
        if (preg_match('/(?:BANK\s*(?:ACCOUNT\s*)?NO\.?)\s*[:=\-]?\s*([\d\s]{10,24})/i', $text, $m)) {
            $header['vendor_bank_account_no'] = preg_replace('/\s+/', '', trim($m[1]));
        }
        if (preg_match('/(?:BANK\s*NAME)\s*[:=\-]?\s*([A-Za-z0-9\s\,\-\&\.]+?)(?:\s*\n|BRANCH|ACCOUNT|IFSC|BALONGI|PUNB)/i', $text, $m)) {
            $val = trim(preg_replace('/\s+/', ' ', $m[1]));
            if (strlen($val) > 2 && strlen($val) < 100) {
                $header['vendor_bank_name'] = $val;
            }
        }
        if (preg_match('/(?:BRANCH)\s*[:=\-]?\s*([A-Za-z0-9\s\,\-\.\d]+?)(?:\s*\n|IFSC|ACCOUNT|DISTT)/i', $text, $m)) {
            $val = trim(preg_replace('/\s+/', ' ', $m[1]));
            if (strlen($val) > 2 && strlen($val) < 150) {
                $header['vendor_bank_branch'] = $val;
            }
        }
        if (preg_match('/(?:IFSC(?:\s*Code)?)\s*[:=\-]?\s*([A-Za-z0-9]{10,15})/i', $text, $m)) {
            $header['vendor_bank_ifsc'] = strtoupper(trim($m[1]));
        }

        return $header;
    }

    /**
     * Extract line items from OCR text.
     * Looks for tabular data with product/description, quantity, rate, amount patterns.
     */
    protected function extractLineItems(string $text, array $lines, array &$lowConfidence): array
    {
        $items = [];

        // Strategy 1: Look for table rows with numbers (qty, rate, amount pattern)
        // Typical invoice line: "Product Name | HSN | Qty | Unit | Rate | GST% | Amount"
        // Or simplified: "Description    10    100.00    1000.00"

        $inItemSection = false;
        $headerLineIndex = -1;

        // Find the start of the items table by looking for header keywords
        foreach ($lines as $idx => $line) {
            $lineUpper = strtoupper($line);
            $headerScore = 0;
            if (preg_match('/\b(S\.?\s*No|Sr\.?\s*No|Sl\.?\s*No|#)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(Description|Particulars|Product|Item|Goods)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(HSN|SAC)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(Qty|Quantity|Qnty)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(Rate|Price|Unit\s*Price)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(Amount|Total|Value)\b/i', $line)) $headerScore++;
            if (preg_match('/\b(GST|Tax|CGST|SGST|IGST)\b/i', $line)) $headerScore++;

            if ($headerScore >= 2) {
                $inItemSection = true;
                $headerLineIndex = $idx;
                break;
            }
        }

        // If we found a header, parse subsequent lines as items
        if ($inItemSection) {
            $startIdx = $headerLineIndex + 1;
            for ($i = $startIdx; $i < count($lines); $i++) {
                $line = $lines[$i];

                // Stop at totals section
                if (preg_match('/\b(Total|Sub\s*Total|Grand\s*Total|Taxable|Net\s*Amount|Amount\s*in\s*Words|Bank|Account|Terms|Note|Signature|Authorized)\b/i', $line)) {
                    break;
                }

                $item = $this->parseItemLine($line);
                if ($item) {
                    $items[] = $item;
                }
            }
        }

        // Strategy 2: If no items found via table, try finding any lines with numbers pattern
        if (empty($items)) {
            foreach ($lines as $line) {
                // Skip header/total lines
                if (preg_match('/\b(Total|Sub\s*Total|Grand|Taxable|GST|CGST|SGST|IGST|Invoice|Bill|Date|GSTIN|Address|Phone|Bank|Account)\b/i', $line)) {
                    continue;
                }
                $item = $this->parseItemLine($line);
                if ($item) {
                    $items[] = $item;
                }
            }
        }

        // Post-process: fix items where product_name has concatenated data but hsn/unit/gst are empty
        $items = array_map(function ($item) {
            return $this->parseMangledProductNameIfNeeded($item);
        }, $items);

        // Clean up: if many items were detected that seem like noise, filter out low-quality ones
        if (count($items) > 20) {
            $items = array_filter($items, fn($i) => ($i['confidence'] ?? 0) >= 0.4);
            $items = array_values($items);
        }

        if (empty($items)) {
            $lowConfidence[] = 'items';
        }

        return $items;
    }

    /**
     * If product_name contains concatenated HSN, UOM, amounts but those fields are empty, parse and fix.
     * e.g. "PLY 18MM () 4412 25.0000PCS 14500.00 18.00 1305.00 1" -> extract name, HSN, UOM, GST.
     */
    protected function parseMangledProductNameIfNeeded(array $item): array
    {
        $name = $item['product_name'] ?? '';
        if (strlen($name) < 15) {
            return $item;
        }
        $hasHsn = ! empty($item['hsn_code']);
        $hasUnit = ! empty($item['unit']);
        $hasGst = isset($item['gst_percent']) && $item['gst_percent'] > 0;
        if ($hasHsn && $hasUnit && $hasGst) {
            return $item;
        }

        $parsed = ['hsn' => null, 'unit' => null, 'gst' => null, 'cleanName' => $name];

        if (! $hasHsn && preg_match('/\s(\d{4})\s/', $name, $m)) {
            $cand = (int) $m[1];
            if ($cand >= 1000 && $cand <= 9999) {
                $parsed['hsn'] = (string) $cand;
            }
        }
        if (! $hasUnit && preg_match('/\d+\.?\d*(PCS|NOS|KG|KGS|LTR|LTRS|MTR|PSC|BAG|BOX|SET|PAIR|DZN|ROLL|UNIT|UNITS|FT|SQFT|SQM|RMT|CFT|CUM)/i', $name, $m)) {
            $parsed['unit'] = strtoupper($m[1]);
        }
        if (! $hasGst && preg_match('/\b(5|12|18|28)\.?0*\b/', $name, $m)) {
            $parsed['gst'] = (float) $m[1];
        }
        if ($parsed['hsn']) {
            $item['hsn_code'] = $parsed['hsn'];
            $parsed['cleanName'] = preg_replace('/\s*'.$parsed['hsn'].'\s*/', ' ', $parsed['cleanName']);
        }
        if ($parsed['unit']) {
            $item['unit'] = $parsed['unit'];
            $parsed['cleanName'] = preg_replace('/\d+\.?\d*'.$parsed['unit'].'\s*/i', ' ', $parsed['cleanName']);
        }
        if ($parsed['gst'] !== null) {
            $item['gst_percent'] = $parsed['gst'];
        }
        $parsed['cleanName'] = preg_replace('/\s+\d+\.?\d*\s+\d+\.?\d*\s+\d+\.?\d*(?:\s+\d+\.?\d*)*\s*$/', '', $parsed['cleanName']);
        $parsed['cleanName'] = trim(preg_replace('/\s+/', ' ', $parsed['cleanName']));
        if (strlen($parsed['cleanName']) >= 2) {
            $item['product_name'] = $parsed['cleanName'];
        }

        return $item;
    }

    /**
     * Try to parse a single line as an item row.
     */
    protected function parseItemLine(string $line): ?array
    {
        // Clean the line
        $line = trim($line);
        if (strlen($line) < 3) return null;

        // Skip lines that are clearly not items
        if (preg_match('/^\s*(Total|Sub\s*Total|Grand|Taxable|Net|Round|Discount|Balance|Advance|Bank|Account|Terms|Signature|Authorized|Note|Amount\s*in\s*Words|E[\-\s]*Way|EWAY|Rupees|Payment)\b/i', $line)) {
            return null;
        }

        // Pattern 1: Line starting with serial number, then description, then numbers
        // "1  PLY 18MM ()    4412    25.0000  PCS    580.00    14500.00  18.00  1305.00  1305.00  17110.00"
        if (preg_match('/^\s*(\d{1,3})\s+(.+?)(?:\s{2,}|\t)(.+)$/', $line, $m)) {
            $srNo = trim($m[1]);
            $descPart = trim($m[2]);
            $numbersPart = trim($m[3]);

            // Extract all numbers and text tokens from the numbers part
            $tokens = preg_split('/\s+/', $numbersPart);
            $numbers = [];
            $textTokens = [];
            $unit = null;

            foreach ($tokens as $tok) {
                $tok = trim($tok);
                $cleaned = str_replace([',', ' '], '', $tok);
                if (is_numeric($cleaned) && $cleaned !== '') {
                    $numbers[] = (float) $cleaned;
                } elseif (preg_match('/^(PCS|NOS|KG|KGS|LTR|LTRS|MTR|MTRS|BAG|BAGS|BOX|BOXES|TON|TONS|SET|SETS|PAIR|PAIRS|DZN|PKT|PKTS|ROLL|ROLLS|UNIT|UNITS|FT|FEET|SQFT|SQMTR|PSC|SQM|RMT|CFT|CUM|NOS\.|PCS\.)$/i', $tok)) {
                    $unit = strtoupper($tok);
                } elseif ($tok !== '' && $tok !== '(' && $tok !== ')') {
                    $textTokens[] = $tok;
                }
            }

            if (count($numbers) >= 2) {
                return $this->buildItemFromStructuredRow($descPart, $numbers, $unit);
            }
        }

        // Pattern 2: Named columns with numbers (no serial number)
        // "Product Name    1234    10    Pcs    100.00    18    1180.00"
        if (preg_match('/^(.+?)\s{2,}(\d[\d,]*\.?\d*)\s+(\d[\d,]*\.?\d*)\s+(\d[\d,]*\.?\d*)(?:\s+(\d[\d,]*\.?\d*))?(?:\s+(\d[\d,]*\.?\d*))?(?:\s+(\d[\d,]*\.?\d*))?$/', $line, $m)) {
            return $this->buildItemFromMatches($m);
        }

        // Pattern 3: With serial number prefix and period/parenthesis
        // "1.  Product Name  10  100.00  1000.00"
        if (preg_match('/^\d{1,3}[\.\)]\s*(.+?)\s{2,}(\d[\d,]*\.?\d*)\s+(\d[\d,]*\.?\d*)\s+(\d[\d,]*\.?\d*)(?:\s+(\d[\d,]*\.?\d*))?(?:\s+(\d[\d,]*\.?\d*))?/', $line, $m)) {
            return $this->buildItemFromMatches($m);
        }

        // Pattern 4: Tab-separated or pipe-separated
        $parts = preg_split('/[\t|]+/', $line);
        if (count($parts) >= 3) {
            $numericParts = [];
            $textParts = [];
            foreach ($parts as $p) {
                $p = trim($p);
                $cleaned = str_replace([',', ' '], '', $p);
                if (is_numeric($cleaned) && $cleaned !== '') {
                    $numericParts[] = (float) $cleaned;
                } elseif ($p !== '') {
                    $textParts[] = $p;
                }
            }
            if (count($numericParts) >= 2 && count($textParts) >= 1) {
                return $this->buildItemFromParts($textParts, $numericParts);
            }
        }

        // Pattern 4b: Run-together "PLY 18MM () 4412 25.0000PCS 14500.00 18.00 1305.00 1" (qty+unit concatenated)
        $uomPattern = 'PCS|NOS|KG|KGS|LTR|LTRS|MTR|PSC|BAG|BOX|SET|PAIR|DZN|ROLL|UNIT|UNITS|FT|SQFT|SQM|RMT|CFT|CUM';
        if (preg_match('/^(.+?)\s+(\d{4})\s+(\d+\.?\d*)('.$uomPattern.')\s+(.+)$/i', $line, $m)) {
            $desc = trim($m[1]);
            $hsn = $m[2];
            $qty = (float) $m[3];
            $unit = strtoupper($m[4]);
            $rest = trim($m[5]);
            $nums = array_values(array_filter(array_map(fn($n) => $this->parseNumber($n), preg_split('/\s+/', $rest)), fn($n) => $n > 0));
            if ($qty > 0 && count($nums) >= 1 && strlen($desc) > 2 && ! preg_match('/\b(Total|Sub|Grand|Taxable)\b/i', $desc)) {
                $price = $nums[0];
                $rate = round($price / $qty, 2);
                $amount = end($nums);
                $gstPct = null;
                foreach ($nums as $n) {
                    if ($n <= 100 && in_array((int) $n, [5, 12, 18, 28], true)) {
                        $gstPct = $n;
                        break;
                    }
                }
                return [
                    'product_name' => $desc,
                    'hsn_code' => $hsn,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'rate' => $rate,
                    'gst_percent' => $gstPct,
                    'amount' => $amount,
                    'confidence' => 0.75,
                ];
            }
        }

        // Pattern 5: Simple - text followed by at least 2 numbers on the same line
        if (preg_match('/^(.{3,}?)\s+([\d,]+\.?\d*)\s+([\d,]+\.?\d*)$/', $line, $m)) {
            $name = trim($m[1]);
            $num1 = $this->parseNumber($m[2]);
            $num2 = $this->parseNumber($m[3]);
            if ($num1 > 0 && $num2 > 0 && ! preg_match('/\b(Total|Sub|Grand|Tax|GST|CGST|SGST|IGST|Net|Balance|Advance|Round|Discount)\b/i', $name)) {
                return [
                    'product_name' => $name,
                    'hsn_code' => null,
                    'quantity' => $num1 <= $num2 ? $num1 : 1,
                    'unit' => null,
                    'rate' => $num1 <= $num2 ? ($num2 / max($num1, 1)) : $num1,
                    'gst_percent' => null,
                    'amount' => max($num1, $num2),
                    'confidence' => 0.4,
                ];
            }
        }

        return null;
    }

    /**
     * Build an item from a structured invoice row with known column order.
     * Handles formats like: Description | HSN | Qty | Unit | Price | Amount | GST% | SGST | CGST | G.Amount
     */
    protected function buildItemFromStructuredRow(string $description, array $numbers, ?string $unit): ?array
    {
        $description = trim($description, " \t\n\r\0\x0B:,-.()\"/");
        if (strlen($description) < 2) return null;

        // Skip summary/total lines
        if (preg_match('/\b(Total|Sub\s*Total|Grand|Taxable|Net|Round|Discount|Balance|Advance)\b/i', $description)) {
            return null;
        }

        $count = count($numbers);
        $item = [
            'product_name' => $description,
            'hsn_code' => null,
            'quantity' => 1,
            'unit' => $unit,
            'rate' => 0,
            'gst_percent' => null,
            'amount' => 0,
            'confidence' => 0.7,
        ];

        // Try to identify columns based on the number of numeric values
        // Common Indian invoice format: HSN, Qty, Price, Amount, GST%, SGST, CGST, G.Amount
        if ($count >= 7) {
            // HSN, Qty, Price, Amount, GST%, SGST, CGST, G.Amount
            $item['hsn_code'] = (string) (int) $numbers[0];
            $item['quantity'] = $numbers[1];
            $item['rate'] = $numbers[2];
            $item['gst_percent'] = isset($numbers[4]) && $numbers[4] <= 100 ? $numbers[4] : null;
            $item['amount'] = $numbers[$count - 1]; // Last is usually grand amount
            $item['confidence'] = 0.8;
        } elseif ($count >= 5) {
            // Could be: HSN, Qty, Price, Amount, GST% or Qty, Price, Amount, GST%, G.Amount
            if ($numbers[0] >= 1000 && $numbers[0] == (int) $numbers[0]) {
                // First number looks like HSN
                $item['hsn_code'] = (string) (int) $numbers[0];
                $item['quantity'] = $numbers[1];
                $item['rate'] = $numbers[2];
                $item['gst_percent'] = $numbers[3] <= 100 ? $numbers[3] : null;
                $item['amount'] = $numbers[$count - 1];
            } else {
                $item['quantity'] = $numbers[0];
                $item['rate'] = $numbers[1];
                $item['gst_percent'] = $numbers[2] <= 100 ? $numbers[2] : null;
                $item['amount'] = $numbers[$count - 1];
            }
            $item['confidence'] = 0.75;
        } elseif ($count >= 4) {
            // HSN, Qty, Rate, Amount  OR  Qty, Rate, GST%, Amount
            if ($numbers[0] >= 1000 && $numbers[0] == (int) $numbers[0]) {
                $item['hsn_code'] = (string) (int) $numbers[0];
                $item['quantity'] = $numbers[1];
                $item['rate'] = $numbers[2];
                $item['amount'] = $numbers[3];
            } elseif ($numbers[2] <= 100 && ($numbers[0] * $numbers[1]) > 0) {
                $item['quantity'] = $numbers[0];
                $item['rate'] = $numbers[1];
                $item['gst_percent'] = $numbers[2];
                $item['amount'] = $numbers[3];
            } else {
                $item['quantity'] = $numbers[0];
                $item['rate'] = $numbers[1];
                $item['amount'] = $numbers[3];
            }
            $item['confidence'] = 0.65;
        } elseif ($count >= 3) {
            $item['quantity'] = $numbers[0];
            $item['rate'] = $numbers[1];
            $item['amount'] = $numbers[2];
            $item['confidence'] = 0.6;
        } elseif ($count >= 2) {
            if ($numbers[0] <= $numbers[1] && $numbers[0] < 10000) {
                $item['quantity'] = $numbers[0];
                $item['amount'] = $numbers[1];
                $item['rate'] = $numbers[0] > 0 ? round($numbers[1] / $numbers[0], 2) : $numbers[1];
            } else {
                $item['rate'] = $numbers[0];
                $item['amount'] = $numbers[1];
            }
            $item['confidence'] = 0.45;
        }

        // Validate: amount should be reasonable
        if ($item['amount'] <= 0 && $item['rate'] > 0 && $item['quantity'] > 0) {
            $item['amount'] = round($item['quantity'] * $item['rate'], 2);
        }

        // Extract HSN from description if not yet set (4-8 digit code, not a price)
        if (! $item['hsn_code'] && preg_match('/\b(\d{4,8})\b/', $description, $hm)) {
            $cand = $hm[1];
            $asFloat = (float) $cand;
            if ($asFloat >= 1000 && $asFloat <= 99999999 && $asFloat == (int) $asFloat) {
                $item['hsn_code'] = (string) (int) $cand;
                $item['product_name'] = trim(preg_replace('/\s*\d{4,8}\s*/', ' ', $description));
            }
        }

        // Extract unit from description if not already found
        if (! $item['unit']) {
            if (preg_match('/\b(PCS|NOS|KG|KGS|LTR|LTRS|MTR|MTRS|BAG|BAGS|BOX|BOXES|TON|TONS|SET|SETS|PAIR|PAIRS|DZN|PKT|PKTS|ROLL|ROLLS|UNIT|UNITS|FT|FEET|SQFT|SQMTR|PSC|SQM|RMT|CFT|CUM)\b/i', $description, $um)) {
                $item['unit'] = strtoupper($um[1]);
            }
        }

        return $item;
    }

    /**
     * Build an item array from regex matches containing text + numbers.
     */
    protected function buildItemFromMatches(array $m): ?array
    {
        $textPart = trim($m[1] ?? '');
        $numbers = [];
        for ($i = 2; $i < count($m); $i++) {
            if (isset($m[$i]) && $m[$i] !== '') {
                $numbers[] = $this->parseNumber($m[$i]);
            }
        }

        if (count($numbers) < 2 || empty($textPart)) {
            return null;
        }

        // Skip if text part looks like a total/summary line
        if (preg_match('/\b(Total|Sub\s*Total|Grand|Taxable|Net|Round|Discount|Balance|Advance)\b/i', $textPart)) {
            return null;
        }

        return $this->buildItemFromParts([$textPart], $numbers);
    }

    /**
     * Build item from text parts and numeric parts.
     * Tries to intelligently assign qty, rate, amount, hsn, gst%.
     */
    protected function buildItemFromParts(array $textParts, array $numbers): ?array
    {
        $productName = implode(' ', $textParts);
        $productName = trim($productName, " \t\n\r\0\x0B:,-.");

        if (strlen($productName) < 2) return null;

        // Try to extract HSN code from the product name
        $hsnCode = null;
        if (preg_match('/\b(\d{4,8})\b/', $productName, $hm)) {
            // Check if it looks like an HSN code (4-8 digits)
            $possibleHsn = $hm[1];
            if (strlen($possibleHsn) >= 4 && strlen($possibleHsn) <= 8) {
                $hsnCode = $possibleHsn;
                // Remove HSN from product name
                $productName = trim(str_replace($possibleHsn, '', $productName));
            }
        }

        // Also check first numeric column for HSN
        if (! $hsnCode && count($numbers) >= 4) {
            $first = $numbers[0];
            if ($first >= 1000 && $first <= 99999999 && $first == (int) $first) {
                $hsnCode = (string) (int) $first;
                array_shift($numbers);
            }
        }

        $count = count($numbers);
        $item = [
            'product_name' => $productName,
            'hsn_code' => $hsnCode,
            'quantity' => 1,
            'unit' => null,
            'rate' => 0,
            'gst_percent' => null,
            'amount' => 0,
            'confidence' => 0.6,
        ];

        // Extract unit from text if present
        if (preg_match('/\b(Pcs|Nos|Kg|Kgs|Ltr|Ltrs|Mtr|Mtrs|Bag|Bags|Box|Boxes|Ton|Tons|Set|Sets|Pair|Pairs|Dozen|Dzn|Nos\.|Pkt|Pkts|Roll|Rolls|Unit|Units|Ft|Feet|Sqft|SqMtr)\b/i', $productName, $um)) {
            $item['unit'] = $um[1];
        }

        if ($count >= 5) {
            // qty, unit_price, gst%, tax_amount, total_amount
            $item['quantity'] = $numbers[0];
            $item['rate'] = $numbers[1];
            $item['gst_percent'] = $numbers[2] <= 100 ? $numbers[2] : null;
            $item['amount'] = $numbers[$count - 1]; // Last number is usually the amount
            $item['confidence'] = 0.7;
        } elseif ($count >= 4) {
            // qty, rate, gst%, amount  OR  hsn, qty, rate, amount
            if ($numbers[2] <= 100 && ($numbers[0] * $numbers[1]) > 0) {
                $item['quantity'] = $numbers[0];
                $item['rate'] = $numbers[1];
                $item['gst_percent'] = $numbers[2];
                $item['amount'] = $numbers[3];
            } else {
                $item['quantity'] = $numbers[0];
                $item['rate'] = $numbers[1];
                $item['amount'] = $numbers[3];
            }
            $item['confidence'] = 0.65;
        } elseif ($count >= 3) {
            // qty, rate, amount
            $item['quantity'] = $numbers[0];
            $item['rate'] = $numbers[1];
            $item['amount'] = $numbers[2];
            $item['confidence'] = 0.6;
        } elseif ($count >= 2) {
            // rate, amount or qty, amount
            if ($numbers[0] <= $numbers[1] && $numbers[0] < 10000) {
                $item['quantity'] = $numbers[0];
                $item['amount'] = $numbers[1];
                $item['rate'] = $numbers[0] > 0 ? round($numbers[1] / $numbers[0], 2) : $numbers[1];
            } else {
                $item['rate'] = $numbers[0];
                $item['amount'] = $numbers[1];
            }
            $item['confidence'] = 0.45;
        }

        // Validate: amount should be reasonable
        if ($item['amount'] <= 0 && $item['rate'] > 0 && $item['quantity'] > 0) {
            $item['amount'] = round($item['quantity'] * $item['rate'], 2);
        }

        return $item;
    }

    /**
     * Extract totals from OCR text.
     */
    protected function extractTotals(string $text, array $lines, array &$lowConfidence): array
    {
        $totals = [
            'taxable_amount' => 0,
            'gst_amount' => 0,
            'cgst_amount' => null,
            'sgst_amount' => null,
            'igst_amount' => null,
            'net_amount' => 0,
            'advance_amount' => null,
            'balance_amount' => null,
        ];

        $patterns = [
            'taxable_amount' => [
                '/(?:taxable\s*(?:amount|value|amt))\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:sub\s*total|subtotal)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:total\s*(?:before\s*tax|taxable))\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'cgst_amount' => [
                '/(?:CGST(?:\s*@\s*\d+\.?\d*\s*%)?)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:Central\s*GST)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'sgst_amount' => [
                '/(?:SGST(?:\s*@\s*\d+\.?\d*\s*%)?)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:State\s*GST)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'igst_amount' => [
                '/(?:IGST(?:\s*@\s*\d+\.?\d*\s*%)?)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:Integrated\s*GST)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'gst_amount' => [
                '/(?:(?:Total\s*)?GST\s*(?:Amount)?|Tax\s*Amount|Total\s*Tax)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'net_amount' => [
                '/(?:Grand\s*Total|Net\s*(?:Amount|Payable|Total)|Total\s*Amount|Invoice\s*Total|Bill\s*Amount|Amount\s*Payable|Total\s*Payable|Round(?:ed)?\s*Off?\s*Total)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
                '/(?:Total)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'advance_amount' => [
                '/(?:Advance|Paid|Payment\s*Received)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
            'balance_amount' => [
                '/(?:Balance|Due|Amount\s*Due|Outstanding)\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i',
            ],
        ];

        foreach ($patterns as $field => $fieldPatterns) {
            foreach ($fieldPatterns as $pattern) {
                if (preg_match($pattern, $text, $m)) {
                    $val = $this->parseNumber($m[1]);
                    if ($val > 0) {
                        $totals[$field] = $val;
                        break;
                    }
                }
            }
        }

        // Calculate GST amount from CGST + SGST if not found directly
        if ($totals['gst_amount'] == 0) {
            $cgst = $totals['cgst_amount'] ?? 0;
            $sgst = $totals['sgst_amount'] ?? 0;
            $igst = $totals['igst_amount'] ?? 0;
            $totals['gst_amount'] = $cgst + $sgst + $igst;
        }

        // If net_amount is 0, try to find the largest number that looks like a total
        if ($totals['net_amount'] == 0) {
            // Look through all lines for the last "Total" with a number
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                if (preg_match('/total\s*[:=\-]?\s*₹?\s*([\d,]+\.?\d*)/i', $lines[$i], $m)) {
                    $val = $this->parseNumber($m[1]);
                    if ($val > 0) {
                        $totals['net_amount'] = $val;
                        break;
                    }
                }
            }
        }

        // If taxable_amount is 0, calculate from net_amount - gst_amount
        if ($totals['taxable_amount'] == 0 && $totals['net_amount'] > 0 && $totals['gst_amount'] > 0) {
            $totals['taxable_amount'] = round($totals['net_amount'] - $totals['gst_amount'], 2);
        }

        // Track low confidence for key missing totals
        if ($totals['net_amount'] == 0) $lowConfidence[] = 'net_amount';
        if ($totals['taxable_amount'] == 0) $lowConfidence[] = 'taxable_amount';

        return $totals;
    }

    /**
     * Parse a number string (handles Indian number format: 1,23,456.78).
     */
    protected function parseNumber(string $str): float
    {
        $str = trim($str);
        // Remove all commas (handles both Indian and international formats)
        $str = str_replace([',', ' '], '', $str);
        return is_numeric($str) ? (float) $str : 0;
    }

    /**
     * Normalize various date formats to YYYY-MM-DD.
     */
    protected function normalizeDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);

        // Already YYYY-MM-DD
        if (preg_match('/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})$/', $dateStr, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        // DD/MM/YYYY or DD-MM-YYYY (Indian format)
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $dateStr, $m)) {
            $day = (int) $m[1];
            $month = (int) $m[2];
            $year = (int) $m[3];
            // If day > 12, it must be DD/MM/YYYY
            if ($day > 12) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
            // If month > 12, it's MM/DD/YYYY
            if ($month > 12) {
                return sprintf('%04d-%02d-%02d', $year, $day, $month);
            }
            // Assume DD/MM/YYYY (Indian standard)
            return sprintf('%04d-%02d-%02d', $year, $month, $day);
        }

        // DD/MM/YY
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2})$/', $dateStr, $m)) {
            $year = (int) $m[3] + 2000;
            return sprintf('%04d-%02d-%02d', $year, (int) $m[2], (int) $m[1]);
        }

        // "15 Jan 2024" or "15 January 2024"
        $months = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        if (preg_match('/(\d{1,2})\s+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s+(\d{2,4})/i', $dateStr, $m)) {
            $monthNum = $months[strtolower(substr($m[2], 0, 3))] ?? 1;
            $year = (int) $m[3];
            if ($year < 100) $year += 2000;
            return sprintf('%04d-%02d-%02d', $year, $monthNum, (int) $m[1]);
        }

        // Try PHP's strtotime as fallback
        $ts = @strtotime($dateStr);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }
}
