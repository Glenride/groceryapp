<?php

namespace App\Http\Controllers\Api;

use App\Events\BatchStatusChanged;
use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Earning;
use App\Notifications\BatchStatusNotification;
use App\Notifications\OrderStatusNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShopperController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper) {
            return response()->json(['message' => 'Shopper profile not found'], 404);
        }

        $todayEarnings = Earning::where('shopper_id', $shopper->id)
            ->whereDate('created_at', today())
            ->sum('amount');

        $weekEarnings = Earning::where('shopper_id', $shopper->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('amount');

        $activeBatch = $shopper->batches()
            ->whereIn('status', ['accepted', 'shopping', 'in_transit'])
            ->with('orders.items')
            ->first();

        return response()->json([
            'data' => [
                'shopper' => $shopper,
                'today_earnings' => $todayEarnings,
                'week_earnings' => $weekEarnings,
                'active_batch' => $activeBatch,
            ],
            'message' => 'Dashboard retrieved successfully',
        ]);
    }

    public function availableBatches(Request $request): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper || !$shopper->isActive()) {
            return response()->json(['message' => 'Shopper not active'], 403);
        }

        $batches = Batch::where('status', 'pending')
            ->with(['orders.merchant', 'orders.items.product'])
            ->orderBy('estimated_pay', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'data' => $batches,
            'message' => 'Available batches retrieved successfully',
        ]);
    }

    public function myBatches(Request $request): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper) {
            return response()->json(['message' => 'Shopper profile not found'], 404);
        }

        $batches = $shopper->batches()
            ->with(['orders.merchant', 'orders.items'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $batches,
            'message' => 'Batches retrieved successfully',
        ]);
    }

    public function acceptBatch(Request $request, Batch $batch): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper || !$shopper->isActive()) {
            return response()->json(['message' => 'Shopper not active'], 403);
        }

        if (!$batch->canAccept()) {
            return response()->json(['message' => 'Batch cannot be accepted'], 400);
        }

        $previousBatchStatus = $batch->status;

        DB::beginTransaction();

        try {
            $batch->update([
                'shopper_id' => $shopper->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Update each order individually to trigger events and notifications
            foreach ($batch->orders as $order) {
                $previousOrderStatus = $order->status;
                $order->update(['status' => 'confirmed']);

                // Broadcast order status change
                event(new OrderStatusChanged($order->fresh(), $previousOrderStatus, 'confirmed'));

                // Notify customer
                $order->customer->notify(new OrderStatusNotification($order, $previousOrderStatus, 'confirmed'));
            }

            // Broadcast batch status change
            event(new BatchStatusChanged($batch->fresh(), $previousBatchStatus, 'accepted'));

            // Notify customers about batch acceptance
            foreach ($batch->orders as $order) {
                $order->customer->notify(new BatchStatusNotification($batch, $previousBatchStatus, 'accepted'));
            }

            DB::commit();

            return response()->json([
                'data' => $batch->load('orders'),
                'message' => 'Batch accepted successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to accept batch: ' . $e->getMessage()], 500);
        }
    }

    public function updateBatchStatus(Request $request, Batch $batch): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper || $batch->shopper_id !== $shopper->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:shopping,in_transit,delivered',
        ]);

        $status = $request->status;
        $updateData = ['status' => $status];
        $previousBatchStatus = $batch->status;

        switch ($status) {
            case 'shopping':
                $updateData['shopping_started_at'] = now();
                break;
            case 'in_transit':
                $updateData['in_transit_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }

        DB::beginTransaction();

        try {
            $batch->update($updateData);

            // Update order statuses
            $orderStatus = match ($status) {
                'shopping' => 'preparing',
                'in_transit' => 'in_transit',
                'delivered' => 'delivered',
            };

            foreach ($batch->orders as $order) {
                $previousOrderStatus = $order->status;
                $order->update([
                    'status' => $orderStatus,
                    'delivered_at' => $status === 'delivered' ? now() : null,
                ]);

                // Broadcast order status change
                event(new OrderStatusChanged($order->fresh(), $previousOrderStatus, $orderStatus));

                // Notify customer
                $order->customer->notify(new OrderStatusNotification($order, $previousOrderStatus, $orderStatus));
            }

            // Broadcast batch status change
            event(new BatchStatusChanged($batch->fresh(), $previousBatchStatus, $status));

            // Notify customers about batch status change
            foreach ($batch->orders as $order) {
                $order->customer->notify(new BatchStatusNotification($batch, $previousBatchStatus, $status));
            }

            // If delivered, create earnings record
            if ($status === 'delivered') {
                Earning::create([
                    'shopper_id' => $shopper->id,
                    'batch_id' => $batch->id,
                    'type' => 'batch_base',
                    'amount' => $batch->base_pay,
                    'status' => 'pending',
                ]);

                if ($batch->distance_pay > 0) {
                    Earning::create([
                        'shopper_id' => $shopper->id,
                        'batch_id' => $batch->id,
                        'type' => 'batch_distance',
                        'amount' => $batch->distance_pay,
                        'status' => 'pending',
                    ]);
                }

                $shopper->increment('total_batches');
                $shopper->update(['last_batch_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'data' => $batch->fresh(),
                'message' => 'Batch status updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update status'], 500);
        }
    }

    public function earnings(Request $request): JsonResponse
    {
        $shopper = $request->user()->shopper;

        if (!$shopper) {
            return response()->json(['message' => 'Shopper profile not found'], 404);
        }

        $earnings = $shopper->earnings()
            ->with('batch')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20);

        $summary = [
            'total_earned' => $shopper->earnings()->sum('amount'),
            'pending' => $shopper->earnings()->where('status', 'pending')->sum('amount'),
            'available' => $shopper->earnings()->where('status', 'available')->sum('amount'),
            'paid' => $shopper->earnings()->where('status', 'paid')->sum('amount'),
        ];

        return response()->json([
            'data' => [
                'earnings' => $earnings,
                'summary' => $summary,
            ],
            'message' => 'Earnings retrieved successfully',
        ]);
    }
}
