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
        Schema::create('unit_convert_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->unsignedBigInteger('from_unit');
            $table->unsignedBigInteger('to_unit');
            $table->decimal('unit_factor', 8, 3)->default(1.000);
            $table->decimal('price_factor', 8, 3)->default(1.000);
            $table->boolean('packet')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();

            $table->foreign('from_unit')->references('id')->on('unit_masters')->onDelete('cascade');
            $table->foreign('to_unit')->references('id')->on('unit_masters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_convert_masters');
    }
};
