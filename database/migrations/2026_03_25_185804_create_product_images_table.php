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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('product_masters')->onDelete('cascade');
            
            $table->string('fst_image_doc', 120)->nullable();
            $table->string('fst_image_file_name', 120)->nullable();
            $table->string('sec_image_doc', 120)->nullable();
            $table->string('sec_image_file_name', 120)->nullable();
            $table->string('trd_image_doc', 120)->nullable();
            $table->string('trd_image_file_name', 120)->nullable();
            $table->string('foth_image_doc', 120)->nullable();
            $table->string('foth_image_file_name', 120)->nullable();
            $table->string('fiv_image_doc', 120)->nullable();
            $table->string('fiv_image_file_name', 120)->nullable();
            $table->string('six_image_doc', 120)->nullable();
            $table->string('six_image_file_name', 120)->nullable();
            $table->string('sev_image_doc', 120)->nullable();
            $table->string('sev_image_file_name', 120)->nullable();
            $table->string('eig_image_doc', 120)->nullable();
            $table->string('eig_image_file_name', 120)->nullable();
            
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
        Schema::dropIfExists('product_images');
    }
};
