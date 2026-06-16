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
        Schema::create('customer_billing_items', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('std_id')->constrained('store_transfer_details')->cascadeOnDelete();
            $table->integer('sl_no')->nullable();
            
            $table->string('product_name', 120);
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('cat_id')->comment('Category ID');
            $table->string('pro_size', 120)->nullable();
            $table->string('product_code', 120)->nullable();
            $table->unsignedBigInteger('uom')->nullable();
            
            $table->decimal('quantity', 10, 2)->default(0.00);
            $table->json('barcode_no')->nullable();
            $table->json('batch_no')->nullable();
            
            $table->decimal('mrp_price', 10, 2)->default(0.00);
            $table->decimal('unit_price', 10, 2)->default(0.00);
            $table->decimal('sale_price', 10, 2)->default(0.00);
            $table->decimal('discount_price', 10, 2)->default(0.00);
            $table->decimal('discount_percentage', 5, 2)->default(0.00);
            
            $table->string('each_packet_quantity', 120)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_billing_items');
    }
};
