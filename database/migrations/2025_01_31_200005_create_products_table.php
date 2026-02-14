<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('code', 64)->nullable()->unique();
            $table->string('hsn_code', 32)->nullable();
            $table->text('description')->nullable();

            $table->decimal('purchase_rate', 15, 4)->default(0);
            $table->decimal('sale_rate', 15, 4)->default(0);
            $table->decimal('gst_percent', 5, 2)->nullable();

            $table->decimal('stock', 12, 3)->default(0);
            $table->decimal('low_stock_threshold', 12, 3)->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
