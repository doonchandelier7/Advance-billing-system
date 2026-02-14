<?php

namespace App\Services;

use App\Models\PurchaseReturn;
use App\Models\SalesReturn;

class ReturnNumberService
{
    protected string $prefix = '';
    protected string $separator = '-';

    public function purchaseReturn(): string
    {
        $this->prefix = 'PR';
        $year = date('Y');
        $last = PurchaseReturn::query()
            ->where('doc_number', 'like', $this->prefix . $this->separator . $year . $this->separator . '%')
            ->orderByDesc('id')
            ->value('doc_number');
        $seq = $this->nextSeq($last);
        return $this->prefix . $this->separator . $year . $this->separator . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    public function salesReturn(): string
    {
        $this->prefix = 'SR';
        $year = date('Y');
        $last = SalesReturn::query()
            ->where('doc_number', 'like', $this->prefix . $this->separator . $year . $this->separator . '%')
            ->orderByDesc('id')
            ->value('doc_number');
        $seq = $this->nextSeq($last);
        return $this->prefix . $this->separator . $year . $this->separator . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function nextSeq(?string $last): int
    {
        $seq = 1;
        if ($last && preg_match('/' . preg_quote($this->prefix . $this->separator . date('Y') . $this->separator, '/') . '(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }
        return $seq;
    }
}
