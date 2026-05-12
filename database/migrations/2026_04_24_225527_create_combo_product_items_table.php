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
        Schema::create('combo_product_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('combo_id')->constrained('combo_products')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->unsignedBigInteger('uom')->nullable();

            $table->integer('no_of_pack')->default(0);
            $table->string('each_pack_quantity')->nullable();

            $table->timestamps();

            // Index for fast queries
            $table->index(['combo_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_product_items');
    }
};
