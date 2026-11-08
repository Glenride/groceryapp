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
        Schema::create('shoppers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type')->nullable(); // car, bike, etc.
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('driver_license_number')->nullable();
            $table->string('driver_license_state')->nullable();
            $table->boolean('insurance_attestation')->default(false);
            $table->string('background_check_status')->default('pending'); // pending, approved, rejected
            $table->timestamp('background_check_completed_at')->nullable();
            $table->enum('status', ['pending', 'active', 'suspended', 'deactivated'])->default('pending');
            $table->decimal('rating', 2, 1)->default(5.0);
            $table->integer('total_batches')->default(0);
            $table->timestamp('last_batch_at')->nullable();
            $table->string('payout_method')->default('bank_transfer');
            $table->string('payout_account_last_four')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoppers');
    }
};
