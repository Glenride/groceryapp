<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with(['merchant', 'address', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $orders,
            'message' => 'Orders retrieved successfully',
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['merchant', 'address', 'items.product', 'batch.shopper.user']);

        return response()->json([
            'data' => $order,
            'message' => 'Order retrieved successfully',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'merchant_id' => 'required|exists:merchants,id',
            'address_id' => 'required|exists:addresses,id',
            'scheduled_delivery_time' => 'nullable|date|after:now',
            'delivery_instructions' => 'nullable|string|max:500',
            'substitution_preference' => 'nullable|in:no_substitute,ask_first,use_best',
            'tip' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        // Verify address belongs to user
        $address = Address::where('id', $request->address_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$address) {
            return response()->json(['message' => 'Invalid address'], 400);
        }

        // Get cart items for this merchant
        $cartItems = $user->cartItems()
            ->where('merchant_id', $request->merchant_id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty for this merchant'], 400);
        }

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item->product->current_price * $item->quantity;
        }

        $merchant = $cartItems->first()->merchant;
        $deliveryFee = $merchant->delivery_fee;
        $serviceFee = round($subtotal * 0.05, 2); // 5% service fee
        $tax = round($subtotal * 0.08, 2); // 8% tax
        $tip = $request->tip ?? 0;
        $total = $subtotal + $deliveryFee + $serviceFee + $tax + $tip;

        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'customer_id' => $user->id,
                'merchant_id' => $request->merchant_id,
                'address_id' => $request->address_id,
                'status' => 'pending',
                'delivery_instructions' => $request->delivery_instructions,
                'scheduled_delivery_time' => $request->scheduled_delivery_time,
                'substitution_preference' => $request->substitution_preference ?? 'ask_first',
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'service_fee' => $serviceFee,
                'tax' => $tax,
                'tip' => $tip,
                'total' => $total,
                'payment_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->current_price,
                    'quantity' => $cartItem->quantity,
                    'unit' => $cartItem->product->unit,
                    'special_instructions' => $cartItem->special_instructions,
                    'substitution_preference' => $cartItem->substitution_preference,
                    'total' => $cartItem->product->current_price * $cartItem->quantity,
                ]);

                // Update stock
                if ($cartItem->product->track_stock) {
                    $cartItem->product->decrement('stock_quantity', $cartItem->quantity);
                }
            }

            // Clear cart for this merchant
            $user->cartItems()->where('merchant_id', $request->merchant_id)->delete();

            DB::commit();

            return response()->json([
                'data' => $order->load('items'),
                'message' => 'Order created successfully',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    public function rate(Request $request, Order $order): JsonResponse
    {
        if ($order->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->isDelivered()) {
            return response()->json(['message' => 'Can only rate delivered orders'], 400);
        }

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:500',
        ]);

        $order->update([
            'customer_rating' => $request->rating,
            'customer_comment' => $request->comment,
            'rated_at' => now(),
        ]);

        return response()->json([
            'data' => $order,
            'message' => 'Order rated successfully',
        ]);
    }
}
