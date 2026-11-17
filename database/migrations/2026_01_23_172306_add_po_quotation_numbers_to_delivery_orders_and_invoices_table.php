<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('delivery_date');
            $table->string('quotation_number')->nullable()->after('po_number');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('po_number')->nullable()->after('delivery_address');
            $table->string('quotation_number')->nullable()->after('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'quotation_number']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['po_number', 'quotation_number']);
        });
    }
};
