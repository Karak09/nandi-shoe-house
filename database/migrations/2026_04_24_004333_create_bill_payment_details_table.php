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
       Schema::create('bill_payment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('std_id')->comment('store_transfer_details table id');
            $table->string('bill_no', 150)->unique()->comment('Serial wise: customer 00, online 01 etc');
            
            $table->unsignedSmallInteger('payment_mode'); 
            $table->string('phone', 15)->nullable();
            
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('recived_money', 12, 2)->default(0.00);
            $table->decimal('refund_money', 12, 2)->default(0.00);
            $table->decimal('dew_money', 12, 2)->default(0.00);
            
            $table->integer('bill_month');
            $table->integer('bill_year');
            
            $table->unsignedSmallInteger('payment_status'); //1 means done,2 means dew,3 means after some time
            $table->integer('cash_transfer_status')->nullable();
            
            $table->string('cus_name', 120)->nullable();
            $table->unsignedSmallInteger('age')->nullable();
            $table->string('address', 250)->nullable();
            $table->string('pincode', 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_payment_details');
    }
};
