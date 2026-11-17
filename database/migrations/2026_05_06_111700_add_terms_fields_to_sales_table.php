<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'terms')) {
                $table->string('terms', 50)->nullable()->after('staff_note');
            }
            if (!Schema::hasColumn('sales', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('terms');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'terms_conditions')) {
                $table->dropColumn('terms_conditions');
            }
            if (Schema::hasColumn('sales', 'terms')) {
                $table->dropColumn('terms');
            }
        });
    }
};

