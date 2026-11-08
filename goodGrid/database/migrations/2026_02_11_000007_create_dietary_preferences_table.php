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
        Schema::create('dietary_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Allergies
            $table->boolean('allergy_nuts')->default(false);
            $table->boolean('allergy_dairy')->default(false);
            $table->boolean('allergy_gluten')->default(false);
            $table->boolean('allergy_shellfish')->default(false);
            $table->boolean('allergy_soy')->default(false);
            $table->boolean('allergy_eggs')->default(false);
            $table->text('other_allergies')->nullable();
            // Dietary goals/preferences
            $table->boolean('goal_heart_healthy')->default(false);
            $table->boolean('goal_low_sodium')->default(false);
            $table->boolean('goal_low_sugar')->default(false);
            $table->boolean('goal_plant_based')->default(false);
            $table->boolean('goal_organic')->default(false);
            $table->boolean('goal_high_protein')->default(false);
            $table->text('other_preferences')->nullable();
            // Settings
            $table->boolean('good_swap_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dietary_preferences');
    }
};
