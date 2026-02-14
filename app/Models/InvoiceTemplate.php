<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceTemplate extends Model
{
    public const TYPE_TAX_INVOICE = 'tax_invoice';
    public const TYPE_PROFORMA = 'proforma';
    public const TYPE_ADVANCE = 'advance';
    public const TYPE_DELIVERY_CHALLAN = 'delivery_challan';
    public const TYPE_CREDIT_NOTE = 'credit_note';
    public const TYPE_DEBIT_NOTE = 'debit_note';

    protected $fillable = [
        'name',
        'type',
        'logo_path',
        'colors',
        'header_html',
        'footer_html',
        'body_html',
        'is_default',
        'role_id',
        'version',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(InvoiceTemplateVersion::class, 'invoice_template_id')->orderByDesc('version');
    }

    public static function types(): array
    {
        return [
            self::TYPE_TAX_INVOICE => 'Tax Invoice',
            self::TYPE_PROFORMA => 'Proforma',
            self::TYPE_ADVANCE => 'Advance',
            self::TYPE_DELIVERY_CHALLAN => 'Delivery Challan',
            self::TYPE_CREDIT_NOTE => 'Credit Note',
            self::TYPE_DEBIT_NOTE => 'Debit Note',
        ];
    }
}
