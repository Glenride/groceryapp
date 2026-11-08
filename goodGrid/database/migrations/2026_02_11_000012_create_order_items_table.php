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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('product_name');
            $table->decimal('product_price', 10, 2);
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->text('special_instructions')->nullable();
            $table->enum('substitution_status', ['none', 'requested', 'approved', 'rejected'])->default('none');
            $table->foreignId('substituted_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('substituted_product_name')->nullable();
            $table->decimal('substituted_product_price', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->boolean('is_found')->default(false);
            $table->text('shopper_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
