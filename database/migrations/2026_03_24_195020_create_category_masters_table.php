<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->bigInteger('cat_id')->unique();
            $table->string('ben_name', 120)->nullable();
            $table->string('cat_code', 120)->unique();
            $table->text('cat_des')->nullable();
            
            // Self-referencing foreign key for Parent/Child categories
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('category_masters')->onDelete('set null');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_masters');
    }
};