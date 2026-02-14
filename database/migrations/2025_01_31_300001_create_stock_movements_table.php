<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 32); // in, out, adjustment
            $table->decimal('quantity', 12, 3); // positive for in, negative for out
            $table->decimal('stock_before', 12, 3)->nullable();
            $table->decimal('stock_after', 12, 3)->nullable();

            $table->string('reference_type', 64)->nullable(); // purchase, sale, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('notes', 512)->nullable();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
