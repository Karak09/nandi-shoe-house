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
        Schema::create('requisition_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('req_details_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2)->default(0.00);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->unsignedBigInteger('uom')->nullable();
            $table->integer('no_of_pack')->nullable();
            $table->string('each_pack_quantity', 120)->nullable();
            $table->boolean('is_packet')->default(false);
            $table->decimal('requested_unit_price', 10, 2)->default(0.00);
            $table->decimal('requested_price', 10, 2)->default(0.00);
            $table->decimal('approved_quantity', 10, 2)->nullable();
            $table->decimal('modify_quantity', 10, 2)->nullable();
            $table->decimal('approved_unit_price', 10, 2)->nullable();
            $table->decimal('approved_price', 10, 2)->nullable();
            $table->string('ip_address', 150)->nullable();
            $table->timestamps();
            
            $table->foreign('req_details_id')->references('id')->on('requisition_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
    }
};
