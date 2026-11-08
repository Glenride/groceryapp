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
        Schema::create('earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopper_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['batch_base', 'batch_distance', 'batch_time', 'tip', 'bonus', 'adjustment']);
            $table->decimal('amount', 8, 2);
            $table->string('description')->nullable();
            $table->enum('status', ['pending', 'available', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('available_at')->nullable(); // When funds become available
            $table->timestamp('paid_at')->nullable();
            $table->string('payout_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('earnings');
    }
};
