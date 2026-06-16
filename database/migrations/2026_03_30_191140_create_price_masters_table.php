<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_masters', function (Blueprint $table) {
            $table->id();
            
            // Link to the product this price belongs to
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('product_masters')->onDelete('cascade');
            
            $table->decimal('pro_mrp_price', 10, 2)->default(0);
            $table->decimal('pro_sale_price', 10, 2)->default(0);
            
            $table->decimal('pro_mrp_discount', 10, 2)->default(0);
            $table->decimal('pro_mrp_discount_percentage', 5, 2)->default(0);
            
            $table->decimal('pro_sale_discount', 10, 2)->default(0);
            $table->decimal('pro_sale_discount_percentage', 5, 2)->default(0);
            
            $table->decimal('pro_online', 10, 2)->default(0);
            $table->decimal('pro_online_discount', 10, 2)->default(0);
            $table->decimal('pro_online_discount_percentage', 5, 2)->default(0);
            
            $table->decimal('pro_unit', 10, 2)->default(1);
            $table->decimal('pro_per_unit_price', 10, 2)->default(0);
            $table->decimal('pro_size', 10, 2)->default(0);
            
            $table->decimal('cgst_rate', 5, 2)->nullable();
            $table->decimal('sgst_rate', 5, 2)->nullable();
            $table->decimal('gst_rate', 5, 2)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_masters');
    }
};