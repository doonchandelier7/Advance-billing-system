<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTemplateVersion extends Model
{
    protected $fillable = ['invoice_template_id', 'version', 'snapshot', 'comment', 'user_id'];

    protected function casts(): array
    {
        return ['snapshot' => 'array'];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
