<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cartItems = $request->user()
            ->cartItems()
            ->with(['product', 'merchant'])
            ->get()
            ->groupBy('merchant_id');

        $carts = [];
        $total = 0;

        foreach ($cartItems as $merchantId => $items) {
            $merchant = $items->first()->merchant;
            $subtotal = $items->sum(fn ($item) => $item->product->current_price * $item->quantity);
            
            $carts[] = [
                'merchant' => $merchant,
                'items' => $items,
                'subtotal' => $subtotal,
                'item_count' => $items->sum('quantity'),
            ];
            
            $total += $subtotal;
        }

        return response()->json([
            'data' => [
                'carts' => $carts,
                'total' => $total,
                'total_items' => $cartItems->flatten()->sum('quantity'),
            ],
            'message' => 'Cart retrieved successfully',
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string|max:255',
            'substitution_preference' => 'nullable|in:no_substitute,ask_first,use_best',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_available || $product->stock_quantity < $request->quantity) {
            return response()->json(['message' => 'Product not available in requested quantity'], 400);
        }

        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ],
            [
                'merchant_id' => $product->merchant_id,
                'quantity' => DB::raw("quantity + {$request->quantity}"),
                'special_instructions' => $request->special_instructions,
                'substitution_preference' => $request->substitution_preference ?? 'ask_first',
            ]
        );

        return response()->json([
            'data' => $cartItem->load('product'),
            'message' => 'Item added to cart',
        ]);
    }

    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:0',
            'special_instructions' => 'nullable|string|max:255',
            'substitution_preference' => 'nullable|in:no_substitute,ask_first,use_best',
        ]);

        if ($request->quantity === 0) {
            $cartItem->delete();
            return response()->json(['message' => 'Item removed from cart']);
        }

        $cartItem->update($request->only(['quantity', 'special_instructions', 'substitution_preference']));

        return response()->json([
            'data' => $cartItem->fresh()->load('product'),
            'message' => 'Cart item updated',
        ]);
    }

    public function remove(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->cartItems()->delete();

        return response()->json(['message' => 'Cart cleared']);
    }
}
