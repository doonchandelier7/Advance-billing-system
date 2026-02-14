<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('document_type', 64)->nullable();
            $table->string('doc_number', 64)->nullable();
            $table->date('invoice_date')->nullable();

            $table->string('party_name')->nullable();
            $table->string('city', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('gstin', 64)->nullable();

            $table->string('transport_name', 191)->nullable();
            $table->string('vehicle_number', 64)->nullable();
            $table->string('driver_name', 191)->nullable();
            $table->string('place_of_supply', 191)->nullable();
            $table->string('eway_bill_no', 64)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();

            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->nullable();
            $table->decimal('sgst_amount', 15, 2)->nullable();
            $table->decimal('igst_amount', 15, 2)->nullable();
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->decimal('advance_amount', 15, 2)->nullable();
            $table->decimal('balance_amount', 15, 2)->nullable();

            $table->string('source_image_path', 512)->nullable();
            $table->decimal('extraction_confidence', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
