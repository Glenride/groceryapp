<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoritable_id',
        'favoritable_type',
        'notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get favorites of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('favoritable_type', $type);
    }

    /**
     * Check if an item is favorited by a user.
     */
    public static function isFavorited(int $userId, string $type, int $id): bool
    {
        return static::where('user_id', $userId)
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->exists();
    }

    /**
     * Toggle favorite status for an item.
     */
    public static function toggle(int $userId, string $type, int $id, ?string $notes = null): bool
    {
        $existing = static::where('user_id', $userId)
            ->where('favoritable_type', $type)
            ->where('favoritable_id', $id)
            ->first();

        if ($existing) {
            $existing->delete();
            return false; // Now unfavorited
        }

        static::create([
            'user_id' => $userId,
            'favoritable_type' => $type,
            'favoritable_id' => $id,
            'notes' => $notes,
        ]);

        return true; // Now favorited
    }
}
