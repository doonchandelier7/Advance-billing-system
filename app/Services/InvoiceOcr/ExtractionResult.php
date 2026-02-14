<?php

namespace App\Services\InvoiceOcr;

use Illuminate\Contracts\Support\Arrayable;

final class ExtractionResult implements Arrayable
{
    public function __construct(
        public readonly array $header,
        public readonly array $items,
        public readonly array $totals,
        public readonly float $overallConfidence,
        /** @var list<string> Field keys that have low confidence and should be highlighted for review */
        public readonly array $lowConfidenceFields = [],
        public readonly ?string $qualityWarning = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            header: $data['header'] ?? [],
            items: $data['items'] ?? [],
            totals: $data['totals'] ?? [],
            overallConfidence: (float) ($data['overall_confidence'] ?? 0),
            lowConfidenceFields: $data['low_confidence_fields'] ?? [],
            qualityWarning: $data['quality_warning'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'header' => $this->header,
            'items' => $this->items,
            'totals' => $this->totals,
            'overall_confidence' => $this->overallConfidence,
            'low_confidence_fields' => $this->lowConfidenceFields,
            'quality_warning' => $this->qualityWarning,
        ];
    }
}
