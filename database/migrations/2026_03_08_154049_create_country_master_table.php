<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_master', function (Blueprint $table) {
            $table->id(); 
            $table->string('c_name', 120); 
            $table->boolean('is_active')->default(true); 
            $table->boolean('is_delete')->default(false);             
            $table->timestamps(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_master');
    }
};