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
        Schema::create('delivery_orders', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_order_no')->unique();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('biller_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->integer('item')->default(0);
            $table->double('total_qty')->default(0);
            $table->double('total_discount')->default(0);
            $table->double('total_tax')->default(0);
            $table->double('total_price')->default(0);
            $table->double('order_tax_rate')->nullable();
            $table->double('order_tax')->default(0);
            $table->double('order_discount')->default(0);
            $table->double('shipping_cost')->default(0);
            $table->double('grand_total')->default(0);
            $table->enum('status', ['pending', 'submitted', 'completed'])->default('pending');
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->string('delivery_address')->nullable();
            $table->date('delivery_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_orders');
    }
};
