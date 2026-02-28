<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('buyer_bank_name', 191)->nullable()->after('gstin');
            $table->string('buyer_bank_account_no', 64)->nullable()->after('buyer_bank_name');
            $table->string('buyer_bank_branch', 191)->nullable()->after('buyer_bank_account_no');
            $table->string('buyer_bank_ifsc', 32)->nullable()->after('buyer_bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['buyer_bank_name', 'buyer_bank_account_no', 'buyer_bank_branch', 'buyer_bank_ifsc']);
        });
    }
};
