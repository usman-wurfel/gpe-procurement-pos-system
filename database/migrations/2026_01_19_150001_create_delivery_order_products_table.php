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
        Schema::create('delivery_order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_batch_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->double('qty')->default(0);
            $table->unsignedBigInteger('sale_unit_id')->nullable();
            $table->double('net_unit_price')->default(0);
            $table->double('discount')->default(0);
            $table->double('tax_rate')->default(0);
            $table->double('tax')->default(0);
            $table->double('total')->default(0);
            $table->timestamps();
            
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_products');
    }
};
