<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('favoritable'); // Can favorite shoppers or merchants
            $table->string('notes')->nullable(); // Optional customer notes
            $table->timestamps();

            // Ensure a user can only favorite the same item once
            $table->unique(['user_id', 'favoritable_id', 'favoritable_type']);
            $table->index(['user_id', 'favoritable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
