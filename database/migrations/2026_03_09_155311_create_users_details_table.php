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
        Schema::create('users_details', function (Blueprint $table) {
            $table->id();
            $table->string('f_name', 120);
            $table->string('l_name', 120)->nullable();
            $table->string('user_name', 120);
            $table->date('dob')->nullable();
            $table->string('mobile', 20);
            $table->boolean('vrfy_mobile')->default(false);
            $table->string('otp_mobile', 120)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('vrfy_email', 120)->nullable(); // Kept as string per your schema, but consider boolean if it's a true/false flag
            $table->string('otp_email', 120)->nullable();
            $table->text('address')->nullable();
            $table->string('gender', 120)->nullable();
            $table->string('application_no', 120)->nullable();
            
            // Foreign Keys
            $table->foreignId('state_id')->constrained('state_masters');
            $table->foreignId('district_id')->constrained('district_masters');
            $table->foreignId('block_id')->nullable()->constrained('block_masters');
            $table->foreignId('gp_id')->nullable()->constrained('gp_masters');
            $table->foreignId('vill_id')->nullable()->constrained('village_masters');
            $table->foreignId('muni_id')->nullable()->constrained('municipality_masters');
            $table->foreignId('ward_id')->nullable()->constrained('ward_masters');
            $table->foreignId('post_id')->nullable()->constrained('post_office_masters');
            
            $table->string('pin', 10)->nullable();
            $table->string('image_doc', 120)->nullable();
            $table->string('image_file_name', 120)->nullable();
            $table->string('proof_doc', 120)->nullable();
            $table->string('proof_file_name', 120)->nullable();
            $table->timestamp('date_of_reg')->nullable();
            $table->timestamp('verify_date')->nullable();
            $table->unsignedBigInteger('verify_by')->nullable();
            $table->smallInteger('verify_status_id')->default(2); // 1=approved, 2=pending, 3=reject, 4=hold
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->string('img_upload_ip', 45)->nullable();
            $table->string('img_change_ip', 45)->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_date')->nullable();
            $table->string('deleted_ip', 150)->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_details');
    }
};
