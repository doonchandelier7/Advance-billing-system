<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->json('snapshot'); // header_html, footer_html, body_html, colors
            $table->string('comment', 512)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['invoice_template_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_template_versions');
    }
};
