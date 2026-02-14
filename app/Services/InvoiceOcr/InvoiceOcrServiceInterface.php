<?php

namespace App\Services\InvoiceOcr;

interface InvoiceOcrServiceInterface
{
    /**
     * Process an invoice/billing image and return structured extraction result.
     * Image path is a local filesystem path (e.g. from Storage).
     */
    public function extract(string $imagePath): ExtractionResult;
}
