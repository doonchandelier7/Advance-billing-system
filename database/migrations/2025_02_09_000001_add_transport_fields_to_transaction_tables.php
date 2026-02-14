<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add transport/invoice details to purchases
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('document_type', 64)->nullable()->after('doc_number');
            $table->string('payment_mode', 32)->nullable()->after('purchase_date');
            $table->string('party_name', 191)->nullable()->after('vendor_id');
            $table->string('city', 191)->nullable()->after('party_name');
            $table->string('state', 191)->nullable()->after('city');
            $table->string('gstin', 64)->nullable()->after('state');
            $table->string('gr_number', 64)->nullable()->after('reference');
            $table->date('gr_date')->nullable()->after('gr_number');
            $table->string('driver_name', 191)->nullable()->after('gr_date');
            $table->string('vehicle_number', 64)->nullable()->after('driver_name');
            $table->string('transport_name', 191)->nullable()->after('vehicle_number');
            $table->string('place_of_supply', 191)->nullable()->after('transport_name');
            $table->string('eway_bill_no', 64)->nullable()->after('place_of_supply');
            $table->decimal('distance_km', 10, 2)->nullable()->after('eway_bill_no');
        });

        // Add transport/invoice details to purchase_returns
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->string('document_type', 64)->nullable()->after('doc_number');
            $table->string('payment_mode', 32)->nullable()->after('return_date');
            $table->string('party_name', 191)->nullable()->after('vendor_id');
            $table->string('city', 191)->nullable()->after('party_name');
            $table->string('state', 191)->nullable()->after('city');
            $table->string('gstin', 64)->nullable()->after('state');
            $table->string('gr_number', 64)->nullable()->after('reference');
            $table->date('gr_date')->nullable()->after('gr_number');
            $table->string('driver_name', 191)->nullable()->after('gr_date');
            $table->string('vehicle_number', 64)->nullable()->after('driver_name');
            $table->string('transport_name', 191)->nullable()->after('vehicle_number');
            $table->string('place_of_supply', 191)->nullable()->after('transport_name');
            $table->string('eway_bill_no', 64)->nullable()->after('place_of_supply');
            $table->decimal('distance_km', 10, 2)->nullable()->after('eway_bill_no');
        });

        // Add transport/invoice details to sales_returns
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->string('document_type', 64)->nullable()->after('doc_number');
            $table->string('payment_mode', 32)->nullable()->after('return_date');
            $table->string('party_name', 191)->nullable()->after('customer_id');
            $table->string('city', 191)->nullable()->after('party_name');
            $table->string('state', 191)->nullable()->after('city');
            $table->string('gstin', 64)->nullable()->after('state');
            $table->string('gr_number', 64)->nullable()->after('reference');
            $table->date('gr_date')->nullable()->after('gr_number');
            $table->string('driver_name', 191)->nullable()->after('gr_date');
            $table->string('vehicle_number', 64)->nullable()->after('driver_name');
            $table->string('transport_name', 191)->nullable()->after('vehicle_number');
            $table->string('place_of_supply', 191)->nullable()->after('transport_name');
            $table->string('eway_bill_no', 64)->nullable()->after('place_of_supply');
            $table->decimal('distance_km', 10, 2)->nullable()->after('eway_bill_no');
        });

        // Add payment_mode and gr fields to invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_mode', 32)->nullable()->after('invoice_date');
            $table->string('gr_number', 64)->nullable()->after('gstin');
            $table->date('gr_date')->nullable()->after('gr_number');
        });

        // Add gst_percent and hsn to purchase_return_items
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->string('product_name', 191)->nullable()->after('product_id');
            $table->string('hsn_code', 32)->nullable()->after('product_name');
            $table->decimal('gst_percent', 5, 2)->nullable()->after('rate');
        });

        // Add gst_percent and hsn to sales_return_items
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->string('product_name', 191)->nullable()->after('product_id');
            $table->string('hsn_code', 32)->nullable()->after('product_name');
            $table->decimal('gst_percent', 5, 2)->nullable()->after('rate');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'payment_mode', 'party_name', 'city', 'state', 'gstin', 'gr_number', 'gr_date', 'driver_name', 'vehicle_number', 'transport_name', 'place_of_supply', 'eway_bill_no', 'distance_km']);
        });
        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'payment_mode', 'party_name', 'city', 'state', 'gstin', 'gr_number', 'gr_date', 'driver_name', 'vehicle_number', 'transport_name', 'place_of_supply', 'eway_bill_no', 'distance_km']);
        });
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropColumn(['document_type', 'payment_mode', 'party_name', 'city', 'state', 'gstin', 'gr_number', 'gr_date', 'driver_name', 'vehicle_number', 'transport_name', 'place_of_supply', 'eway_bill_no', 'distance_km']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_mode', 'gr_number', 'gr_date']);
        });
        Schema::table('purchase_return_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'hsn_code', 'gst_percent']);
        });
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropColumn(['product_name', 'hsn_code', 'gst_percent']);
        });
    }
};
