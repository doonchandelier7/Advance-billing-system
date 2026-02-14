<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'user_id',
        'customer_id',
        'invoice_number',
        'document_type',
        'doc_number',
        'invoice_date',
        'payment_mode',
        'party_name',
        'city',
        'state',
        'gstin',
        'gr_number',
        'gr_date',
        'transport_name',
        'vehicle_number',
        'driver_name',
        'place_of_supply',
        'eway_bill_no',
        'distance_km',
        'taxable_amount',
        'gst_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'net_amount',
        'advance_amount',
        'balance_amount',
        'source_image_path',
        'extraction_confidence',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'gr_date' => 'date',
            'taxable_amount' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'advance_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'distance_km' => 'decimal:2',
            'extraction_confidence' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }
}
