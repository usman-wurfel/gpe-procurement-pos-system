<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('general_settings', 'delivery_order_product_name_font_size')) {
                $table->string('delivery_order_product_name_font_size', 8)->default('12')->after('delivery_order_prefix');
            }
        });
    }

    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            if (Schema::hasColumn('general_settings', 'delivery_order_product_name_font_size')) {
                $table->dropColumn('delivery_order_product_name_font_size');
            }
        });
    }
};
