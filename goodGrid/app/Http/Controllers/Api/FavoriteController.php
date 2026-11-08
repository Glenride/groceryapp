<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Merchant;
use App\Models\Shopper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    /**
     * List all favorites for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $type = $request->query('type'); // 'shopper' or 'merchant'

        $query = $request->user()
            ->favorites()
            ->with('favoritable');

        if ($type) {
            $modelClass = $type === 'shopper' ? Shopper::class : Merchant::class;
            $query->where('favoritable_type', $modelClass);
        }

        $favorites = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $favorites,
            'message' => 'Favorites retrieved successfully',
        ]);
    }

    /**
     * Toggle favorite status for a shopper.
     */
    public function toggleShopper(Request $request, Shopper $shopper): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCustomer()) {
            return response()->json(['message' => 'Only customers can favorite shoppers'], 403);
        }

        // Prevent favoriting inactive shoppers
        if (!$shopper->isActive()) {
            return response()->json(['message' => 'Cannot favorite inactive shopper'], 400);
        }

        $isNowFavorited = Favorite::toggle(
            $user->id,
            Shopper::class,
            $shopper->id,
            $request->input('notes')
        );

        return response()->json([
            'data' => [
                'shopper_id' => $shopper->id,
                'is_favorited' => $isNowFavorited,
            ],
            'message' => $isNowFavorited ? 'Shopper added to favorites' : 'Shopper removed from favorites',
        ]);
    }

    /**
     * Toggle favorite status for a merchant.
     */
    public function toggleMerchant(Request $request, Merchant $merchant): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCustomer()) {
            return response()->json(['message' => 'Only customers can favorite merchants'], 403);
        }

        $isNowFavorited = Favorite::toggle(
            $user->id,
            Merchant::class,
            $merchant->id,
            $request->input('notes')
        );

        return response()->json([
            'data' => [
                'merchant_id' => $merchant->id,
                'is_favorited' => $isNowFavorited,
            ],
            'message' => $isNowFavorited ? 'Merchant added to favorites' : 'Merchant removed from favorites',
        ]);
    }

    /**
     * Check if specific items are favorited (batch check).
     */
    public function checkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'shoppers' => 'nullable|array',
            'shoppers.*' => 'integer|exists:shoppers,id',
            'merchants' => 'nullable|array',
            'merchants.*' => 'integer|exists:merchants,id',
        ]);

        $user = $request->user();
        $result = [
            'shoppers' => [],
            'merchants' => [],
        ];

        if ($request->has('shoppers')) {
            foreach ($request->shoppers as $shopperId) {
                $result['shoppers'][$shopperId] = Favorite::isFavorited(
                    $user->id,
                    Shopper::class,
                    $shopperId
                );
            }
        }

        if ($request->has('merchants')) {
            foreach ($request->merchants as $merchantId) {
                $result['merchants'][$merchantId] = Favorite::isFavorited(
                    $user->id,
                    Merchant::class,
                    $merchantId
                );
            }
        }

        return response()->json([
            'data' => $result,
            'message' => 'Favorite status checked',
        ]);
    }

    /**
     * Update notes for a favorite.
     */
    public function updateNotes(Request $request, Favorite $favorite): JsonResponse
    {
        if ($favorite->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $favorite->update(['notes' => $request->notes]);

        return response()->json([
            'data' => $favorite->fresh(),
            'message' => 'Notes updated successfully',
        ]);
    }

    /**
     * Delete a favorite.
     */
    public function destroy(Request $request, Favorite $favorite): JsonResponse
    {
        if ($favorite->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Favorite removed successfully',
        ]);
    }
}
