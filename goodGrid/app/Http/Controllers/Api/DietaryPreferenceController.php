<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DietaryPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DietaryPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $preferences = $request->user()->dietaryPreferences;

        if (!$preferences) {
            $preferences = DietaryPreference::create([
                'user_id' => $request->user()->id,
            ]);
        }

        return response()->json([
            'data' => $preferences,
            'message' => 'Dietary preferences retrieved successfully',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'allergy_nuts' => 'boolean',
            'allergy_dairy' => 'boolean',
            'allergy_gluten' => 'boolean',
            'allergy_shellfish' => 'boolean',
            'allergy_soy' => 'boolean',
            'allergy_eggs' => 'boolean',
            'other_allergies' => 'nullable|string|max:500',
            'goal_heart_healthy' => 'boolean',
            'goal_low_sodium' => 'boolean',
            'goal_low_sugar' => 'boolean',
            'goal_plant_based' => 'boolean',
            'goal_organic' => 'boolean',
            'goal_high_protein' => 'boolean',
            'other_preferences' => 'nullable|string|max:500',
            'good_swap_enabled' => 'boolean',
        ]);

        $preferences = $request->user()->dietaryPreferences;

        if (!$preferences) {
            $preferences = DietaryPreference::create([
                'user_id' => $request->user()->id,
                ...$request->all(),
            ]);
        } else {
            $preferences->update($request->all());
        }

        return response()->json([
            'data' => $preferences->fresh(),
            'message' => 'Dietary preferences updated successfully',
        ]);
    }
}
