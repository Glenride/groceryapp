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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('shopper_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', [
                'pending',           // Waiting for shopper
                'offered',           // Sent to shopper
                'accepted',          // Shopper accepted
                'shopping',          // Shopper in store
                'in_transit',        // Shopper delivering
                'delivered',         // Order delivered
                'cancelled',         // Cancelled
            ])->default('pending');
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('shopping_started_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('estimated_pay', 8, 2);
            $table->decimal('base_pay', 8, 2);
            $table->decimal('distance_pay', 8, 2)->default(0);
            $table->decimal('time_pay', 8, 2)->default(0);
            $table->decimal('estimated_tip', 8, 2)->default(0);
            $table->decimal('bonus', 8, 2)->default(0);
            $table->decimal('total_distance', 8, 2)->nullable(); // miles
            $table->integer('estimated_time')->nullable(); // minutes
            $table->json('route_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
