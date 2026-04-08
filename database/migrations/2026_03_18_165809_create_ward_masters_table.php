<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ward_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120); // e.g., "Ward No 1"
            $table->foreignId('municipality_id')->constrained('municipality_masters');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ward_masters');
    }
};