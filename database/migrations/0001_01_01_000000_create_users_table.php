<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('users', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('name');
    //         $table->string('email')->unique();
    //         $table->timestamp('email_verified_at')->nullable();
    //         $table->string('password');
    //         $table->rememberToken();
    //         $table->timestamps();
    //     });

    //     Schema::create('password_reset_tokens', function (Blueprint $table) {
    //         $table->string('email')->primary();
    //         $table->string('token');
    //         $table->timestamp('created_at')->nullable();
    //     });

    //     Schema::create('sessions', function (Blueprint $table) {
    //         $table->string('id')->primary();
    //         $table->foreignId('user_id')->nullable()->index();
    //         $table->string('ip_address', 45)->nullable();
    //         $table->text('user_agent')->nullable();
    //         $table->longText('payload');
    //         $table->integer('last_activity')->index();
    //     });
    // }

    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('user_details_id')->constrained('users_details');
            $table->foreignId('user_type_id')->constrained('user_type_masters'); // Add the 's' here!
            
            $table->string('username', 100)->unique();
            $table->string('login_id', 150)->unique();
            $table->string('password');
            
            // Note: Generally, you shouldn't store 'confirm_password' in the database, 
            // it's only used for form validation. However, I've added it to match your schema.
            $table->string('com_password')->nullable(); 
            
            $table->timestamp('entry_time')->nullable();
            $table->timestamp('exit_time')->nullable();
            $table->integer('pwd_chng_count')->default(0);
            $table->string('pwd_chng_ip', 150)->nullable();
            $table->boolean('is_subscription')->default(false);
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('entry_ip', 150)->nullable();
            $table->string('device_name', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
