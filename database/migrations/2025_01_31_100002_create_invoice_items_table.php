<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            $table->string('product_name')->nullable();
            $table->string('hsn_code', 32)->nullable();
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit', 32)->nullable();
            $table->decimal('rate', 15, 4)->default(0);
            $table->decimal('gst_percent', 5, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('confidence', 5, 2)->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
