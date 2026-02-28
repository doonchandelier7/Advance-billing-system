<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('district', 191)->nullable()->after('city');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('district', 191)->nullable()->after('city');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('district', 191)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('district');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('district');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('district');
        });
    }
};
