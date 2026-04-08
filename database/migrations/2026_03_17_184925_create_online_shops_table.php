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
        Schema::create('online_shops', function (Blueprint $table) {
            $table->id();

            $table->string('store_name', 120);
            $table->text('address')->nullable();
            $table->string('flat_no', 120)->nullable();

            $table->foreignId('state_id')->constrained('state_masters');
            $table->foreignId('district_id')->constrained('district_masters');
            $table->foreignId('block_id')->nullable()->constrained('block_masters');
            $table->foreignId('gp_id')->nullable()->constrained('gp_masters');
            $table->foreignId('vill_id')->nullable()->constrained('village_masters');
            $table->foreignId('muni_id')->nullable()->constrained('municipality_masters');
            $table->foreignId('ward_id')->nullable()->constrained('ward_masters');
            $table->foreignId('post_id')->nullable()->constrained('post_office_masters');

            $table->string('pin', 10)->nullable();
            $table->string('contact_no', 15);
            $table->string('email', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_shops');
    }
};
