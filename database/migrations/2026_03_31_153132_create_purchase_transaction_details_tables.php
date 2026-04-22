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
        Schema::create('purchase_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_details_id')->constrained('purchase_details')->onDelete('cascade');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->unsignedBigInteger('uom')->nullable();
            
            $table->date('mfg_date')->nullable();
            $table->date('exp_date')->nullable();
            $table->decimal('mrp', 10, 2)->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            
            $table->json('batch_no')->nullable(); // JSON Array
            $table->integer('no_of_pack')->default(0);
            $table->string('each_pack_quantity', 120)->nullable();
            
            $table->decimal('gst', 5, 2)->default(0);
            $table->decimal('cgst', 5, 2)->default(0);
            $table->decimal('sgst', 5, 2)->default(0);
            
            $table->boolean('is_packet')->default(false);
            $table->smallInteger('transaction_type'); // 1 = IN (Purchase), 2 = OUT (Transfer)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_transaction_details');
    }
};
