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
        Schema::create('product_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('ben_name', 150)->nullable();
            $table->string('product_code', 150)->unique();
            $table->text('product_des')->nullable();
            
            $table->string('sku', 50)->unique()->nullable();
            $table->unsignedBigInteger('cat_id')->nullable(); // Foreign Key to category_masters
            $table->boolean('is_packet')->default(false);
            $table->unsignedBigInteger('uom')->nullable(); // Foreign Key to unit master
            $table->string('hsn_code', 120)->unique()->nullable();
            $table->string('pro_size', 120)->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_masters');
    }
};
