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
        Schema::create('requisition_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('where_req', 120);
            $table->unsignedBigInteger('req_store_id')->nullable();
            $table->timestamp('req_at')->nullable();
            $table->unsignedBigInteger('send_store_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00); // Kept for schema compliance, but hidden in UI
            $table->decimal('approved_total_amount', 10, 2)->default(0.00); // Kept for schema compliance, but hidden in UI
            $table->smallInteger('status')->default(4)->comment('confirm=1, modify=2, reject=3, on-hold=4');
            $table->string('ip_address', 150)->nullable();
            $table->string('req_id', 120)->unique();
            $table->text('remarks')->nullable();
            $table->text('remarks1')->nullable();
            $table->text('remarks2')->nullable();
            $table->text('remarks3')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('modified_by')->nullable();
            $table->timestamp('modified_at')->nullable();
            $table->string('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisition_details');
    }
};
