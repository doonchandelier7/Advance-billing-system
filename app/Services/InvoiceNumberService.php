<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceNumberService
{
    protected string $prefix = 'INV';
    protected string $separator = '-';

    public function generate(): string
    {
        $year = date('Y');
        $last = Invoice::query()
            ->whereNotNull('invoice_number')
            ->where('invoice_number', 'like', $this->prefix.$this->separator.$year.$this->separator.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $seq = 1;
        if ($last && preg_match('/'.preg_quote($this->prefix.$this->separator.$year.$this->separator, '/').'(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $this->prefix.$this->separator.$year.$this->separator.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
