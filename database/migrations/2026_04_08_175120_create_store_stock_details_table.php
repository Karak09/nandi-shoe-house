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
        Schema::create('store_stock_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('store_id');
            $table->foreignId('purchase_details_id')->nullable()->constrained('purchase_details')->onDelete('cascade');
            $table->foreignId('requisition_details_id')->nullable()->constrained('requisition_details')->onDelete('cascade');
            $table->foreignId('combo_id')->nullable()->constrained('combo_products')->onDelete('cascade');
            $table->foreignId('store_transfer_id')->nullable()->constrained('store_transfer_details')->onDelete('cascade');
            $table->unsignedBigInteger('received_from')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->unsignedBigInteger('uom')->nullable();
            $table->decimal('mrp', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->json('batch_no')->nullable();
            $table->json('barcode_no')->nullable();
            $table->integer('no_of_pack')->default(0);
            $table->string('each_pack_quantity', 120)->nullable();
            $table->decimal('gst', 5, 2)->default(0);
            $table->decimal('cgst', 5, 2)->default(0);
            $table->decimal('sgst', 5, 2)->default(0);
            $table->boolean('is_packet')->default(false);
            $table->smallInteger('transaction_type');  // 1 = IN (Purchase), 2 = OUT (store Transfer to customer), 3 = OUT (Combo Send), 4 = OUT (For Requisition Send),
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_stock_details');
    }
};
