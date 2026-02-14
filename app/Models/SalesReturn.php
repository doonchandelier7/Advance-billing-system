<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReturn extends Model
{
    protected $fillable = [
        'user_id',
        'customer_id',
        'party_name',
        'city',
        'state',
        'gstin',
        'invoice_id',
        'doc_number',
        'document_type',
        'return_date',
        'payment_mode',
        'reference',
        'gr_number',
        'gr_date',
        'driver_name',
        'vehicle_number',
        'transport_name',
        'place_of_supply',
        'eway_bill_no',
        'distance_km',
        'subtotal',
        'gst_amount',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'date',
            'gr_date' => 'date',
            'distance_km' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'gst_amount' => 'decimal:2',
            'total' => 'decimal:2',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id')->orderBy('sort_order');
    }
}
