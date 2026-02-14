<?php

namespace App\Services\InvoiceOcr;

use InvalidArgumentException;

class InvoiceOcrService implements InvoiceOcrServiceInterface
{
    public function __construct(
        protected InvoiceOcrServiceInterface $driver,
    ) {}

    public static function fromConfig(): self
    {
        $provider = config('invoice-ocr.provider', 'openai_vision');

        $driver = match ($provider) {
            'tesseract' => new TesseractOcrService(
                tesseractPath: config('invoice-ocr.tesseract.executable', ''),
                lang: config('invoice-ocr.tesseract.lang', 'eng'),
            ),
            'openai_vision' => new OpenAiVisionOcrService(
                apiKey: config('invoice-ocr.openai_vision.api_key', ''),
                model: config('invoice-ocr.openai_vision.model', 'gpt-4o'),
            ),
            'google_vision' => new GoogleVisionOcrService,
            'aws_textract' => new AwsTextractOcrService,
            default => throw new InvalidArgumentException("Unknown invoice OCR provider: {$provider}"),
        };

        return new self($driver);
    }

    public function extract(string $imagePath): ExtractionResult
    {
        return $this->driver->extract($imagePath);
    }
}
