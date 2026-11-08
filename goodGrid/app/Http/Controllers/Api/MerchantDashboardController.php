<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantDashboardController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $todayRevenue = $merchant->orders()
            ->where('status', 'delivered')
            ->whereDate('created_at', today())
            ->sum('subtotal');

        $pendingOrders = $merchant->orders()
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->count();

        $lowStockProducts = $merchant->products()
            ->where('track_stock', true)
            ->where('stock_quantity', '<', 10)
            ->count();

        return response()->json([
            'data' => [
                'merchant' => $merchant,
                'today_revenue' => $todayRevenue,
                'pending_orders' => $pendingOrders,
                'low_stock_products' => $lowStockProducts,
                'total_products' => $merchant->products()->count(),
            ],
            'message' => 'Dashboard retrieved successfully',
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $query = $merchant->orders()
            ->with(['customer', 'address', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $orders,
            'message' => 'Orders retrieved successfully',
        ]);
    }

    public function updateOrderStatus(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:confirmed,preparing,ready_for_pickup',
        ]);

        $order = $merchant->orders()->findOrFail($request->order_id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'data' => $order,
            'message' => 'Order status updated successfully',
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $query = $merchant->products()->with(['category', 'healthTags']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->orderBy('name')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $products,
            'message' => 'Products retrieved successfully',
        ]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:50',
            'category_id' => 'required|exists:categories,id',
            'stock_quantity' => 'integer|min:0',
            'track_stock' => 'boolean',
            'is_available' => 'boolean',
            'health_tags' => 'nullable|array',
            'health_tags.*' => 'exists:health_tags,id',
        ]);

        $data = $request->except('health_tags');
        $data['merchant_id'] = $merchant->id;
        $data['slug'] = \Illuminate\Support\Str::slug($request->name . '-' . uniqid());

        $product = Product::create($data);

        if ($request->has('health_tags')) {
            $product->healthTags()->sync($request->health_tags);
        }

        return response()->json([
            'data' => $product->load(['category', 'healthTags']),
            'message' => 'Product created successfully',
        ], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant || $product->merchant_id !== $merchant->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'size' => 'nullable|string|max:50',
            'sku' => 'nullable|string|max:50',
            'category_id' => 'exists:categories,id',
            'stock_quantity' => 'integer|min:0',
            'track_stock' => 'boolean',
            'is_available' => 'boolean',
            'health_tags' => 'nullable|array',
            'health_tags.*' => 'exists:health_tags,id',
        ]);

        $product->update($request->except('health_tags'));

        if ($request->has('health_tags')) {
            $product->healthTags()->sync($request->health_tags);
        }

        return response()->json([
            'data' => $product->fresh()->load(['category', 'healthTags']),
            'message' => 'Product updated successfully',
        ]);
    }

    public function destroyProduct(Request $request, Product $product): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant || $product->merchant_id !== $merchant->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function analytics(Request $request): JsonResponse
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['message' => 'Merchant profile not found'], 404);
        }

        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $revenue = $merchant->orders()
            ->where('status', 'delivered')
            ->where('created_at', '>=', $startDate)
            ->sum('subtotal');

        $orderCount = $merchant->orders()
            ->where('created_at', '>=', $startDate)
            ->count();

        $topProducts = $merchant->products()
            ->withCount(['orderItems as sold_count' => function ($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->orderBy('sold_count', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'data' => [
                'revenue' => $revenue,
                'order_count' => $orderCount,
                'top_products' => $topProducts,
            ],
            'message' => 'Analytics retrieved successfully',
        ]);
    }
}
