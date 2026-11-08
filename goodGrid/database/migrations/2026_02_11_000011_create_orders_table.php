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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('batch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'pending',
                'confirmed',
                'preparing',
                'ready_for_pickup',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled',
                'refunded',
            ])->default('pending');
            $table->text('delivery_instructions')->nullable();
            $table->timestamp('scheduled_delivery_time')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->enum('substitution_preference', ['no_substitute', 'ask_first', 'use_best'])->default('ask_first');
            // Pricing
            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('delivery_fee', 8, 2)->default(0);
            $table->decimal('service_fee', 8, 2)->default(0);
            $table->decimal('tip', 8, 2)->default(0);
            $table->decimal('discount', 8, 2)->default(0);
            $table->decimal('total', 10, 2);
            // Payment
            $table->string('payment_method')->default('card');
            $table->string('payment_intent_id')->nullable();
            $table->enum('payment_status', ['pending', 'authorized', 'captured', 'failed', 'refunded'])->default('pending');
            // Ratings
            $table->integer('customer_rating')->nullable(); // 1-5
            $table->text('customer_comment')->nullable();
            $table->timestamp('rated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
