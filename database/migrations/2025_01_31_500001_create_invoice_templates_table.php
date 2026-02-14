<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 64); // tax_invoice, proforma, advance, delivery_challan, credit_note, debit_note
            $table->string('logo_path', 512)->nullable();
            $table->json('colors')->nullable(); // primary, secondary, etc.
            $table->text('header_html')->nullable();
            $table->text('footer_html')->nullable();
            $table->text('body_html')->nullable(); // main layout with placeholders
            $table->boolean('is_default')->default(false);
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_templates');
    }
};
