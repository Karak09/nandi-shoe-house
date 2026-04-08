<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipality_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 50)->default('Municipality'); // Can be Municipality or Municipal Corporation
            $table->foreignId('district_id')->constrained('district_masters');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipality_masters');
    }
};