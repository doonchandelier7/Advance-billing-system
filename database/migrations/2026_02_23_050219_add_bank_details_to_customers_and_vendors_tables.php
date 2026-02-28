<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('bank_name', 191)->nullable()->after('pan');
            $table->string('bank_account_no', 64)->nullable()->after('bank_name');
            $table->string('bank_branch', 191)->nullable()->after('bank_account_no');
            $table->string('bank_ifsc', 32)->nullable()->after('bank_branch');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('bank_name', 191)->nullable()->after('pan');
            $table->string('bank_account_no', 64)->nullable()->after('bank_name');
            $table->string('bank_branch', 191)->nullable()->after('bank_account_no');
            $table->string('bank_ifsc', 32)->nullable()->after('bank_branch');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_no', 'bank_branch', 'bank_ifsc']);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_no', 'bank_branch', 'bank_ifsc']);
        });
    }
};
