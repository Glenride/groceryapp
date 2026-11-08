<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HealthTag;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->where('is_available', true)
            ->with(['merchant', 'category', 'healthTags']);

        // Filter by merchant
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by health tags
        if ($request->has('health_tags')) {
            $tagIds = is_array($request->health_tags) 
                ? $request->health_tags 
                : explode(',', $request->health_tags);
            $query->whereHas('healthTags', function ($q) use ($tagIds) {
                $q->whereIn('health_tags.id', $tagIds);
            });
        }

        // Dietary preference filtering for authenticated users
        if ($request->user() && $request->user()->dietaryPreferences) {
            $preferences = $request->user()->dietaryPreferences;
            
            // Filter out allergens
            $allergies = $preferences->getAllergiesArray();
            if (!empty($allergies)) {
                $query->where(function ($q) use ($allergies) {
                    foreach ($allergies as $allergen) {
                        $q->whereRaw("JSON_SEARCH(allergens, 'one', ?) IS NULL", [$allergen]);
                    }
                });
            }
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $products = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $products,
            'message' => 'Products retrieved successfully',
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if (!$product->is_available) {
            return response()->json(['message' => 'Product not available'], 404);
        }

        $product->load(['merchant', 'category', 'healthTags']);

        // Get good swap suggestions
        $goodSwaps = $product->goodSwaps()
            ->where('is_active', true)
            ->with('suggestedProduct')
            ->orderBy('priority')
            ->get();

        return response()->json([
            'data' => [
                'product' => $product,
                'good_swaps' => $goodSwaps,
            ],
            'message' => 'Product retrieved successfully',
        ]);
    }

    public function healthFilters(): JsonResponse
    {
        $tags = HealthTag::where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => $tags,
            'message' => 'Health filters retrieved successfully',
        ]);
    }
}
