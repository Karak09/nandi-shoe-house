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
        Schema::create('store_transfer_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('User performing the transaction');
            $table->unsignedBigInteger('store_id')->comment('Source store ID');
            
            // 1: Customer, 2: Online, 3: 3rd Party, 4: Requisition
            $table->unsignedSmallInteger('transfer_type'); 
            
            $table->string('transfer_no', 120)->unique();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('ip_address', 150)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_transfer_details');
    }
};
