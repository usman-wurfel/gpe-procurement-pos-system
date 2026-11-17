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
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'terms')) {
                $table->string('terms', 50)->nullable()->after('note');
            }
            if (!Schema::hasColumn('quotations', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('terms');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('terms');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'terms')) {
                $table->string('terms', 50)->nullable()->after('note');
            }
            if (!Schema::hasColumn('invoices', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('terms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'terms_conditions')) {
                $table->dropColumn('terms_conditions');
            }
            if (Schema::hasColumn('invoices', 'terms')) {
                $table->dropColumn('terms');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'terms_conditions')) {
                $table->dropColumn('terms_conditions');
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            if (Schema::hasColumn('quotations', 'terms_conditions')) {
                $table->dropColumn('terms_conditions');
            }
            if (Schema::hasColumn('quotations', 'terms')) {
                $table->dropColumn('terms');
            }
        });
    }
};
