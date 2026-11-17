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
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('note');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('delivery_address');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('delivery_address');
        });
    }
};
