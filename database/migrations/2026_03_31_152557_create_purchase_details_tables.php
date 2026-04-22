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
        Schema::create('purchase_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('challan_no', 120);
            $table->date('challan_date');
            $table->decimal('total', 12, 2)->default(0);
            $table->text('command')->nullable();
            $table->string('ip_address', 45)->nullable();
            
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
            $table->smallInteger('transaction_type')->default(1); // 1 = IN (Purchase), 2 = OUT (Transfer)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_details');
    }
};
