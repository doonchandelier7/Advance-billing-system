<?php

namespace App\Services;

use App\Models\Purchase;

class PurchaseNumberService
{
    protected string $prefix = 'PUR';
    protected string $separator = '-';

    public function generate(): string
    {
        $year = date('Y');
        $last = Purchase::query()
            ->where('doc_number', 'like', $this->prefix . $this->separator . $year . $this->separator . '%')
            ->orderByDesc('id')
            ->value('doc_number');
        $seq = 1;
        if ($last && preg_match('/' . preg_quote($this->prefix . $this->separator . $year . $this->separator, '/') . '(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        return $this->prefix . $this->separator . $year . $this->separator . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
