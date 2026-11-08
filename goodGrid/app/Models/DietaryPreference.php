<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietaryPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'allergy_nuts',
        'allergy_dairy',
        'allergy_gluten',
        'allergy_shellfish',
        'allergy_soy',
        'allergy_eggs',
        'other_allergies',
        'goal_heart_healthy',
        'goal_low_sodium',
        'goal_low_sugar',
        'goal_plant_based',
        'goal_organic',
        'goal_high_protein',
        'other_preferences',
        'good_swap_enabled',
    ];

    protected $casts = [
        'allergy_nuts' => 'boolean',
        'allergy_dairy' => 'boolean',
        'allergy_gluten' => 'boolean',
        'allergy_shellfish' => 'boolean',
        'allergy_soy' => 'boolean',
        'allergy_eggs' => 'boolean',
        'goal_heart_healthy' => 'boolean',
        'goal_low_sodium' => 'boolean',
        'goal_low_sugar' => 'boolean',
        'goal_plant_based' => 'boolean',
        'goal_organic' => 'boolean',
        'goal_high_protein' => 'boolean',
        'good_swap_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAllergiesArray(): array
    {
        $allergies = [];
        if ($this->allergy_nuts) $allergies[] = 'nuts';
        if ($this->allergy_dairy) $allergies[] = 'dairy';
        if ($this->allergy_gluten) $allergies[] = 'gluten';
        if ($this->allergy_shellfish) $allergies[] = 'shellfish';
        if ($this->allergy_soy) $allergies[] = 'soy';
        if ($this->allergy_eggs) $allergies[] = 'eggs';
        return $allergies;
    }

    public function getGoalsArray(): array
    {
        $goals = [];
        if ($this->goal_heart_healthy) $goals[] = 'heart_healthy';
        if ($this->goal_low_sodium) $goals[] = 'low_sodium';
        if ($this->goal_low_sugar) $goals[] = 'low_sugar';
        if ($this->goal_plant_based) $goals[] = 'plant_based';
        if ($this->goal_organic) $goals[] = 'organic';
        if ($this->goal_high_protein) $goals[] = 'high_protein';
        return $goals;
    }
}
