<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Merchant::query()
            ->where('status', 'approved')
            ->withCount('products');

        // Filter by delivery radius if location provided
        if ($request->has('latitude') && $request->has('longitude')) {
            // Calculate distance and filter
            $lat = $request->latitude;
            $lng = $request->longitude;
            
            $query->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$lat, $lng, $lat]
            )->havingRaw('distance <= delivery_radius');
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('business_name', 'like', '%' . $request->search . '%');
        }

        $merchants = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $merchants,
            'message' => 'Merchants retrieved successfully',
        ]);
    }

    public function show(Merchant $merchant): JsonResponse
    {
        if (!$merchant->isApproved()) {
            return response()->json(['message' => 'Merchant not available'], 404);
        }

        $merchant->load(['products' => function ($query) {
            $query->where('is_available', true)->with('healthTags');
        }]);

        return response()->json([
            'data' => $merchant,
            'message' => 'Merchant retrieved successfully',
        ]);
    }

    public function categories(Merchant $merchant): JsonResponse
    {
        $categories = $merchant->products()
            ->where('is_available', true)
            ->with('category')
            ->get()
            ->pluck('category')
            ->unique('id')
            ->values();

        return response()->json([
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
        ]);
    }
}
